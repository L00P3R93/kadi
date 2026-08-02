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
        Schema::table('ad_wallet_transactions', function (Blueprint $table) {
            $table->foreignId('ad_campaign_id')->nullable()->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignId('ad_view_id')->nullable()->constrained('ad_views')->cascadeOnDelete();
            $table->foreignId('ad_click_id')->nullable()->constrained('ad_clicks')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_wallet_transactions', function (Blueprint $table) {
            //
        });
    }
};
