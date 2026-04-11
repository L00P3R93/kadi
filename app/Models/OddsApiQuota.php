<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OddsApiQuota extends Model
{
    protected $table = 'odds_api_quota';

    public $timestamps = false;

    protected $fillable = ['remaining', 'used', 'updated_at'];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (self $model) {
            $model->updated_at = now();
        });
    }
}
