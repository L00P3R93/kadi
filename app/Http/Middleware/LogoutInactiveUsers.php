<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LogoutInactiveUsers
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity');

            if ($lastActivity && (time() - $lastActivity) > config('auth.idle_timeout', 30) * 60) {
                $user = Auth::user();

                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($user) {
                    DB::table('sessions')
                        ->where('user_id', $user->getAuthIdentifier())
                        ->delete();
                }

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session expired.'], 401);
                }

                return redirect()->route('login')
                    ->with('status', 'Your session has expired. Please log in again.');
            }

            session(['last_activity' => time()]);
        }

        return $next($request);
    }
}
