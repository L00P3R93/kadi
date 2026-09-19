<?php

use App\Http\Middleware\AuthenticatePushApiKey;
use App\Jobs\SendPushBroadcast;
use App\Jobs\SendPushBroadcastChunk;
use App\Models\PushBroadcast;
use App\Models\User;
use App\Notifications\PushBroadcastNotification;
use App\Push\PushBroadcaster;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Minishlink\WebPush\MessageSentReport;
use NotificationChannels\WebPush\PushSubscription;
use NotificationChannels\WebPush\WebPushChannel;

/**
 * System-wide broadcasts reach EVERY registered device, so they are the highest-impact thing the
 * push API can do. These tests cover who may trigger one, how often, how it is split and sent, and
 * how it is tracked and cancelled.
 */
function bcKey(): string
{
    $key = 'kpk_'.bin2hex(random_bytes(32));
    config(['kadi.push_api.broadcast_key_hashes' => [hash('sha256', $key)]]);

    return $key;
}

function bcUrl(string $suffix = ''): string
{
    return '/api/v1/push-broadcasts'.$suffix;
}

function bcPayload(array $overrides = []): array
{
    return array_replace(['title' => 'Maintenance tonight', 'body' => 'Kadi is offline 1am-2am EAT.'], $overrides);
}

function bcSend(string $key, array $payload = [], array $headers = [])
{
    return test()->withToken($key)->postJson(bcUrl(), bcPayload($payload), $headers + ['Idempotency-Key' => 'bc-'.bin2hex(random_bytes(6))]);
}

/** Registers a device; guests and logged-out users are just rows in the table, owned or not. */
function bcDevices(int $count): void
{
    $owner = User::factory()->create();

    for ($i = 0; $i < $count; $i++) {
        $owner->pushSubscriptions()->create([
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/'.bin2hex(random_bytes(12)),
            'public_key' => 'BKey',
            'auth_token' => 'authtoken',
        ]);
    }
}

function bcReport(int $status, bool $success): MessageSentReport
{
    return new MessageSentReport(new PsrRequest('POST', 'https://fcm.googleapis.com/fcm/send/x'), new PsrResponse($status), $success);
}

function bcBroadcast(array $overrides = []): PushBroadcast
{
    return PushBroadcast::create($overrides + [
        'source' => 'api', 'key_id' => 'abcd1234', 'title' => 'T', 'body' => 'B', 'url' => '/', 'urgency' => 'normal',
        'ttl' => 3600, 'status' => PushBroadcast::QUEUED,
    ]);
}

// --- Authentication --------------------------------------------------------

test('broadcasts are disabled, and refuse everyone, until a broadcast key hash is configured', function () {
    config(['kadi.push_api.broadcast_key_hashes' => []]);

    // Even a perfectly valid per-player key must not open the broadcast endpoint.
    $playerKey = 'kpk_'.bin2hex(random_bytes(32));
    config(['kadi.push_api.key_hashes' => [hash('sha256', $playerKey)]]);

    test()->withToken($playerKey)->postJson(bcUrl(), bcPayload())->assertStatus(503);
});

test('the per-player key cannot broadcast, and the broadcast key cannot push to players', function () {
    $playerKey = 'kpk_'.bin2hex(random_bytes(32));
    $broadcastKey = bcKey();
    config(['kadi.push_api.key_hashes' => [hash('sha256', $playerKey)]]);

    test()->withToken($playerKey)->postJson(bcUrl(), bcPayload(), ['Idempotency-Key' => 'wrong-key-0001'])->assertUnauthorized();

    test()->withToken($broadcastKey)->postJson(route('api.v1.push-notifications.store'), ['recipients' => [1], 'title' => 't', 'body' => 'b'])
        ->assertUnauthorized();

    Queue::fake();
    bcSend($broadcastKey)->assertStatus(202);
});

test('missing and wrong keys get 401 JSON', function () {
    bcKey();

    test()->flushHeaders()->postJson(bcUrl(), bcPayload())->assertUnauthorized()->assertHeader('WWW-Authenticate', 'Bearer');
    test()->withToken('kpk_'.str_repeat('a', 64))->postJson(bcUrl(), bcPayload())->assertUnauthorized();
});

