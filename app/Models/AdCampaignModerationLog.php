<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('ad_campaign_moderation_logs')]
#[Fillable('ad_campaign_id', 'action', 'performed_by', 'reason')]
class AdCampaignModerationLog extends Model
{
    use SoftDeletes;

    public function adCampaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
