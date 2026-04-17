<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KadiGameController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $profile = Cache::get("kadi.customer.{$user->id}", []);
        $googleId = $profile['google_id'] ?? DB::connection('kadi')
            ->table('accounts')
            ->where('email', $user->email)
            ->value('google_id') ?? '';

        return view('kadi', ['googleId' => $googleId]);
    }
}