test('the broadcast routes are outside the web group and authenticate before they throttle', function () {
    foreach (['store', 'show', 'destroy'] as $name) {
        $route = Route::getRoutes()->getByName("api.v1.push-broadcasts.{$name}");
        $middleware = $route->gatherMiddleware();

        expect($middleware)->toContain('api')->not->toContain('web');

        $order = array_values(array_map(
            fn ($m) => is_string($m) ? strtok($m, ':') : $m::class,
            app('router')->gatherRouteMiddleware($route),
        ));

        expect(array_search(AuthenticatePushApiKey::class, $order, true))->toBeLessThan(array_search(ThrottleRequests::class, $order, true));
    }
});

test('the broadcast key ids in the middleware come from the broadcast list', function () {
    $key = bcKey();

    Queue::fake();
    bcSend($key)->assertStatus(202);

    expect(PushBroadcast::first()->key_id)->toBe(substr(hash('sha256', $key), 0, 8));
});

// --- Validation ------------------------------------------------------------

test('the broadcast payload is validated', function (array $overrides, string $field) {
    bcSend(bcKey(), $overrides)->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    'no title' => [['title' => ''], 'title'],
    'title too long' => [['title' => str_repeat('a', 66)], 'title'],
    'body too long' => [['body' => str_repeat('a', 241)], 'body'],
    'absolute url' => [['url' => 'https://evil.example/'], 'url'],
    'protocol relative url' => [['url' => '//evil.example'], 'url'],
    'high urgency is reserved' => [['urgency' => 'high'], 'urgency'],
    'ttl too short' => [['ttl' => 5], 'ttl'],
    'ttl too long' => [['ttl' => 86401], 'ttl'],
    'bad tag' => [['tag' => 'has space'], 'tag'],
]);

test('a real broadcast must carry an Idempotency-Key, so a retry cannot announce twice', function () {
    Queue::fake();

    test()->withToken(bcKey())->postJson(bcUrl(), bcPayload())->assertUnprocessable()->assertJsonValidationErrors('idempotency_key');

    expect(PushBroadcast::count())->toBe(0);
    Queue::assertNothingPushed();
});

test('recipients are not part of a broadcast', function () {
    Queue::fake();

    bcSend(bcKey(), ['recipients' => [1, 2, 3]])->assertStatus(202);

    // Nobody is picked: the broadcast row has no such thing, and it goes to every device.
    expect(PushBroadcast::first()->getAttributes())->not->toHaveKey('recipients');
});

// --- Dry run ---------------------------------------------------------------

test('a dry run reports the audience and sends nothing', function () {
    Queue::fake();
    bcDevices(3);

    test()->withToken(bcKey())->postJson(bcUrl(), bcPayload(['dry_run' => true]))
        ->assertOk()->assertExactJson(['status' => 'dry_run', 'devices' => 3]);

    expect(PushBroadcast::count())->toBe(0);
    Queue::assertNothingPushed();
});

// --- Creating a broadcast --------------------------------------------------

test('a broadcast is accepted, stored and queued, never sent inside the request', function () {
    Queue::fake();
    bcDevices(2);

    $response = bcSend(bcKey(), ['url' => '/wallet', 'tag' => 'maintenance', 'ttl' => 7200, 'urgency' => 'low'])
        ->assertStatus(202)->assertJsonPath('status', 'accepted');

    $broadcast = PushBroadcast::findOrFail($response->json('id'));

    expect($broadcast->status)->toBe('queued')
        ->and($broadcast->source)->toBe('api')
        ->and($broadcast->title)->toBe('Maintenance tonight')
        ->and($broadcast->url)->toBe('/wallet')
        ->and($broadcast->tag)->toBe('maintenance')
        ->and($broadcast->ttl)->toBe(7200)
        ->and($broadcast->urgency)->toBe('low');

    Queue::assertPushed(SendPushBroadcast::class, fn ($job) => $job->broadcastId === $broadcast->id);
    Queue::assertNotPushed(SendPushBroadcastChunk::class);
});

test('defaults: a longer time to live, the site root and normal urgency', function () {
    Queue::fake();

    bcSend(bcKey())->assertStatus(202);

    $broadcast = PushBroadcast::first();
    expect($broadcast->ttl)->toBe(3600)->and($broadcast->url)->toBe('/')->and($broadcast->urgency)->toBe('normal');
});

