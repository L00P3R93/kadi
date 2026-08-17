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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('type', 40)->default('percentage_discount'); // percentage_discount|fixed_discount|coin_discount|special_coin_price|buy_x_get_y|free_shipping
            $table->string('status', 20)->default('active'); // draft|scheduled|active|paused|expired|archived
            $table->integer('priority')->default(0);
            $table->integer('usage_limit')->default(0);
            $table->integer('per_user_limit')->default(0);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->json('rules'); // engine-evaluated rule payload, never hard-coded in controller
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index(['status', 'starts_at', 'ends_at']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
