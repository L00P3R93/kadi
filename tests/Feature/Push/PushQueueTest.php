<?php

use App\Models\User;
use App\Notifications\PushMessageNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use NotificationChannels\WebPush\WebPushChannel;

/**
 * These tests use the real `database` queue and a real `queue:work --once`, so they prove what
 * `Queue::fake()` cannot: the notification survives serialization and reaches the channel with
 * every option intact, and retries behave as configured.
 */
function queuedUserWithDevice(): User
{
    config(['queue.default' => 'database']);

    $user = User::factory()->create();
    $user->pushSubscriptions()->create([
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/'.bin2hex(random_bytes(12)),
        'public_key' => 'BKey',
        'auth_token' => 'authtoken',
    ]);

    return $user;
}

function workOneJob(): void
{
    Artisan::call('queue:work', ['--once' => true, '--sleep' => 0]);
}

test('a queued push survives the queue and reaches the channel with every option intact', function () {
    $user = queuedUserWithDevice();
    $received = null;

    $this->mock(WebPushChannel::class, function ($mock) use (&$received) {
        $mock->shouldReceive('send')->once()->andReturnUsing(function ($notifiable, $notification) use (&$received) {
            $received = [
                'notifiable' => [get_class($notifiable), $notifiable->getKey()],
                'title' => $notification->title,
                'body' => $notification->body,
                'url' => $notification->url,
                'tag' => $notification->tag,
                'ttl' => $notification->ttl,
                'urgency' => $notification->urgency,
            ];

            return [];
        });
    });

    $user->notify(new PushMessageNotification('Deposit received', 'Your vault was topped up.', '/wallet', 'deposit', 900, 'high'));

    // Queued, not sent inline.
    expect(DB::table('jobs')->count())->toBe(1)->and($received)->toBeNull();

    workOneJob();

    expect(DB::table('jobs')->count())->toBe(0)
        ->and(DB::table('failed_jobs')->count())->toBe(0)
        ->and($received)->toBe([
            'notifiable' => [User::class, $user->getKey()],
            'title' => 'Deposit received',
            'body' => 'Your vault was topped up.',
            'url' => '/wallet',
            'tag' => 'deposit',
            'ttl' => 900,
            'urgency' => 'high',
        ]);
});

test('a user with no device costs no push, even though the job was queued', function () {
    config(['queue.default' => 'database']);
    $user = User::factory()->create();

    $this->mock(WebPushChannel::class)->shouldNotReceive('send');

    $user->notify(new PushMessageNotification('Hello', 'World'));
    workOneJob();

    expect(DB::table('jobs')->count())->toBe(0)->and(DB::table('failed_jobs')->count())->toBe(0);
});

test('a failing send is retried after the backoff and only fails for good after three attempts', function () {
    $user = queuedUserWithDevice();

    $this->mock(WebPushChannel::class, fn ($mock) => $mock->shouldReceive('send')->times(3)->andThrow(new RuntimeException('push service down')));

    $user->notify(new PushMessageNotification('Hello', 'World'));

    // Attempt 1 fails: back on the queue, delayed by the first backoff step (10s).
    workOneJob();

    $job = DB::table('jobs')->first();
    expect($job)->not->toBeNull()
        ->and((int) $job->attempts)->toBe(1)
        ->and((int) $job->available_at)->toBeGreaterThanOrEqual(now()->addSeconds(9)->timestamp)
        ->and(DB::table('failed_jobs')->count())->toBe(0);

    // Skip the wait. Attempt 2 fails too, and the delay steps up to the second backoff value (60s).
    DB::table('jobs')->update(['available_at' => now()->timestamp]);
    workOneJob();

    $job = DB::table('jobs')->first();
    expect((int) $job->attempts)->toBe(2)
        ->and((int) $job->available_at)->toBeGreaterThanOrEqual(now()->addSeconds(59)->timestamp)
        ->and(DB::table('failed_jobs')->count())->toBe(0);

    // Attempt 3 is the last: it fails for good instead of retrying forever.
    DB::table('jobs')->update(['available_at' => now()->timestamp]);
    workOneJob();

    expect(DB::table('jobs')->count())->toBe(0)->and(DB::table('failed_jobs')->count())->toBe(1);
});

test('queue limits nest: one web push request < the push job timeout < retry_after, on every connection', function () {
    $push = new PushMessageNotification('t', 'b');

    // The web push client waits up to 30 seconds per request (fixed inside the package).
    expect($push->timeout)->toBeGreaterThan(30);

    // If retry_after were not longer than the job timeout, the queue could hand the same push to a
    // second worker while the first is still sending it. Production runs its worker on redis, the
    // local default is database, so both connections must satisfy this.
    foreach (['database', 'redis'] as $connection) {
        expect((int) config("queue.connections.{$connection}.retry_after"))
            ->toBeGreaterThan($push->timeout, "{$connection} retry_after must exceed the push job timeout");
    }
});