test('control characters are stripped from the text', function () {
    Queue::fake();

    bcSend(bcKey(), ['title' => "Hello\x00\x07 world", 'body' => "Line\x1B one"])->assertStatus(202);

    expect(PushBroadcast::first()->title)->toBe('Hello world')->and(PushBroadcast::first()->body)->toBe('Line one');
});

test('a retried request with the same Idempotency-Key never announces twice', function () {
    Queue::fake();
    $key = bcKey();
    $headers = ['Idempotency-Key' => 'announce-2026-09-19'];

    $first = test()->withToken($key)->postJson(bcUrl(), bcPayload(), $headers)->assertStatus(202);
    $second = test()->withToken($key)->postJson(bcUrl(), bcPayload(), $headers)->assertStatus(202)->assertHeader('Idempotent-Replay', 'true');

    expect($second->json('id'))->toBe($first->json('id'))->and(PushBroadcast::count())->toBe(1);
    Queue::assertPushed(SendPushBroadcast::class, 1);
});

test('the same Idempotency-Key with different text is refused, not silently dropped', function () {
    Queue::fake();
    $key = bcKey();
    $headers = ['Idempotency-Key' => 'announce-2026-09-20'];

    test()->withToken($key)->postJson(bcUrl(), bcPayload(), $headers)->assertStatus(202);
    test()->withToken($key)->postJson(bcUrl(), bcPayload(['body' => 'Something else']), $headers)->assertUnprocessable();

    expect(PushBroadcast::count())->toBe(1);
});

test('a broadcast idempotency key does not collide with a per-player one', function () {
    Queue::fake();
    $key = bcKey();

    Cache::put('push-api:idempotency:'.substr(hash('sha256', $key), 0, 8).':shared-key-01', ['fingerprint' => 'x', 'body' => ['status' => 'accepted']], 60);

    test()->withToken($key)->postJson(bcUrl(), bcPayload(), ['Idempotency-Key' => 'shared-key-01'])->assertStatus(202)->assertHeaderMissing('Idempotent-Replay');
});

test('a request still in progress under the same Idempotency-Key gets a 409', function () {
    Queue::fake();
    $key = bcKey();
    $lock = Cache::lock('push-api:idempotency:broadcast:'.substr(hash('sha256', $key), 0, 8).':busy-key-0001:lock', 30);
    $lock->get();

    test()->withToken($key)->postJson(bcUrl(), bcPayload(), ['Idempotency-Key' => 'busy-key-0001'])->assertStatus(409);

    $lock->release();
});

// --- Frequency caps --------------------------------------------------------

test('a key can only broadcast so often per hour', function () {
    Queue::fake();
    config(['kadi.push_api.broadcast_per_hour' => 2, 'kadi.push_api.broadcast_per_day' => 100]);
    $key = bcKey();

    bcSend($key)->assertStatus(202);
    bcSend($key)->assertStatus(202);
    $blocked = bcSend($key)->assertStatus(429)->assertHeader('Retry-After');

    expect((int) $blocked->headers->get('Retry-After'))->toBeGreaterThan(0)->toBeLessThanOrEqual(3600)
        ->and(PushBroadcast::count())->toBe(2);
});

test('there is also a total daily cap across every key and the command', function () {
    Queue::fake();
    config(['kadi.push_api.broadcast_per_hour' => 100, 'kadi.push_api.broadcast_per_day' => 2]);

    bcBroadcast(['key_id' => null, 'source' => 'cli']);
    bcBroadcast(['key_id' => 'ffff0000']);

    bcSend(bcKey())->assertStatus(429)->assertJsonPath('message', fn ($m) => str_contains($m, '24 hours'));
});

test('old broadcasts stop counting once they leave the window', function () {
    Queue::fake();
    config(['kadi.push_api.broadcast_per_hour' => 1, 'kadi.push_api.broadcast_per_day' => 1]);

    $old = bcBroadcast();
    $old->forceFill(['created_at' => now()->subHours(25)])->save();

    bcSend(bcKey())->assertStatus(202);
});

