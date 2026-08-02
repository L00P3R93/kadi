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
        Schema::create('ad_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_id')->index()->constrained('ads')->cascadeOnDelete();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->index()->constrained('users')->cascadeOnDelete();

            $table->string('status')->default('requested');
            $table->decimal('watched_percentage', 5)->default(0.00);

            $table->boolean('reward_granted')->default(false);
            $table->integer('reward_amount')->default(0)->comment('snapshot of what was granted');
            $table->boolean('reward_credited_to_game_wallet')->default(false)->comment('true once KadiApi credit call succeeds');

            $table->decimal('charge_amount', 6)->default(0.00)->comment('snapshot of what was charged from the campaign budget');
            $table->foreignId('ad_wallet_transaction_id')->nullable()->constrained('ad_wallet_transactions')->cascadeOnDelete();

            $table->string('device_platform', 30)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->string('country', 2)->nullable(); // country code

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ad_campaign_id', 'user_id', 'status', 'completed_at'], 'idx_freq_cap');

            $table->comment('
                One row per watch attempt. Source of truth for frequency-cap checks and billing.
            ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_views');
    }
};
