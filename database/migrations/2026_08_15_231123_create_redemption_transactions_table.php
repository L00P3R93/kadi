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
        Schema::create('redemption_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->index()->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->string('source', 40)->default('kadi_api');

            $table->string('direction', 6); // debit or credit
            $table->bigInteger('coin_amount')->default(0);

            // Mirrors of the KadiApi customer payload immediately before/after the
            // call, purely for audit/reconciliation — NOT used for balance checks
            // at request time (always re-fetch live balance from KadiApi first).
            $table->bigInteger('balance_before')->nullable();
            $table->bigInteger('balance_after')->nullable();

            $table->string('kadi_reference', 80)->nullable(); // id/reference returned by KadiApi
            $table->string('idempotency_key', 80)->unique();
            $table->string('status')->index()->default('pending'); // pending|completed|failed|reserved

            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redemption_transactions');
    }
};