test('a refused broadcast is not remembered, so it can be retried once the cap clears', function () {
    Queue::fake();
    config(['kadi.push_api.broadcast_per_hour' => 1, 'kadi.push_api.broadcast_per_day' => 10]);
    $key = bcKey();
    $headers = ['Idempotency-Key' => 'retry-after-cap-1'];

    test()->withToken($key)->postJson(bcUrl(), bcPayload(), $headers)->assertStatus(202);
    test()->withToken($key)->postJson(bcUrl(), bcPayload(['body' => 'Second']), ['Idempotency-Key' => 'retry-after-cap-2'])->assertStatus(429);

    PushBroadcast::query()->update(['created_at' => now()->subHours(2)]);

    test()->withToken($key)->postJson(bcUrl(), bcPayload(['body' => 'Second']), ['Idempotency-Key' => 'retry-after-cap-2'])->assertStatus(202);
});

test('a dry run does not use up the caps', function () {
    Queue::fake();
    config(['kadi.push_api.broadcast_per_hour' => 1]);
    $key = bcKey();

    test()->withToken($key)->postJson(bcUrl(), bcPayload(['dry_run' => true]))->assertOk();
    bcSend($key)->assertStatus(202);
});

// --- Status and cancel -----------------------------------------------------

test('progress can be read', function () {
    $broadcast = bcBroadcast(['status' => PushBroadcast::SENDING, 'devices_total' => 10, 'sent' => 6, 'failed' => 1, 'expired_removed' => 2]);

    test()->withToken(bcKey())->getJson(bcUrl("/{$broadcast->id}"))->assertOk()
        ->assertJsonPath('status', 'sending')
        ->assertJsonPath('devices_total', 10)
        ->assertJsonPath('sent', 6)
        ->assertJsonPath('failed', 1)
        ->assertJsonPath('expired_removed', 2)
        ->assertJsonMissingPath('title');
});

test('an unknown or malformed broadcast id is a JSON 404', function () {
    $key = bcKey();

    test()->withToken($key)->getJson(bcUrl('/'.fake()->uuid()))->assertNotFound();
    test()->withToken($key)->getJson(bcUrl('/not-a-uuid'))->assertNotFound();
    test()->withToken($key)->deleteJson(bcUrl("/1' OR '1'='1"))->assertNotFound();
});

test('a broadcast that is queued or sending can be cancelled', function (string $status) {
    $broadcast = bcBroadcast(['status' => $status]);

    test()->withToken(bcKey())->deleteJson(bcUrl("/{$broadcast->id}"))->assertOk()->assertJsonPath('status', 'cancelled');

    expect($broadcast->fresh()->status)->toBe('cancelled')->and($broadcast->fresh()->finished_at)->not->toBeNull();
})->with(['queued', 'sending']);

test('a finished broadcast cannot be cancelled', function (string $status) {
    $broadcast = bcBroadcast(['status' => $status]);

    test()->withToken(bcKey())->deleteJson(bcUrl("/{$broadcast->id}"))->assertStatus(409);

    expect($broadcast->fresh()->status)->toBe($status);
})->with(['done', 'cancelled']);

// --- The jobs --------------------------------------------------------------

test('the dispatcher splits every device, owned or not, into chunks of the configured size', function () {
    Queue::fake([SendPushBroadcastChunk::class]);
    config(['kadi.push_api.broadcast_chunk_size' => 2]);
    bcDevices(5);

    $broadcast = bcBroadcast();
    (new SendPushBroadcast($broadcast->id))->handle();

    $ids = PushSubscription::orderBy('id')->pluck('id')->all();

    Queue::assertPushed(SendPushBroadcastChunk::class, 3);
    Queue::assertPushed(SendPushBroadcastChunk::class, fn ($j) => $j->fromId === $ids[0] && $j->toId === $ids[1]);
    Queue::assertPushed(SendPushBroadcastChunk::class, fn ($j) => $j->fromId === $ids[4] && $j->toId === $ids[4]);

    $fresh = $broadcast->fresh();
    expect($fresh->status)->toBe('sending')->and($fresh->devices_total)->toBe(5)->and($fresh->chunks_total)->toBe(3)->and($fresh->started_at)->not->toBeNull();
});

test('exact multiples and single devices split correctly', function (int $devices, int $size, int $chunks) {
    Queue::fake([SendPushBroadcastChunk::class]);
    config(['kadi.push_api.broadcast_chunk_size' => $size]);
    bcDevices($devices);

    (new SendPushBroadcast(bcBroadcast()->id))->handle();

    Queue::assertPushed(SendPushBroadcastChunk::class, $chunks);
})->with([[1, 200, 1], [4, 2, 2], [200, 200, 1], [201, 200, 2]]);

