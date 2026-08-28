<?php

use App\Livewire\Wallet\Index;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

const COLD_API_BASE = 'https://api.kadi-kings.co.ke/api/v1/';

test('mount flags a cold cache for deferred live fetch and refreshCustomer resolves it', function () {
    Http::fake([
        COLD_API_BASE.'customers/*' => Http::response(['data' => ['balance' => 850.0, 'account_no' => 'KK-1']]),
        COLD_API_BASE.'customers/transactions/*' => Http::response(['transactions' => []]),
    ]);

    $user = User::factory()->create(['linked_id' => 9090]);

    Livewire::actingAs($user)->test(Index::class)
        ->assertSet('needsLoad', true)
        ->assertSet('balance', 0.0)
        ->call('refreshCustomer')
        ->assertSet('needsLoad', false)
        ->assertSet('balance', 850.0);

    expect(Cache::get("kadi.customer.{$user->id}")['balance'])->toEqual(850.0);
});

test('mount does not flag needsLoad when the customer cache is warm', function () {
    Http::fake([
        COLD_API_BASE.'customers/*' => Http::response(['data' => ['balance' => 1.0]]),
        COLD_API_BASE.'customers/transactions/*' => Http::response(['transactions' => []]),
    ]);

    $user = User::factory()->create(['linked_id' => 9091]);
    Cache::put("kadi.customer.{$user->id}", ['balance' => 64.0], now()->addHour());

    Livewire::actingAs($user)->test(Index::class)
        ->assertSet('needsLoad', false)
        ->assertSet('balance', 64.0);
});
