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
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->index()->constrained('ad_campaigns')->cascadeOnDelete();

            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('reward_message', 255);
            $table->integer('reward_amount');
            $table->string('reward_type', 30)->default('coins');

            $table->string('video_source')->nullable(); // upload or external
            $table->string('video_url', 500)->nullable(); // external
            $table->string('video_storage_path', 500)->nullable(); // upload

            $table->string('thumbnail_source')->nullable(); // upload or external
            $table->string('thumbnail_url', 500)->nullable(); // external
            $table->string('thumbnail_storage_path', 500)->nullable(); // upload

            $table->string('cta_text', 50);
            $table->string('cta_subtitle', 100);
            $table->string('click_url', 500)->nullable();

            $table->tinyInteger('duration_seconds')->comment('must be 10, 20 or 30');
            $table->string('orientation'); // portrait or landscape
            $table->boolean('skip_allowed')->default(false);
            $table->boolean('reward_requires_completion')->default(true);

            $table->decimal('cost_per_view', 6)->default(0.00)->comment('snapshot: base_cost(duration) x category multiplier');
            $table->decimal('cost_per_click', 6)->default(0.00)->comment('snapshot: flat CTA cost at creation time');

            $table->boolean('is_active')->index()->default(true)->comment('on/off switch, independent of campaign status');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
