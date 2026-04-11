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
        Schema::create('odds_api_quota', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedInteger('remaining')->default(0);
            $table->unsignedInteger('used')->default(0);
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odds_api_quota');
    }
};
