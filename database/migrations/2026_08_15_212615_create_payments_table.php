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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();

            $table->string('payment_method', 40); // PaymentMethod Enum
            $table->string('provider', 40)->nullable(); // e.g. M-Pesa, Stripe, Airtel, Bank etc
            $table->string('reference', 64)->unique()->nullable(); // e.g. Transaction ID
            $table->string('merchant_request_id', 64)->nullable();
            $table->string('checkout_request_id', 64)->unique()->nullable();

            $table->decimal('amount', 12);
            $table->string('currency', 3)->default('KES');
            $table->string('status')->index()->default('pending'); // pending|processing|successful|failed|cancelled|refunded|partially_refunded
            $table->text('failure_reason')->nullable();

            $table->dateTime('initiated_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('failed_at')->nullable();

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
        Schema::dropIfExists('payments');
    }
};