test('with no devices the broadcast finishes immediately', function () {
    Queue::fake();

    $broadcast = bcBroadcast();
    (new SendPushBroadcast($broadcast->id))->handle();

    expect($broadcast->fresh()->status)->toBe('done')->and($broadcast->fresh()->devices_total)->toBe(0);
    Queue::assertNothingPushed();
});

test('the dispatcher is harmless when retried, cancelled or already running', function (string $status) {
    Queue::fake();
    bcDevices(3);

    $broadcast = bcBroadcast(['status' => $status]);
    (new SendPushBroadcast($broadcast->id))->handle();

    Queue::assertNothingPushed();
    expect($broadcast->fresh()->status)->toBe($status);
})->with(['sending', 'done', 'cancelled']);

test('a chunk sends the announcement through the web push channel and counts the outcomes', function () {
    bcDevices(4);
    $broadcast = bcBroadcast(['title' => 'Maintenance', 'body' => 'Back at 2am', 'url' => '/wallet', 'urgency' => 'low', 'ttl' => 7200, 'chunks_total' => 2, 'status' => 'sending']);
    $ids = PushSubscription::orderBy('id')->pluck('id');

    $seen = [];
    $this->mock(WebPushChannel::class, function ($mock) use (&$seen) {
        $mock->shouldReceive('send')->once()->andReturnUsing(function ($audience, $notification) use (&$seen) {
            $seen = ['count' => $audience->routeNotificationFor('WebPush')->count(), 'notification' => $notification];

            return [bcReport(201, true), bcReport(201, true), bcReport(410, false), bcReport(500, false)];
        });
    });

    dispatch_sync(new SendPushBroadcastChunk($broadcast->id, $ids->first(), $ids->last()));

    expect($seen['count'])->toBe(4)
        ->and($seen['notification'])->toBeInstanceOf(PushBroadcastNotification::class)
        ->and($seen['notification']->title)->toBe('Maintenance')
        ->and($seen['notification']->url)->toBe('/wallet')
        ->and($seen['notification']->urgency)->toBe('low')
        ->and($seen['notification']->ttl)->toBe(7200);

    $fresh = $broadcast->fresh();
    expect($fresh->sent)->toBe(2)->and($fresh->failed)->toBe(1)->and($fresh->expired_removed)->toBe(1)
        ->and($fresh->chunks_done)->toBe(1)->and($fresh->status)->toBe('sending'); // one of two chunks
});

test('without an explicit tag each broadcast gets its own, so a retried chunk replaces rather than stacks', function () {
    $a = PushBroadcastNotification::for(bcBroadcast());
    $b = PushBroadcastNotification::for(bcBroadcast(['tag' => 'maintenance']));

    expect($a->tag)->toStartWith('broadcast-')->and($b->tag)->toBe('maintenance');
});

test('the broadcast finishes exactly once, when the last chunk is done, with totals added up', function () {
    bcDevices(5);
    config(['kadi.push_api.broadcast_chunk_size' => 2]);

    $this->mock(WebPushChannel::class, fn ($mock) => $mock->shouldReceive('send')->andReturnUsing(
        fn ($audience) => $audience->routeNotificationFor('WebPush')->map(fn () => bcReport(201, true))->all(),
    ));

    $broadcast = bcBroadcast();

    // The sync queue runs the dispatcher and every chunk inside this call.
    SendPushBroadcast::dispatchSync($broadcast->id);

    $fresh = $broadcast->fresh();
    expect($fresh->status)->toBe('done')
        ->and($fresh->devices_total)->toBe(5)
        ->and($fresh->sent)->toBe(5)
        ->and($fresh->chunks_done)->toBe(3)->and($fresh->chunks_total)->toBe(3)
        ->and($fresh->finished_at)->not->toBeNull();
});

test('a cancelled broadcast sends nothing and stays cancelled', function () {
    bcDevices(3);
    $this->mock(WebPushChannel::class)->shouldNotReceive('send');

    $broadcast = bcBroadcast(['status' => 'cancelled', 'chunks_total' => 1, 'finished_at' => now()]);
    $ids = PushSubscription::pluck('id');

    dispatch_sync(new SendPushBroadcastChunk($broadcast->id, $ids->min(), $ids->max()));

    expect($broadcast->fresh()->status)->toBe('cancelled')->and($broadcast->fresh()->sent)->toBe(0);
});

