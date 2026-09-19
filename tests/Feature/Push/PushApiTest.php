<?php

use App\Http\Middleware\AuthenticatePushApiKey;
use App\Models\User;
use App\Notifications\PushMessageNotification;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;

/**
 * The push API is an authentication boundary: an outside server causes notifications on players'
 * devices. These tests cover who may call it, what they may send, and what can go wrong.
 */
function apiKey(): string
{
    $key = 'kpk_'.bin2hex(random_bytes(32));
    config(['kadi.push_api.key_hashes' => [hash('sha256', $key)]]);

    return $key;
}

function apiUrl(): string
{
    return route('api.v1.push-notifications.store');
}

function callApi(string $key, array $payload = [], array $headers = [])
{
    return test()->withToken($key)->postJson(apiUrl(), $payload, $headers);
}

function apiPlayer(array $attributes = [], bool $withDevice = true): User
{
    $user = User::factory()->create($attributes);

    if ($withDevice) {
        $user->pushSubscriptions()->create([
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/'.bin2hex(random_bytes(12)),
            'public_key' => 'BKey',
            'auth_token' => 'authtoken',
        ]);
    }

    return $user;
}

function apiPayload(array $overrides = []): array
{
    return array_replace([
        'recipients' => [1],
        'title' => 'Your turn',
        'body' => 'It is your move in table 7.',
    ], $overrides);
}

// --- Authentication --------------------------------------------------------

test('the api is disabled, and refuses everyone, until a key hash is configured', function () {
    config(['kadi.push_api.key_hashes' => []]);

    test()->postJson(apiUrl(), apiPayload())->assertStatus(503);
    test()->withToken('kpk_'.bin2hex(random_bytes(32)))->postJson(apiUrl(), apiPayload())->assertStatus(503);
    // An empty bearer token must not match an empty configuration.
    test()->withHeader('Authorization', 'Bearer ')->postJson(apiUrl(), apiPayload())->assertStatus(503);
});

test('a valid key is accepted', function () {
    Notification::fake();
    $user = apiPlayer(['linked_id' => 501]);

    callApi(apiKey(), apiPayload(['recipients' => [501]]))->assertStatus(202)->assertJsonPath('queued', 1);
});

test('requests without a valid key are refused with 401', function (callable $attempt) {
    $key = apiKey();

    $attempt($key)->assertUnauthorized()->assertHeader('WWW-Authenticate', 'Bearer');
})->with([
    'no header' => [fn () => test()->postJson(apiUrl(), apiPayload())],
    'wrong key' => [fn () => test()->withToken('kpk_'.bin2hex(random_bytes(32)))->postJson(apiUrl(), apiPayload())],
    'a key that is too short' => [fn () => test()->withToken('short')->postJson(apiUrl(), apiPayload())],
    'the wrong scheme' => [fn ($key) => test()->withHeader('Authorization', 'Basic '.$key)->postJson(apiUrl(), apiPayload())],
    'the key with one character changed' => [fn ($key) => test()->withToken(substr($key, 0, -1).(str_ends_with($key, 'a') ? 'b' : 'a'))->postJson(apiUrl(), apiPayload())],
    'the HASH presented as if it were the key' => [fn ($key) => test()->withToken(hash('sha256', $key))->postJson(apiUrl(), apiPayload())],
]);

test('a signed-in web user is not an api caller', function () {
    apiKey();

    test()->actingAs(User::factory()->create())->postJson(apiUrl(), apiPayload())->assertUnauthorized();
});

test('two keys work side by side during rotation, and a retired key stops working', function () {
    Notification::fake();
    apiPlayer(['linked_id' => 502]);

    $old = 'kpk_'.bin2hex(random_bytes(32));
    $new = 'kpk_'.bin2hex(random_bytes(32));

    config(['kadi.push_api.key_hashes' => [hash('sha256', $old), hash('sha256', $new)]]);
    callApi($old, apiPayload(['recipients' => [502]]))->assertStatus(202);
    callApi($new, apiPayload(['recipients' => [502]]))->assertStatus(202);

    config(['kadi.push_api.key_hashes' => [hash('sha256', $new)]]);
    callApi($old, apiPayload(['recipients' => [502]]))->assertUnauthorized();
    callApi($new, apiPayload(['recipients' => [502]]))->assertStatus(202);
});

