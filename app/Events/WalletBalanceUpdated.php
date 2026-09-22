<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Pushed to the owning user's private channel the moment a wallet webhook applies a new balance,
 * so an open tab updates without waiting on the 30s wire:poll. Broadcast synchronously (not
 * queued): Reverb runs on the same box, so this is one fast local call — see
 * WalletWebhookController, which wraps the broadcast() call in a try/catch so a Reverb outage can
 * never turn the webhook's required 200 response into an error.
 */
class WalletBalanceUpdated implements ShouldBroadcastNow
{
    public function __construct(
        public readonly int $userId,
        public readonly float $balance,
    ) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("user.{$this->userId}")];
    }

    public function broadcastAs(): string
    {
        return 'wallet.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['balance' => $this->balance];
    }
}
