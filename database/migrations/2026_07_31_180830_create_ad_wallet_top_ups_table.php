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
        Schema::create('ad_wallet_top_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_wallet_id')->constrained('ad_wallets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->string('phone_number', 20);
            $table->string('transaction_ref')->nullable();
            $table->string('completed_at')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed, canceled
            $table->softDeletes();
            $table->timestamps();

            $table->index(['ad_wallet_id', 'status'], 'idx_wallet_status');

            $table->comment('M-Pesa Stk Top up credits to the ad wallet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_wallet_top_ups');
    }
};
