<?php

namespace App\Models;

use Database\Factories\AdFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table('ads')]
#[Fillable([
    'ad_campaign_id', 'title', 'description', 'reward_message', 'reward_amount', 'reward_type',
    'video_source', 'video_url', 'video_storage_path',
    'thumbnail_source', 'thumbnail_url', 'thumbnail_storage_path',
    'cta_text', 'cta_subtitle', 'click_url',
    'duration_seconds', 'orientation', 'skip_allowed', 'reward_requires_completion',
    'cost_per_view', 'cost_per_click', 'is_active',
])]
class Ad extends Model implements HasMedia
{
    /** @use HasFactory<AdFactory> */
    use HasFactory, InteractsWithMedia, LogsActivity, SoftDeletes;

    /**
     * video_url / thumbnail_url stay the source of truth for what actually
     * gets served (works the same whether video_source is 'upload' or
     * 'external') — these two collections just give uploaded files a home
     * with Media Library's usual conveniences (disk config, cleanup on
     * model delete, conversions if you add them later).
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('video')->singleFile();
        $this->addMediaCollection('thumbnail')->singleFile();
    }

    public function adCampaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class);
    }

    public function adViews(): HasMany
    {
        return $this->hasMany(AdView::class);
    }

    public function adClicks(): HasMany
    {
        return $this->hasMany(AdClick::class);
    }

    public function adAnalyticEvents(): HasMany
    {
        return $this->hasMany(AdAnalyticEvent::class);
    }
}
