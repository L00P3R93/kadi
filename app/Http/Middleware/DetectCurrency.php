<?php

namespace App\Http\Middleware;

use App\Services\CurrencyService;
use Closure;
use Illuminate\Http\Request;

class DetectCurrency
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! session()->has('currency')) {
            session(['currency' => CurrencyService::detectCurrency()]);
        }

        return $next($request);
    }
}
