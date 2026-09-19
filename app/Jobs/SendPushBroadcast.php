<?php

namespace App\Jobs;

use App\Models\PushBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WebPush\PushSubscription;

/**
 * Splits a broadcast into small jobs of `broadcast_chunk_size` devices each. This job only reads
 * ids (never keys or endpoints); the chunk jobs do the sending.
 */
class SendPushBroadcast implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public string $broadcastId) {}

    public function handle(): void
    {
        $broadcast = PushBroadcast::find($this->broadcastId);

        // Cancelled while waiting in the queue, or a retry of a job that already ran.
        if ($broadcast === null || $broadcast->status !== PushBroadcast::QUEUED) {
            return;
        }

        $size = max(1, (int) config('kadi.push_api.broadcast_chunk_size', 200));
        $ranges = [];
        $devices = 0;

        PushSubscription::query()->select('id')->orderBy('id')->chunkById($size, function ($rows) use (&$ranges, &$devices) {
            $ranges[] = [$rows->first()->id, $rows->last()->id];
            $devices += $rows->count();
        });

        $broadcast->update([
            'status' => $ranges === [] ? PushBroadcast::DONE : PushBroadcast::SENDING,
            'devices_total' => $devices,
            'chunks_total' => count($ranges),
            'started_at' => now(),
            'finished_at' => $ranges === [] ? now() : null,
        ]);

        foreach ($ranges as [$from, $to]) {
            SendPushBroadcastChunk::dispatch($broadcast->id, $from, $to)->onQueue(config('kadi.push_api.broadcast_queue'));
        }

        Log::info('Push broadcast started', ['broadcast' => $broadcast->id, 'devices' => $devices, 'chunks' => count($ranges)]);
    }
}
