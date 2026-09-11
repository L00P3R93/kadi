<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyDomain
{
    private const LEGACY_HOSTS = [
        'kadikings.co.ke',
        'www.kadikings.co.ke',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->getHost(), self::LEGACY_HOSTS, true)) {
            return redirect()->to(
                'https://kadi.online'.$request->getRequestUri(),
                301
            );
        }

        return $next($request);
    }
}