test('errors are always JSON, even when the client sends no Accept header', function () {
    $key = apiKey();

    // A plain form post without Accept: validation must answer 422 JSON, not redirect to a page.
    test()->withToken($key)->post(apiUrl(), [])->assertStatus(422)->assertHeader('Content-Type', 'application/json');

    // withToken() sticks to the test case, so clear it before sending an unauthenticated request.
    test()->flushHeaders()->post(apiUrl(), [])->assertStatus(401)->assertHeader('Content-Type', 'application/json');

    // Wrong method and unknown routes under /api are JSON too.
    test()->get(apiUrl())->assertStatus(405)->assertHeader('Content-Type', 'application/json');
    test()->get('/api/v1/does-not-exist')->assertStatus(404)->assertHeader('Content-Type', 'application/json');
});

test('the route is outside the web group: no session, no cookies, no csrf', function () {
    $middleware = Route::getRoutes()->getByName('api.v1.push-notifications.store')->gatherMiddleware();

    expect($middleware)->toContain('api')->not->toContain('web');

    Notification::fake();
    apiPlayer(['linked_id' => 503]);

    $response = callApi(apiKey(), apiPayload(['recipients' => [503]]));

    expect($response->headers->getCookies())->toBeEmpty();
});

test('repeated bad keys from one address are throttled, but a valid key is never locked out by them', function () {
    $key = apiKey();
    config(['kadi.push_api.failed_attempts_per_minute' => 3]);

    foreach (range(1, 3) as $attempt) {
        test()->withToken('kpk_'.bin2hex(random_bytes(32)))->postJson(apiUrl(), apiPayload())->assertUnauthorized();
    }

    test()->withToken('kpk_'.bin2hex(random_bytes(32)))->postJson(apiUrl(), apiPayload())
        ->assertStatus(429)->assertHeader('Retry-After');

    // The real server, on the same address, still gets through.
    Notification::fake();
    apiPlayer(['linked_id' => 504]);
    callApi($key, apiPayload(['recipients' => [504]]))->assertStatus(202);
});

test('the optional ip allow-list refuses other addresses before even looking at the key', function () {
    $key = apiKey();
    config(['kadi.push_api.allowed_ips' => ['203.0.113.10', '2001:db8::1']]);

    test()->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])->withToken($key)->postJson(apiUrl(), apiPayload())->assertForbidden();

    Notification::fake();
    apiPlayer(['linked_id' => 505]);
    test()->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->withToken($key)->postJson(apiUrl(), apiPayload(['recipients' => [505]]))->assertStatus(202);

    // The same IPv6 address in another notation is the same address.
    test()->withServerVariables(['REMOTE_ADDR' => '2001:0db8:0000:0000:0000:0000:0000:0001'])->withToken($key)
        ->postJson(apiUrl(), apiPayload(['recipients' => [505]]))->assertStatus(202);
});

test('authentication runs before the rate limiter (laravel sorts middleware by priority)', function () {
    // The limiter is keyed by the API key that the auth middleware establishes. If the throttle ran
    // first it would fall back to the caller's IP. This checks the REAL runtime order, not the
    // order the routes file declares.
    $route = Route::getRoutes()->getByName('api.v1.push-notifications.store');
    $order = array_values(array_map(
        fn ($middleware) => is_string($middleware) ? strtok($middleware, ':') : $middleware::class,
        app('router')->gatherRouteMiddleware($route),
    ));

    $auth = array_search(AuthenticatePushApiKey::class, $order, true);
    $throttle = array_search(ThrottleRequests::class, $order, true);

    expect($auth)->not->toBeFalse()->and($throttle)->not->toBeFalse()->and($auth)->toBeLessThan($throttle);
});