test('cancelling part way stops the chunks that have not run yet', function () {
    bcDevices(4);
    config(['kadi.push_api.broadcast_chunk_size' => 2]);

    $calls = 0;
    $this->mock(WebPushChannel::class, function ($mock) use (&$calls, &$broadcast) {
        $mock->shouldReceive('send')->andReturnUsing(function ($audience) use (&$calls, &$broadcast) {
            $calls++;
            // The operator cancels while the first chunk is in flight.
            app(PushBroadcaster::class)->cancel($broadcast);

            return $audience->routeNotificationFor('WebPush')->map(fn () => bcReport(201, true))->all();
        });
    });

    $broadcast = bcBroadcast();
    SendPushBroadcast::dispatchSync($broadcast->id);

    expect($calls)->toBe(1)->and($broadcast->fresh()->status)->toBe('cancelled');
});

test('a chunk that fails for good is counted as failed devices and still lets the broadcast finish', function () {
    bcDevices(2);
    $ids = PushSubscription::pluck('id');
    $broadcast = bcBroadcast(['status' => 'sending', 'chunks_total' => 1, 'devices_total' => 2]);

    (new SendPushBroadcastChunk($broadcast->id, $ids->min(), $ids->max()))->failed(new RuntimeException('https://fcm.googleapis.com/fcm/send/secret-token'));

    $fresh = $broadcast->fresh();
    expect($fresh->failed)->toBe(2)->and($fresh->status)->toBe('done');
});

test('chunk jobs keep the nested queue limits and never carry device data', function () {
    $job = new SendPushBroadcastChunk('id', 1, 200);

    expect($job->timeout)->toBe(60)->toBeLessThan(90)->and($job->tries)->toBe(3)->and($job->backoff)->toBe([10, 60]);

    // Ids only: no endpoint, key or text travels through the queue payload.
    expect(json_encode(get_object_vars($job)))->not->toContain('endpoint')->not->toContain('auth');
});

test('logs hold counts and ids only: never text, endpoints or keys', function () {
    bcDevices(2);
    Log::spy();
    $this->mock(WebPushChannel::class, fn ($mock) => $mock->shouldReceive('send')->andReturn([bcReport(201, true), bcReport(201, true)]));

    $key = bcKey();
    bcSend($key, ['title' => 'Secret headline', 'body' => 'Secret body text'])->assertStatus(202);

    Log::shouldHaveReceived('info')->atLeast()->once();
    Log::shouldHaveReceived('info')->withArgs(function ($message, $context = []) use ($key) {
        $flat = $message.json_encode($context);

        expect($flat)->not->toContain('Secret')->not->toContain('fcm.googleapis')->not->toContain($key);

        return true;
    });
});

// --- The command -----------------------------------------------------------

test('push:broadcast --dry-run counts devices and sends nothing', function () {
    Queue::fake();
    bcDevices(3);

    $this->artisan('push:broadcast', ['--title' => 'Hi', '--body' => 'There', '--dry-run' => true])
        ->expectsOutputToContain('3 registered devices')->assertSuccessful();

    expect(PushBroadcast::count())->toBe(0);
    Queue::assertNothingPushed();
});

test('push:broadcast validates with the same rules as the API', function () {
    Queue::fake();
    bcDevices(1);

    $this->artisan('push:broadcast', ['--title' => str_repeat('a', 66), '--body' => 'x', '--yes' => true])->assertExitCode(2);
    $this->artisan('push:broadcast', ['--title' => 'ok', '--body' => 'x', '--urgency' => 'high', '--yes' => true])->assertExitCode(2);
    $this->artisan('push:broadcast', ['--title' => 'ok', '--body' => 'x', '--url' => 'https://evil.example', '--yes' => true])->assertExitCode(2);

    expect(PushBroadcast::count())->toBe(0);
});

test('push:broadcast asks before sending to everyone, and creates a queued broadcast when told to', function () {
    Queue::fake();
    bcDevices(2);

    $this->artisan('push:broadcast', ['--title' => 'Hi', '--body' => 'There'])
        ->expectsConfirmation('Send this to 2 devices?', 'no')->expectsOutputToContain('Nothing was sent')->assertSuccessful();

    expect(PushBroadcast::count())->toBe(0);

    $this->artisan('push:broadcast', ['--title' => 'Hi', '--body' => 'There'])
        ->expectsConfirmation('Send this to 2 devices?', 'yes')->assertSuccessful();

    $broadcast = PushBroadcast::firstOrFail();
    expect($broadcast->source)->toBe('cli')->and($broadcast->key_id)->toBeNull();
    Queue::assertPushed(SendPushBroadcast::class);
});

