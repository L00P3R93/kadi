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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->string('sku', 64);
            $table->string('name', 180);
            $table->string('slug', 200);
            $table->string('short_description', 320)->nullable();
            $table->text('description')->nullable();
            $table->json('specifications')->nullable(); // freeform key/value spec sheet from the source catalog
            $table->string('product_type', 30)->default('reward'); // physical_product, digital_product, voucher, gift_card, reward
            $table->string('status', 30)->default('active'); // draft, active, inactive, out_of_stock, archived

            // Pricing — plain KES to match existing BuyCoins/purchaseOptions convention
            // (that codebase stores 49, 1000, etc. — not minor units). Coin values are
            // whole-integer points, matching the source catalog (75000, 150000, ...).
            $table->decimal('money_price', 12)->default(0.00);
            $table->bigInteger('coin_price')->default(0);
            $table->decimal('original_money_price', 12)->default(0.00);
            $table->bigInteger('original_coin_price')->default(0);
            $table->string('currency', 3)->default('KES');

            // Inventory (physical_product only; digital/voucher/gift_card ignore these)
            $table->integer('stock_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);

            // Merchandising flags — kept for cheap boolean filters, NOT the sole
            // discovery mechanism (see merchandising_collections below)
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_promotional')->default(false);

            $table->decimal('estimated_value', 12)->nullable(); // catalog "estimated retail value" if different from money_price
            $table->boolean('requires_shipping')->default(false);
            $table->boolean('is_redeemable_with_coins')->default(true);
            $table->boolean('is_purchasable_with_money')->default(true);

            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sku');
            $table->index('slug');
            $table->index('product_category_id');
            $table->index('status');
            $table->index('product_type');
            $table->index(['is_trending', 'is_promotional', 'is_popular', 'is_new', 'is_featured'], 'product_flags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
