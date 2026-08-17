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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->index()->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->index()->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

            // Immutable historical snapshot - never reconstructed form current product row
            $table->string('sku', 64);
            $table->string('product_name', 180);
            $table->string('product_type'); // ProductType Enum

            $table->integer('quantity')->default(1);
            $table->decimal('unit_money_price', 12)->default(0.00);
            $table->decimal('discount_money', 12)->default(0.00);
            $table->decimal('subtotal_money', 12)->default(0.00);
            $table->bigInteger('unit_coin_price')->default(0);
            $table->bigInteger('discount_coins')->default(0);
            $table->bigInteger('subtotal_coins')->default(0);

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
        Schema::dropIfExists('order_items');
    }
};
