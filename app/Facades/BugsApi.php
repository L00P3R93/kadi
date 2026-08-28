<?php

namespace App\Facades;

use App\Services\BugsApiService;
use Illuminate\Support\Facades\Facade;

class BugsApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BugsApiService::class;
    }
}
