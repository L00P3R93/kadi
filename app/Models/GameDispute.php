<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A complaint a player filed with KadiApi about a lost game or tournament/jackpot round. KadiApi owns
 * the complaint; this row records that it was filed (see App\Services\GameDisputeService).
 *
 * @property string $subject_key game:{game_wallet_id} | tournament-round:{transaction_id} | jackpot-round:{transaction_id}
 * @property ?int $competition_wallet_id the opponent's wallet the complaint names (rounds only)
 * @property string $status pending_dispute | resolved | rejected | cancelled
 */
class GameDispute extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'game_wallet_id' => 'integer',
            'competition_wallet_id' => 'integer',
            'transaction_id' => 'integer',
            'opponent_transaction_id' => 'integer',
            'kadi_complaint_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
