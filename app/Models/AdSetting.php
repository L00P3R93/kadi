<?php

namespace App\Models;

use Database\Factories\AdSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

#[Fillable(['key', 'value'])]
class AdSetting extends Model
{
    /** @use HasFactory<AdSettingFactory> */
    use HasFactory, LogsActivity, SoftDeletes;
}
