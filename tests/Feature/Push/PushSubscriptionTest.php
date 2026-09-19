<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use NotificationChannels\WebPush\PushSubscription;

function pushPayload(array $overrides = []): array
{
    $b64 = fn (int $bytes) => rtrim(strtr(base64_encode(random_bytes($bytes)), '+/', '-_'), '=');

    return array_replace_recursive([
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/'.Str::random(40),
        'keys' => ['p256dh' => $b64(65), 'auth' => $b64(16)],
        'contentEncoding' => 'aes128gcm',
    ], $overrides);
}

// --- Authentication --------------------------------------------------------

test('guests cannot subscribe or unsubscribe', function () {
    $this->postJson(route('push.subscriptions.store'), pushPayload())->assertUnauthorized();
    $this->deleteJson(route('push.subscriptions.destroy'), ['endpoint' => 'https://fcm.googleapis.com/x'])->assertUnauthorized();

    expect(PushSubscription::count())->toBe(0);
});

test('a user who has not yet accepted the terms cannot register a device', function () {
    $user = User::factory()->withoutConsent()->create();

    // Logging in flags the consent requirement in the session, as it does in the browser.
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

    $this->postJson(route('push.subscriptions.store'), pushPayload())->assertForbidden();

    expect(PushSubscription::count())->toBe(0);
});

// --- Validation ------------------------------------------------------------

test('subscribing validates the payload', function (array $overrides, string $errorKey) {
    $this->actingAs(User::factory()->create())
        ->postJson(route('push.subscriptions.store'), pushPayload($overrides))
        ->assertUnprocessable()
        ->assertJsonValidationErrors($errorKey);

    expect(PushSubscription::count())->toBe(0);
})->with([
    'not a url' => [['endpoint' => 'not a url'], 'endpoint'],
    'plain http' => [['endpoint' => 'http://fcm.googleapis.com/fcm/send/abc'], 'endpoint'],
    'endpoint too long' => [['endpoint' => 'https://fcm.googleapis.com/'.str_repeat('a', 1100)], 'endpoint'],
    'not a push service' => [['endpoint' => 'https://internal.example.com/hook'], 'endpoint'],
    'loopback address' => [['endpoint' => 'https://127.0.0.1/fcm/send/abc'], 'endpoint'],
    'cloud metadata address' => [['endpoint' => 'https://169.254.169.254/latest/meta-data'], 'endpoint'],
    'non-default port' => [['endpoint' => 'https://fcm.googleapis.com:8443/fcm/send/abc'], 'endpoint'],
    'lookalike host' => [['endpoint' => 'https://fcm.googleapis.com.evil.example/fcm/send/abc'], 'endpoint'],
    'bad p256dh characters' => [['keys' => ['p256dh' => 'has spaces!']], 'keys.p256dh'],
    'bad auth characters' => [['keys' => ['auth' => '<script>']], 'keys.auth'],
    'unknown content encoding' => [['contentEncoding' => 'gzip'], 'contentEncoding'],
]);

