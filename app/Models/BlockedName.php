<?php

namespace App\Models;

use App\Support\NameGuard;
use Illuminate\Database\Eloquent\Model;

/**
 * A name (or part of one) that players may not use. Terms are stored normalised, and the cached
 * list is dropped whenever the table changes, so an edit takes effect immediately.
 *
 * @property string $term
 * @property string $match word | contains
 */
class BlockedName extends Model
{
    public const WORD = 'word';

    public const CONTAINS = 'contains';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(fn (self $blocked) => $blocked->term = NameGuard::normalise($blocked->term));
        static::saved(fn () => NameGuard::flush());
        static::deleted(fn () => NameGuard::flush());
    }
}
