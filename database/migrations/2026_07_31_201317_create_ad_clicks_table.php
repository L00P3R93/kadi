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
        Schema::create('ad_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_view_id')->comment('the view this click followed, if any')->constrained('ad_views')->cascadeOnDelete();
            $table->foreignId('ad_id')->index()->constrained('ads')->cascadeOnDelete();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('charge_amount', 6)->default(0.00)->comment('snapshot of what was charged from the campaign budget');
            $table->foreignId('ad_wallet_transaction_id')->nullable()->constrained('ad_wallet_transactions')->cascadeOnDelete();

            $table->string('device_platform', 30)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->string('country', 2)->nullable(); // country code

            $table->timestamp('clicked_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ad_campaign_id', 'user_id'], 'idx_campaign_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_clicks');
    }
};
