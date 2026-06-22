<?php

namespace App\Facades;

use App\Services\BrevoEmailService;
use Illuminate\Support\Facades\Facade;

class BrevoMail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return BrevoEmailService::class;
    }
}
