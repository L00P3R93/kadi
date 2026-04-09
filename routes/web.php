<?php

use App\Livewire\Dashboard;
use App\Livewire\Games;
use App\Livewire\Guest\GamesList;
use App\Livewire\Profile\Show;
use App\Livewire\Wallet\Index;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('home');
Route::get('/lobby', GamesList::class)->name('guest.games');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/play', Games::class)->name('games');
    Route::get('/profile', Show::class)->name('profile');
    Route::get('/wallet', Index::class)->name('wallet');
});

require __DIR__.'/settings.php';
