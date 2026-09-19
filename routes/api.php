<?php

use App\Http\Controllers\Api\PushApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Server-to-server API
|--------------------------------------------------------------------------
|
| Registered under the `api` middleware group (prefix /api): no session, no cookies, no CSRF.
| Authentication is a bearer key checked by the `push.api` middleware, which fails closed when
| no key is configured. See docs/push-api.md.
|
*/

Route::prefix('v1')->middleware(['push.api', 'throttle:push-api'])->group(function () {
    Route::post('push-notifications', PushApiController::class)->name('api.v1.push-notifications.store');
});
