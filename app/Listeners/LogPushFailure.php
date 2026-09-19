<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\Events\NotificationFailed;

/**
 * Records failed web push deliveries. Expired subscriptions (404/410) are already
 * deleted by the package, so those are informational.
 *
 * Deliberately logs only ids, the push service host and the status code: the
 * endpoint URL works like a capability token, and reasons can echo it back.
 */
class LogPushFailure
{
    public function handle(NotificationFailed $event): void
    {
        $expired = $event->report->isSubscriptionExpired();

        Log::log($expired ? 'info' : 'warning', $expired ? 'Web push subscription expired and was removed' : 'Web push delivery failed', [
            'subscription_id' => $event->subscription->getKey(),
            'subscribable_id' => $event->subscription->subscribable_id,
            'push_service' => parse_url($event->report->getEndpoint(), PHP_URL_HOST) ?: null,
            'status' => $event->report->getResponse()?->getStatusCode(),
        ]);
    }
}
