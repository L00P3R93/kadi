<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Complaints players filed about a lost game or tournament/jackpot round (see
     * App\Services\GameDisputeService). KadiApi owns the complaint; this copy stops a player reporting
     * the same thing twice and marks it on their game history.
     */
    public function up(): void
    {
        Schema::create('game_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject_key', 60);                              // game:{game_wallet_id} | {kind}-round:{transaction_id}
            $table->string('kind', 20);                                     // game | tournament | jackpot
            $table->unsignedBigInteger('game_wallet_id')->nullable();       // single game
            $table->unsignedBigInteger('competition_wallet_id')->nullable(); // the OPPONENT's wallet (round)
            $table->unsignedBigInteger('transaction_id')->nullable();       // the player's lost round
            $table->unsignedBigInteger('opponent_transaction_id')->nullable();
            $table->string('game_id', 100)->nullable();
            $table->string('competition_id', 100)->nullable();
            $table->unsignedBigInteger('kadi_complaint_id')->nullable();
            $table->string('complaint_uuid', 64)->nullable();
            $table->string('status', 30)->default('pending_dispute');
            $table->string('reason', 255);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'subject_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_disputes');
    }
};
