<?php

namespace App\Notifications;

use App\Models\PushBroadcast;

/**
 * The message of a broadcast. It reuses PushMessageNotification's payload and safety rules
 * (visible notification, same-origin tap target, urgency allow-list) and is sent by
 * App\Jobs\SendPushBroadcastChunk, never queued per device.
 */
class PushBroadcastNotification extends PushMessageNotification
{
    public static function for(PushBroadcast $broadcast): self
    {
        return new self(
            title: $broadcast->title,
            body: $broadcast->body,
            url: $broadcast->url,
            // A per-broadcast tag by default: a retried chunk replaces its own notification instead of stacking a duplicate.
            tag: $broadcast->tag ?: 'broadcast-'.substr($broadcast->id, 0, 8),
            ttl: $broadcast->ttl,
            urgency: $broadcast->urgency,
        );
    }

    /**
     * The audience is a batch of devices, not a user: there is nothing to check per notifiable.
     */
    public function shouldSend(object $notifiable, string $channel): bool
    {
        return true;
    }
}
