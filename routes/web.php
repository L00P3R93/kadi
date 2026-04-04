<?php

use App\Livewire\Dashboard;
use App\Livewire\Profile\Show;
use App\Livewire\Wallet\Index;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile', Show::class)->name('profile');
    Route::get('/wallet', Index::class)->name('wallet');
});

require __DIR__.'/settings.php';