test('push:broadcast obeys the daily cap unless --force is given', function () {
    Queue::fake();
    bcDevices(1);
    config(['kadi.push_api.broadcast_per_day' => 1]);
    bcBroadcast();

    $this->artisan('push:broadcast', ['--title' => 'Hi', '--body' => 'There', '--yes' => true])->expectsOutputToContain('--force')->assertFailed();
    expect(PushBroadcast::count())->toBe(1);

    $this->artisan('push:broadcast', ['--title' => 'Hi', '--body' => 'There', '--yes' => true, '--force' => true])->assertSuccessful();
    expect(PushBroadcast::count())->toBe(2);
});

test('push:broadcast shows progress and cancels', function () {
    $broadcast = bcBroadcast(['status' => 'sending', 'sent' => 7]);

    $this->artisan('push:broadcast', ['--status' => $broadcast->id])->expectsOutputToContain('sending')->assertSuccessful();
    $this->artisan('push:broadcast', ['--cancel' => $broadcast->id])->assertSuccessful();
    expect($broadcast->fresh()->status)->toBe('cancelled');

    $this->artisan('push:broadcast', ['--cancel' => $broadcast->id])->assertFailed();
    $this->artisan('push:broadcast', ['--status' => 'nope'])->assertFailed();
});

test('push-api:key --broadcast prints the broadcast variable, and the plain command the player one', function () {
    $this->artisan('push-api:key', ['--broadcast' => true])->expectsOutputToContain('PUSH_API_BROADCAST_KEY_HASHES=')->assertSuccessful();
    $this->artisan('push-api:key')->expectsOutputToContain('PUSH_API_KEY_HASHES=')->assertSuccessful();
});

test('broadcast key hashes in .env are parsed as strictly as the player ones', function () {
    $good = hash('sha256', 'one');

    $config = kadiConfigWithEnv(['PUSH_API_BROADCAST_KEY_HASHES' => "junk, {$good} ,".substr($good, 0, 60)]);

    expect($config['push_api']['broadcast_key_hashes'])->toBe([$good]);
});

// --- Dedicated queue -------------------------------------------------------

test('by default broadcast jobs use the default queue', function () {
    Queue::fake();
    bcDevices(1);

    bcSend(bcKey())->assertStatus(202);
    // No queue name means the connection's default queue, which the existing worker already serves.
    Queue::assertPushed(SendPushBroadcast::class, fn ($job) => $job->queue === null);

    (new SendPushBroadcast(PushBroadcast::first()->id))->handle();
    Queue::assertPushed(SendPushBroadcastChunk::class, fn ($job) => $job->queue === null);
});

test('with a dedicated queue configured, the dispatcher and every chunk go to it, not the default queue', function () {
    Queue::fake();
    config(['kadi.push_api.broadcast_queue' => 'broadcasts', 'kadi.push_api.broadcast_chunk_size' => 1]);
    bcDevices(2);

    bcSend(bcKey())->assertStatus(202);
    Queue::assertPushedOn('broadcasts', SendPushBroadcast::class);

    (new SendPushBroadcast(PushBroadcast::first()->id))->handle();
    Queue::assertPushedOn('broadcasts', SendPushBroadcastChunk::class);
    Queue::assertPushed(SendPushBroadcastChunk::class, 2);
    Queue::assertNotPushed(SendPushBroadcastChunk::class, fn ($job, $queue) => $queue !== 'broadcasts');
});

test('the dedicated queue name is validated so a typo cannot become a dead queue name', function () {
    $parse = fn (string $value) => kadiConfigWithEnv(['PUSH_API_BROADCAST_QUEUE' => $value])['push_api']['broadcast_queue'];

    expect($parse(''))->toBeNull()
        ->and($parse('broadcasts'))->toBe('broadcasts')
        ->and($parse('push-broadcasts_2'))->toBe('push-broadcasts_2')
        ->and($parse('bad name'))->toBeNull()
        ->and($parse('a,b'))->toBeNull();
});
