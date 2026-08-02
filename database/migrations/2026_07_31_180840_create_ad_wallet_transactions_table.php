<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ad_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_wallet_id')->constrained('ad_wallets')->cascadeOnDelete();
            $table->string('type'); // top_up, campaign_reserve, campaign_release, view_charge, click_charge, refund, adjustment
            $table->decimal('amount', 12)->default(0.00);
            $table->decimal('balance_after', 12)->default(0.00);
            $table->foreignId('ad_wallet_top_up_id')->nullable()->constrained('ad_wallet_top_ups')->cascadeOnDelete();
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['ad_wallet_id', 'created_at'], 'idx_wallet_created');

            $table->comment('Full ledger: every balance-affecting event on wallet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_wallet_transactions');
    }
};
