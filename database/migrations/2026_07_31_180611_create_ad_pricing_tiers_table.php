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
        Schema::create('ad_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('duration_seconds')->unique()->comment('10, 20 or 30');
            $table->decimal('base_cost', 6)->comment('KES 0.50 / 1.00 / 2.00');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_pricing_tiers');
    }
};