test('unauthenticated junk cannot use up the real key\'s rate limit', function () {
    Notification::fake();
    apiPlayer(['linked_id' => 507]);
    $key = apiKey();
    config(['kadi.push_api.requests_per_minute' => 3, 'kadi.push_api.failed_attempts_per_minute' => 1000]);

    // Far more bad requests than the per-key limit allows...
    foreach (range(1, 10) as $i) {
        test()->withToken('kpk_'.bin2hex(random_bytes(32)))->postJson(apiUrl(), apiPayload())->assertUnauthorized();
    }

    // ...and the real server still has its whole allowance.
    foreach (range(1, 3) as $i) {
        callApi($key, apiPayload(['recipients' => [507]]))->assertStatus(202);
    }

    callApi($key, apiPayload(['recipients' => [507]]))->assertStatus(429);
});

test('each key has its own rate limit', function () {
    Notification::fake();
    apiPlayer(['linked_id' => 506]);

    $a = 'kpk_'.bin2hex(random_bytes(32));
    $b = 'kpk_'.bin2hex(random_bytes(32));
    config(['kadi.push_api.key_hashes' => [hash('sha256', $a), hash('sha256', $b)], 'kadi.push_api.requests_per_minute' => 3]);

    foreach (range(1, 3) as $i) {
        callApi($a, apiPayload(['recipients' => [506]]))->assertStatus(202);
    }

    callApi($a, apiPayload(['recipients' => [506]]))->assertStatus(429);
    callApi($b, apiPayload(['recipients' => [506]]))->assertStatus(202);
});

test('key hashes in .env are parsed strictly: junk and short entries are ignored, case is normalised', function () {
    $good = hash('sha256', 'one');
    $other = hash('sha256', 'two');

    $parse = function (string $value) {
        // Override every place env() reads from, so a real value in the developer's .env cannot leak in.
        $saved = [$_ENV['PUSH_API_KEY_HASHES'] ?? null, $_SERVER['PUSH_API_KEY_HASHES'] ?? null];
        $_ENV['PUSH_API_KEY_HASHES'] = $_SERVER['PUSH_API_KEY_HASHES'] = $value;
        putenv("PUSH_API_KEY_HASHES={$value}");

        $config = require config_path('kadi.php');

        putenv('PUSH_API_KEY_HASHES');
        [$env, $server] = $saved;
        $env === null ? Arr::forget($_ENV, 'PUSH_API_KEY_HASHES') : $_ENV['PUSH_API_KEY_HASHES'] = $env;
        $server === null ? Arr::forget($_SERVER, 'PUSH_API_KEY_HASHES') : $_SERVER['PUSH_API_KEY_HASHES'] = $server;

        return $config['push_api']['key_hashes'];
    };

    expect($parse(''))->toBe([])
        ->and($parse('  ,, '))->toBe([])
        ->and($parse('not-a-hash'))->toBe([])
        ->and($parse(substr($good, 0, 63)))->toBe([])                           // one character short
        ->and($parse($good.'0'))->toBe([])                                       // one character long
        ->and($parse(" {$good} , junk , ".strtoupper($other)))->toBe([$good, $other]);
});

// --- Validation ------------------------------------------------------------

test('the payload is validated', function (array $overrides, string $field) {
    callApi(apiKey(), apiPayload($overrides))->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    'no recipients' => [['recipients' => []], 'recipients'],
    'too many recipients' => [['recipients' => range(1, 101)], 'recipients'],
    'a recipient that is not a number' => [['recipients' => ['abc']], 'recipients.0'],
    'a zero id' => [['recipients' => [0]], 'recipients.0'],
    'an id past the int column' => [['recipients' => [2147483648]], 'recipients.0'],
    'an account number with odd characters' => [['by' => 'account_no', 'recipients' => ['KK-1; DROP TABLE']], 'recipients.0'],
    'an unknown identifier type' => [['by' => 'email'], 'by'],
    'a missing title' => [['title' => ''], 'title'],
    'a title that is only control characters' => [['title' => "\x07\x08"], 'title'],
    'a title that is too long' => [['title' => str_repeat('a', 66)], 'title'],
    'a missing body' => [['body' => ''], 'body'],
    'a body that is too long' => [['body' => str_repeat('a', 241)], 'body'],
    'an absolute url' => [['url' => 'https://evil.example/phish'], 'url'],
    'a protocol-relative url' => [['url' => '//evil.example'], 'url'],
    'a backslash trick' => [['url' => '/\\evil.example'], 'url'],
    'a url with whitespace' => [['url' => '/wallet now'], 'url'],
    'a javascript url' => [['url' => 'javascript:alert(1)'], 'url'],
    'a tag with spaces' => [['tag' => 'has spaces'], 'tag'],
    'high urgency is reserved for security alerts' => [['urgency' => 'high'], 'urgency'],
    'an unknown urgency' => [['urgency' => 'critical'], 'urgency'],
    'a ttl that is too short' => [['ttl' => 5], 'ttl'],
    'a ttl that is too long' => [['ttl' => 999999], 'ttl'],
]);

