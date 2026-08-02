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
        Schema::create('ad_campaign_moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ad_campaign_id')->constrained('ad_campaigns')->cascadeOnDelete();
            $table->string('action'); // submitted, approved, rejected, paused, resumed
            $table->foreignId('performed_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ad_campaign_id', 'created_at'], 'idx_campaign');

            $table->comment('Audit trail for Alcohol/Political approval workflow plus any manual pause/resume/reject.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_campaign_moderation_logs');
    }
};
