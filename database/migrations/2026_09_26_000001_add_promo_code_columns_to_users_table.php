<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // POST customers runs after the e-mail is verified, so the code must live on the row.
            $table->string('signup_promo_code', 30)->nullable()->after('signup_referral_code');
            // KadiApi's promo_code_applied from POST customers; null until a code was sent.
            $table->boolean('promo_code_applied')->nullable()->after('signup_promo_code');
            $table->timestamp('promo_notice_dismissed_at')->nullable()->after('promo_code_applied');
            // The game server's promo jackpot wallet (CreatePromoJackpotWallet): pending | sent | failed | unknown.
            // Claimed before the call so it is only ever requested once.
            $table->string('promo_jackpot_wallet_status', 16)->nullable()->after('promo_notice_dismissed_at');
            $table->timestamp('promo_jackpot_wallet_at')->nullable()->after('promo_jackpot_wallet_status');
            // POST customers/{id}/verified is now reported for every player, not only referred ones.
            $table->renameColumn('referral_verified_reported_at', 'verified_reported_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('verified_reported_at', 'referral_verified_reported_at');
            $table->dropColumn([
                'signup_promo_code', 'promo_code_applied', 'promo_notice_dismissed_at',
                'promo_jackpot_wallet_status', 'promo_jackpot_wallet_at',
            ]);
        });
    }
};
