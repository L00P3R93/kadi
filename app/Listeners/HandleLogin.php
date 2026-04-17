<?php

namespace App\Listeners;

use App\Facades\KadiApi;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HandleLogin implements ShouldQueue
{
    public function handle(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        if (! $user->linked_id) {
            return;
        }

        try {
            $response = KadiApi::getCustomer($user->linked_id);
            $profile = $response['data'] ?? $response;

            $googleId = DB::connection('kadi')
                ->table('accounts')
                ->where('email', $user->email)
                ->value('google_id');

            if ($googleId !== null) {
                $profile['google_id'] = $googleId;
            }

            Cache::put("kadi.customer.{$user->id}", $profile, now()->addHour());
        } catch (RequestException|ConnectionException $e) {
            Log::error("HandleLogin: KadiApi fetch failed for user {$user->id}: ".$e->getMessage());
        } catch (\Throwable $e) {
            Log::error("HandleLogin: Failed to cache profile for user {$user->id}: ".$e->getMessage());
        }
    }
}
