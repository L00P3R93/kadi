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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_number', 40)->index()->unique(); // KKO-YYYYMMDD-XXXXXX, public identifier - never expose id

            $table->string('status', 40)->index()->default('pending'); // pending|awaiting_payment|paid|processing|ready_for_fulfillment|shipped|delivered|completed|cancelled|failed
            $table->string('payment_state', 40)->index()->default('unpaid'); // unpaid|awaiting_payment|paid|refunded|partially_paid|partially_refunded|failed
            $table->string('fulfillment_state', 40)->default('not_applicable'); // not_applicable|unfulfilled|processing|shipped|delivered|digital_delivered

            $table->string('payment_method', 20); // m-pesa|coins|mixed|airtel-money|card|other
            $table->string('currency', 3)->default('KES');

            $table->decimal('subtotal_money', 12)->default(0);
            $table->decimal('discount_money', 12)->default(0);
            $table->decimal('shipping_money', 12)->default(0);
            $table->decimal('tax_money', 12)->default(0);
            $table->decimal('grand_total_money', 12)->default(0);

            $table->bigInteger('subtotal_coins')->default(0);
            $table->bigInteger('discount_coins')->default(0);
            $table->bigInteger('grand_total_coins')->default(0);

            $table->boolean('requires_shipping')->default(true);

            $table->dateTime('payment_due_at')->nullable(); // drives the expire-pending-orders scheduled job
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'payment_due_at']);
        });

        Schema::table('promotion_usages', function (Blueprint $table) {
            $table->foreignId('order_id')->index()->nullable()->constrained('orders')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
