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
        Schema::create('ad_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // sports, gaming, political
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('pricing_multiplier', 4)->default(1.00);
            $table->boolean('requires_approval')->default(false)->comment('true for Alcohol, Political');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_categories');
    }
};