test('the idempotency key header is validated', function (string $value) {
    callApi(apiKey(), apiPayload(), ['Idempotency-Key' => $value])->assertUnprocessable()->assertJsonValidationErrors('idempotency_key');
})->with(['too short' => ['abc'], 'illegal characters' => ['has spaces in it!!'], 'too long' => [str_repeat('a', 65)]]);

test('control characters are stripped from the text and the url defaults to the home page', function () {
    Notification::fake();
    $user = apiPlayer(['linked_id' => 510]);

    callApi(apiKey(), apiPayload(['recipients' => [510], 'title' => "  Your\x07 turn  ", 'body' => "Move\x00 now"]))->assertStatus(202);

    Notification::assertSentTo($user, PushMessageNotification::class, fn ($n) => $n->title === 'Your turn' && $n->body === 'Move now' && $n->url === '/');
});

// --- Delivery --------------------------------------------------------------

test('it pushes to players with a device and reports everyone it could not reach', function () {
    Notification::fake();
    $reachable = apiPlayer(['linked_id' => 601]);
    $noDevice = apiPlayer(['linked_id' => 602], withDevice: false);

    callApi(apiKey(), apiPayload(['recipients' => [601, 602, 603, 604]]))
        ->assertStatus(202)
        ->assertJson([
            'status' => 'accepted',
            'requested' => 4,
            'queued' => 1,
            'skipped' => ['unknown' => [603, 604], 'no_device' => 1, 'throttled' => 0, 'ambiguous' => 0],
        ])
        ->assertJsonStructure(['id']);

    Notification::assertSentTo($reachable, PushMessageNotification::class);
    Notification::assertNotSentTo($noDevice, PushMessageNotification::class);
});

test('the message reaches the notification with sensible defaults, or with the caller\'s options', function () {
    Notification::fake();
    $user = apiPlayer(['linked_id' => 610]);
    $key = apiKey();

    callApi($key, apiPayload(['recipients' => [610]]))->assertStatus(202);
    Notification::assertSentTo($user, PushMessageNotification::class, fn ($n) => $n->title === 'Your turn'
        && $n->body === 'It is your move in table 7.' && $n->url === '/' && $n->tag === null
        && $n->ttl === 900 && $n->urgency === 'normal');

    callApi($key, apiPayload(['recipients' => [610], 'url' => '/wallet', 'tag' => 'table-7', 'ttl' => 60, 'urgency' => 'low']))->assertStatus(202);
    Notification::assertSentTo($user, PushMessageNotification::class, fn ($n) => $n->url === '/wallet'
        && $n->tag === 'table-7' && $n->ttl === 60 && $n->urgency === 'low');
});

test('recipients can be addressed by account number, ignoring case', function () {
    Notification::fake();
    $user = apiPlayer(['account_no' => 'KK-ABC123']);

    callApi(apiKey(), apiPayload(['by' => 'account_no', 'recipients' => ['kk-abc123', 'KK-NOPE']]))
        ->assertStatus(202)
        ->assertJsonPath('queued', 1)
        ->assertJsonPath('skipped.unknown', ['kk-nope']);

    Notification::assertSentTo($user, PushMessageNotification::class);
});

