<?php

namespace App\Push;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\PushSubscription;

/**
 * A stand-in "notifiable" holding a batch of device subscriptions, so a broadcast goes through the
 * same WebPushChannel as every other push (VAPID signing, concurrent sending, deleting expired
 * devices, failure events and logging) without needing a user per device.
 */
class BroadcastAudience
{
    /**
     * @param  Collection<int, PushSubscription>  $subscriptions
     */
    public function __construct(private Collection $subscriptions) {}

    /**
     * @return Collection<int, PushSubscription>
     */
    public function routeNotificationFor(string $driver, ?Notification $notification = null): Collection
    {
        return $this->subscriptions;
    }
}
