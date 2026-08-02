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
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_profile_id')->index()->constrained('ad_profiles')->cascadeOnDelete();
            $table->foreignId('ad_category_id')->index()->constrained('ad_categories')->cascadeOnDelete();
            $table->string('name', 150);

            $table->string('status', 50)->index()->default('draft'); // draft, pending_review, active, paused, rejected, exhausted, completed

            $table->decimal('total_budget', 12)->default(0.00)->comment('what the advertiser funded this campaign with');
            $table->decimal('escrowed_budget', 12)->default(0.00)->comment('remaining spendable balance, starts == total_budget');
            $table->decimal('spent_budget', 12)->default(0.00)->comment('cumulative amount spent on this campaign, for reporting');

            $table->integer('priority')->default(0)->comment('lower numbers = higher priority, higher priority served first among eligible campaigns');
            $table->integer('frequency_cap')->default(0)->comment('max completed views / user / rolling 24h');

            $table->timestamp('starts_at');
            $table->timestamp('ends_at');

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['starts_at', 'ends_at'], 'idx_schedule');

            $table->comment('
                One category, one budget, many ads.
                cost_per_view / cost_per_click live on `ads` (snapshotted per-duration),
                not here, since ads within one campaign can use different durations.
            ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_campaigns');
    }
};