test('a duplicated recipient is pushed once', function () {
    Notification::fake();
    $user = apiPlayer(['linked_id' => 620]);

    callApi(apiKey(), apiPayload(['recipients' => [620, 620, '620']]))->assertStatus(202)->assertJsonPath('requested', 1);

    Notification::assertSentToTimes($user, PushMessageNotification::class, 1);
});

test('an id that maps to more than one account is skipped rather than guessed', function () {
    Notification::fake();
    [$first, $second] = [apiPlayer(['linked_id' => 630]), apiPlayer(['linked_id' => 630])];

    callApi(apiKey(), apiPayload(['recipients' => [630]]))
        ->assertStatus(202)
        ->assertJsonPath('queued', 0)
        ->assertJsonPath('skipped.ambiguous', 1);

    Notification::assertNotSentTo($first, PushMessageNotification::class);
    Notification::assertNotSentTo($second, PushMessageNotification::class);
});

test('one player can only be pushed so often per hour', function () {
    Notification::fake();
    $user = apiPlayer(['linked_id' => 640]);
    $key = apiKey();
    config(['kadi.push_api.per_user_per_hour' => 2]);

    callApi($key, apiPayload(['recipients' => [640]]))->assertJsonPath('queued', 1);
    callApi($key, apiPayload(['recipients' => [640]]))->assertJsonPath('queued', 1);
    callApi($key, apiPayload(['recipients' => [640]]))->assertJsonPath('queued', 0)->assertJsonPath('skipped.throttled', 1);

    Notification::assertSentToTimes($user, PushMessageNotification::class, 2);
});

test('pushes are queued, one job per player, never sent inside the request', function () {
    Queue::fake();
    apiPlayer(['linked_id' => 650]);
    apiPlayer(['linked_id' => 651]);

    callApi(apiKey(), apiPayload(['recipients' => [650, 651]]))->assertStatus(202)->assertJsonPath('queued', 2);

    Queue::assertPushed(SendQueuedNotifications::class, 2);
});

test('the response never contains the key, its hash or anything else secret', function () {
    Notification::fake();
    apiPlayer(['linked_id' => 660]);
    $key = apiKey();

    $body = callApi($key, apiPayload(['recipients' => [660]]))->assertStatus(202)->getContent();

    expect($body)->not->toContain($key)->not->toContain(hash('sha256', $key));
    expect(array_keys(json_decode($body, true)))->toBe(['status', 'id', 'requested', 'queued', 'skipped']);
});

test('the audit log records counts and the key id, never the message, the players or the key', function () {
    Notification::fake();
    apiPlayer(['linked_id' => 670]);
    $key = apiKey();
    $entries = [];

    Log::listen(function ($event) use (&$entries) {
        if ($event->message === 'Push API request') {
            $entries[] = $event;
        }
    });

    callApi($key, apiPayload(['recipients' => [670, 671], 'title' => 'SECRET-TITLE', 'body' => 'SECRET-BODY']))->assertStatus(202);

    expect($entries)->toHaveCount(1);
    $context = $entries[0]->context;

    expect($context['key'])->toBe(substr(hash('sha256', $key), 0, 8))
        ->and($context)->toMatchArray(['requested' => 2, 'queued' => 1, 'unknown' => 1, 'no_device' => 0]);

    $logged = json_encode($context);
    expect($logged)->not->toContain('SECRET-TITLE')->not->toContain('SECRET-BODY')->not->toContain($key)->not->toContain('670');
});

// --- Idempotency -----------------------------------------------------------

test('a retried request with the same Idempotency-Key does not send a second push', function () {
    Notification::fake();
    $user = apiPlayer(['linked_id' => 700]);
    $key = apiKey();
    $headers = ['Idempotency-Key' => 'game-42-turn-9'];

    $first = callApi($key, apiPayload(['recipients' => [700]]), $headers)->assertStatus(202)->assertHeaderMissing('Idempotent-Replay');
    $second = callApi($key, apiPayload(['recipients' => [700]]), $headers)->assertStatus(202)->assertHeader('Idempotent-Replay', 'true');

    expect($second->json())->toBe($first->json());
    Notification::assertSentToTimes($user, PushMessageNotification::class, 1);
});

