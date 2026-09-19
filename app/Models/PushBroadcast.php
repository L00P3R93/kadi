<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * One system-wide announcement sent to every registered device. Holds the (non-personal) text and
 * the delivery counters, so a broadcast can be tracked, audited and cancelled.
 *
 * @property string $id
 * @property string $status queued | sending | done | cancelled
 */
class PushBroadcast extends Model
{
    use HasUuids;

    public const QUEUED = 'queued';

    public const SENDING = 'sending';

    public const DONE = 'done';

    public const CANCELLED = 'cancelled';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'finished_at' => 'datetime'];
    }

    public function isCancelled(): bool
    {
        return $this->status === self::CANCELLED;
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::DONE, self::CANCELLED], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function toStatusArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'devices_total' => $this->devices_total,
            'sent' => $this->sent,
            'failed' => $this->failed,
            'expired_removed' => $this->expired_removed,
            'created_at' => $this->created_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
        ];
    }
}
