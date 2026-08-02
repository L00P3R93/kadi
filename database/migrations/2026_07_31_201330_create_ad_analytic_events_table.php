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
        Schema::create('ad_analytic_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ad_id')->constrained('ads')->cascadeOnDelete();
            $table->foreignId('ad_view_id')->constrained('ad_views')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();

            $table->string('event_type');
            $table->json('event_data')->nullable();

            $table->string('device_platform', 30)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->string('country', 2)->nullable(); // country code

            $table->timestamp('occurred_at');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['ad_id', 'event_type'], 'idx_ad_event');
            $table->index(['ad_campaign_id', 'occurred_at'], 'idx_campaign');

            $table->comment('
                Raw telemetry for every event in the spec (requested → closed/playback_error).
                Kept separate from ad_views/ad_clicks so billing tables stay lean.
            ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_analytic_events');
    }
};