test('reusing an Idempotency-Key for a different request is refused', function () {
    Notification::fake();
    apiPlayer(['linked_id' => 701]);
    $key = apiKey();
    $headers = ['Idempotency-Key' => 'reused-key-001'];

    callApi($key, apiPayload(['recipients' => [701]]), $headers)->assertStatus(202);
    callApi($key, apiPayload(['recipients' => [701], 'body' => 'A different message']), $headers)
        ->assertStatus(422)
        ->assertJsonPath('message', 'This Idempotency-Key was already used with a different request.');
});

test('the same request with the recipients in another order still counts as a retry', function () {
    Notification::fake();
    $a = apiPlayer(['linked_id' => 702]);
    $b = apiPlayer(['linked_id' => 703]);
    $key = apiKey();
    $headers = ['Idempotency-Key' => 'order-independent-1'];

    callApi($key, apiPayload(['recipients' => [702, 703]]), $headers)->assertStatus(202);
    callApi($key, apiPayload(['recipients' => [703, 702]]), $headers)->assertHeader('Idempotent-Replay', 'true');

    Notification::assertSentToTimes($a, PushMessageNotification::class, 1);
    Notification::assertSentToTimes($b, PushMessageNotification::class, 1);
});

test('without an Idempotency-Key every request sends', function () {
    Notification::fake();
    $user = apiPlayer(['linked_id' => 704]);
    $key = apiKey();

    callApi($key, apiPayload(['recipients' => [704]]))->assertStatus(202);
    callApi($key, apiPayload(['recipients' => [704]]))->assertStatus(202);

    Notification::assertSentToTimes($user, PushMessageNotification::class, 2);
});

test('idempotency keys are scoped to the api key that used them', function () {
    Notification::fake();
    $user = apiPlayer(['linked_id' => 705]);
    $a = 'kpk_'.bin2hex(random_bytes(32));
    $b = 'kpk_'.bin2hex(random_bytes(32));
    config(['kadi.push_api.key_hashes' => [hash('sha256', $a), hash('sha256', $b)]]);
    $headers = ['Idempotency-Key' => 'shared-name-001'];

    callApi($a, apiPayload(['recipients' => [705]]), $headers)->assertHeaderMissing('Idempotent-Replay');
    callApi($b, apiPayload(['recipients' => [705]]), $headers)->assertHeaderMissing('Idempotent-Replay');

    Notification::assertSentToTimes($user, PushMessageNotification::class, 2);
});

test('a request still being processed under the same Idempotency-Key gets a 409', function () {
    Notification::fake();
    apiPlayer(['linked_id' => 706]);
    $key = apiKey();

    $lock = Cache::lock('push-api:idempotency:'.substr(hash('sha256', $key), 0, 8).':busy-key-0001:lock', 30);
    expect($lock->get())->toBeTrue();

    callApi($key, apiPayload(['recipients' => [706]]), ['Idempotency-Key' => 'busy-key-0001'])->assertStatus(409);

    $lock->release();
});

// --- The key generator -----------------------------------------------------

test('push-api:key prints a key and the matching hash, and never the same twice', function () {
    $run = function () {
        Artisan::call('push-api:key');
        $output = preg_replace('/\e\[[0-9;]*m/', '', Artisan::output());

        expect(preg_match('/(kpk_[a-f0-9]{64})/', $output, $key))->toBe(1);
        expect(preg_match('/PUSH_API_KEY_HASHES=([a-f0-9]{64})/', $output, $hash))->toBe(1);

        return [$key[1], $hash[1]];
    };

    [$key1, $hash1] = $run();
    [$key2, $hash2] = $run();

    expect($hash1)->toBe(hash('sha256', $key1))
        ->and($hash2)->toBe(hash('sha256', $key2))
        ->and($key1)->not->toBe($key2);

    // A generated key really authenticates against the hash it printed.
    config(['kadi.push_api.key_hashes' => [$hash1]]);
    Notification::fake();
    callApi($key1, apiPayload())->assertStatus(202);
    callApi($key2, apiPayload())->assertUnauthorized();
});
