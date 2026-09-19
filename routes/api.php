<?php

use App\Http\Controllers\Api\PushApiController;
use App\Http\Controllers\Api\PushBroadcastController;
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

// System-wide announcements to every registered device. A separate key list (`push.api:broadcast`),
// so the per-player key cannot notify everyone.
Route::prefix('v1/push-broadcasts')->middleware(['push.api:broadcast', 'throttle:push-api'])->name('api.v1.push-broadcasts.')->group(function () {
    Route::post('/', [PushBroadcastController::class, 'store'])->name('store');
    Route::get('{broadcast}', [PushBroadcastController::class, 'show'])->name('show');
    Route::delete('{broadcast}', [PushBroadcastController::class, 'destroy'])->name('destroy');
});