test('subscribing requires the endpoint and both keys', function (string $missing) {
    $payload = pushPayload();
    data_forget($payload, $missing);

    $this->actingAs(User::factory()->create())
        ->postJson(route('push.subscriptions.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($missing);
})->with(['endpoint', 'keys.p256dh', 'keys.auth']);

// --- Subscribing -----------------------------------------------------------

test('a device can subscribe and the row belongs to the user', function () {
    $user = User::factory()->create();
    $payload = pushPayload();

    $this->actingAs($user)
        ->postJson(route('push.subscriptions.store'), $payload)
        ->assertCreated()
        ->assertExactJson(['status' => 'subscribed']);

    $row = PushSubscription::sole();

    expect($row->endpoint)->toBe($payload['endpoint'])
        ->and($row->public_key)->toBe($payload['keys']['p256dh'])
        ->and($row->auth_token)->toBe($payload['keys']['auth'])
        ->and($row->content_encoding->value)->toBe('aes128gcm')
        ->and($row->subscribable_id)->toBe($user->id)
        ->and($user->pushSubscriptions)->toHaveCount(1);
});

test('the content encoding is optional', function () {
    $payload = pushPayload();
    unset($payload['contentEncoding']);

    $this->actingAs(User::factory()->create())
        ->postJson(route('push.subscriptions.store'), $payload)
        ->assertCreated();

    expect(PushSubscription::sole()->content_encoding)->toBeNull();
});

test('re-posting the same endpoint updates it instead of duplicating', function () {
    $user = User::factory()->create();
    $payload = pushPayload();

    $this->actingAs($user)->postJson(route('push.subscriptions.store'), $payload)->assertCreated();

    $rotated = array_replace_recursive($payload, ['keys' => pushPayload()['keys'], 'contentEncoding' => 'aesgcm']);

    $this->actingAs($user)->postJson(route('push.subscriptions.store'), $rotated)->assertOk();

    $row = PushSubscription::sole();
    expect($row->public_key)->toBe($rotated['keys']['p256dh'])
        ->and($row->auth_token)->toBe($rotated['keys']['auth'])
        ->and($row->content_encoding->value)->toBe('aesgcm');
});

test('one user can register several devices', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('push.subscriptions.store'), pushPayload())->assertCreated();
    $this->actingAs($user)->postJson(route('push.subscriptions.store'), pushPayload())->assertCreated();

    expect($user->pushSubscriptions()->count())->toBe(2);
});

test('a shared device moves to whoever signs in and subscribes next', function () {
    [$first, $second] = User::factory()->count(2)->create();
    $payload = pushPayload();

    $this->actingAs($first)->postJson(route('push.subscriptions.store'), $payload)->assertCreated();
    $this->actingAs($second)->postJson(route('push.subscriptions.store'), $payload)->assertCreated();

    // Exactly one row for that endpoint, and it now belongs to the second user.
    expect(PushSubscription::where('endpoint', $payload['endpoint'])->count())->toBe(1)
        ->and($first->pushSubscriptions()->count())->toBe(0)
        ->and($second->pushSubscriptions()->count())->toBe(1);
});

// --- Unsubscribing ---------------------------------------------------------

test('unsubscribing removes the callers device', function () {
    $user = User::factory()->create();
    $payload = pushPayload();

    $this->actingAs($user)->postJson(route('push.subscriptions.store'), $payload);

    $this->actingAs($user)
        ->deleteJson(route('push.subscriptions.destroy'), ['endpoint' => $payload['endpoint']])
        ->assertNoContent();

    expect(PushSubscription::count())->toBe(0);
});

test('unsubscribing never touches another users device', function () {
    [$owner, $attacker] = User::factory()->count(2)->create();
    $payload = pushPayload();

    $this->actingAs($owner)->postJson(route('push.subscriptions.store'), $payload);

    $this->actingAs($attacker)
        ->deleteJson(route('push.subscriptions.destroy'), ['endpoint' => $payload['endpoint']])
        ->assertNoContent();

    expect($owner->pushSubscriptions()->count())->toBe(1);
});

test('unsubscribing requires an endpoint', function () {
    $this->actingAs(User::factory()->create())
        ->deleteJson(route('push.subscriptions.destroy'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('endpoint');
});

// --- Throttling ------------------------------------------------------------

test('the subscription routes are throttled per user', function () {
    $user = User::factory()->create();
    RateLimiter::clear('push'.$user->id);

    $this->actingAs($user);

    foreach (range(1, 20) as $attempt) {
        $this->deleteJson(route('push.subscriptions.destroy'), ['endpoint' => 'https://fcm.googleapis.com/x'])
            ->assertNoContent();
    }

    $this->deleteJson(route('push.subscriptions.destroy'), ['endpoint' => 'https://fcm.googleapis.com/x'])
        ->assertStatus(429);

    // A different user is unaffected.
    $this->actingAs(User::factory()->create())
        ->postJson(route('push.subscriptions.store'), pushPayload())
        ->assertCreated();
});
