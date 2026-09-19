<?php

namespace App\Jobs;

use App\Models\PushBroadcast;
use App\Notifications\PushBroadcastNotification;
use App\Push\BroadcastAudience;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\MessageSentReport;
use NotificationChannels\WebPush\PushSubscription;
use NotificationChannels\WebPush\WebPushChannel;
use Throwable;

/**
 * Sends one broadcast to the devices whose subscription id lies in [fromId, toId].
 *
 * Limits stay nested like every push job: this job's timeout (60s) < worker --timeout (90s) <
 * retry_after (120s). A retry re-sends the whole chunk; devices replace the earlier copy because
 * the notification tag is the same, so nobody sees a stacked duplicate.
 */
class SendPushBroadcastChunk implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60];

    public int $timeout = 60;

    public function __construct(public string $broadcastId, public int $fromId, public int $toId) {}

    public function handle(WebPushChannel $channel): void
    {
        $broadcast = PushBroadcast::find($this->broadcastId);

        if ($broadcast === null) {
            return;
        }

        if ($broadcast->isCancelled()) {
            $this->finishChunk($broadcast, 0, 0, 0);

            return;
        }

        $subscriptions = PushSubscription::query()->whereBetween('id', [$this->fromId, $this->toId])->get();
        $sent = $failed = $expired = 0;

        if ($subscriptions->isNotEmpty()) {
            $reports = $channel->send(new BroadcastAudience($subscriptions), PushBroadcastNotification::for($broadcast));

            /** @var MessageSentReport $report */
            foreach ($reports as $report) {
                if ($report->isSuccess()) {
                    $sent++;
                } elseif ($report->isSubscriptionExpired()) {
                    $expired++; // the channel already deleted the row
                } else {
                    $failed++;
                }
            }

            // A device with no report at all was not attempted: count it, do not hide it.
            $failed += max(0, $subscriptions->count() - $sent - $failed - $expired);
        }

        $this->finishChunk($broadcast, $sent, $failed, $expired);
    }

    /**
     * Runs once every attempt has failed (for example the push service was unreachable throughout).
     */
    public function failed(Throwable $exception): void
    {
        $broadcast = PushBroadcast::find($this->broadcastId);

        if ($broadcast === null) {
            return;
        }

        // Only the class name: exception messages can echo endpoints.
        Log::warning('Push broadcast chunk failed', ['broadcast' => $broadcast->id, 'error' => $exception::class]);

        $devices = PushSubscription::query()->whereBetween('id', [$this->fromId, $this->toId])->count();
        $this->finishChunk($broadcast, 0, $devices, 0);
    }

    private function finishChunk(PushBroadcast $broadcast, int $sent, int $failed, int $expired): void
    {
        // Atomic increments: chunks run in parallel on several workers.
        PushBroadcast::whereKey($broadcast->id)->update([
            'sent' => DB::raw('sent + '.$sent),
            'failed' => DB::raw('failed + '.$failed),
            'expired_removed' => DB::raw('expired_removed + '.$expired),
            'chunks_done' => DB::raw('chunks_done + 1'),
        ]);

        $fresh = PushBroadcast::find($broadcast->id);

        if ($fresh->chunks_done >= $fresh->chunks_total && ! $fresh->isFinished()) {
            // Guarded update so exactly one chunk finalises it, and a cancelled broadcast stays cancelled.
            $won = PushBroadcast::whereKey($fresh->id)->where('status', PushBroadcast::SENDING)
                ->update(['status' => PushBroadcast::DONE, 'finished_at' => now()]);

            if ($won) {
                Log::info('Push broadcast finished', [
                    'broadcast' => $fresh->id, 'devices' => $fresh->devices_total,
                    'sent' => $fresh->sent, 'failed' => $fresh->failed, 'expired_removed' => $fresh->expired_removed,
                ]);
            }
        }
    }
}
