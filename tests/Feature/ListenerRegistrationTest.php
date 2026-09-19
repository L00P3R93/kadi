<?php

use App\Listeners\HandleLogin;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Queue;

/**
 * Laravel auto-discovers listeners in app/Listeners (any class whose handle() is type-hinted
 * with an event). Registering one explicitly as well, with Event::listen(), makes it run twice
 * for every event. These tests keep that from creeping back in.
 */
test('every app listener is registered exactly once per event', function () {
    $duplicates = [];

    foreach (app('events')->getRawListeners() as $event => $listeners) {
        $counts = collect($listeners)
            ->filter(fn ($listener) => is_string($listener))
            ->map(fn (string $listener) => preg_replace('/@handle$/', '', $listener))
            ->filter(fn (string $class) => str_starts_with($class, 'App\\Listeners\\'))
            ->countBy();

        foreach ($counts->filter(fn (int $n) => $n > 1) as $class => $n) {
            $duplicates[] = "{$class} is registered {$n} times for {$event}";
        }
    }

    expect($duplicates)->toBe([]);
});

test('logging in queues the profile refresh exactly once', function () {
    Queue::fake();

    event(new Login('web', User::factory()->create(), false));

    $queued = Queue::pushed(CallQueuedListener::class)
        ->filter(fn (CallQueuedListener $job) => $job->class === HandleLogin::class);

    expect($queued)->toHaveCount(1);
});
