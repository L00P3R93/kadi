<?php

use App\Http\Controllers\KadiGameController;
use App\Livewire\Dashboard;
use App\Livewire\Games;
use App\Livewire\Guest\GamesList;
use App\Livewire\Profile\Show;
use App\Livewire\Sportsbook\GuestSportsbookPage;
use App\Livewire\Sportsbook\SportsbookPage;
use App\Livewire\Wallet\Index;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

// Serve brotli-compressed Godot game assets (Nginx doesn't process .htaccess)
Route::get('/kadig/index.js', function () {
    $path = public_path('kadig/index.js.br');
    abort_unless(file_exists($path), 404);
    return response()->stream(fn () => readfile($path), 200, [
        'Content-Type' => 'application/javascript',
        'Content-Encoding' => 'br',
        'Content-Length' => filesize($path),
        'Cache-Control' => 'no-cache, no-transform',
        'Vary' => 'Accept-Encoding',
    ]);
});
Route::get('/kadig/index.wasm', function () {
    $path = public_path('kadig/index.wasm.br');
    abort_unless(file_exists($path), 404);
    return response()->stream(fn () => readfile($path), 200, [
        'Content-Type' => 'application/wasm',
        'Content-Encoding' => 'br',
        'Content-Length' => filesize($path),
        'Cache-Control' => 'no-cache, no-transform',
        'Vary' => 'Accept-Encoding',
    ]);
});
Route::get('/kadig/index.pck', function () {
    $path = public_path('kadig/index.pck.br');
    abort_unless(file_exists($path), 404);
    return response()->stream(fn () => readfile($path), 200, [
        'Content-Type' => 'application/octet-stream',
        'Content-Encoding' => 'br',
        'Content-Length' => filesize($path),
        'Cache-Control' => 'no-cache, no-transform',
        'Vary' => 'Accept-Encoding',
    ]);
});
Route::get('/kadig/version.php', fn () => response('1.0.0', 200, ['Content-Type' => 'text/plain']));

Route::get('/', Welcome::class)->name('home');
Route::get('/lobby', GamesList::class)->name('guest.games');
Route::get('/sportsbook', GuestSportsbookPage::class)->name('sportsbook');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/dashboard/sportsbook', SportsbookPage::class)->name('dashboard.sportsbook');
    Route::get('/play', Games::class)->name('games');
    Route::get('/profile', Show::class)->name('profile');
    Route::get('/wallet', Index::class)->name('wallet');
    Route::get('/admin/sportsbook', fn () => view('admin.sportsbook'))->name('admin.sportsbook');
    Route::get('/kadi', KadiGameController::class)->name('kadi');
});

require __DIR__.'/settings.php';

Route::get('/sitemap.xml', function () {
    $path = public_path('sitemap.xml');
    abort_unless(file_exists($path), 404);
    return response()->file($path, ['Content-Type' => 'application/xml']);
});
