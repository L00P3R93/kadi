<?php

use App\Facades\KadiApi;
use App\Jobs\ProcessVerifiedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Point the kadi connection at a throwaway in-memory database so the
    // job's cross-database insert can be observed without MySQL.
    config(['database.connections.kadi' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]]);

    Schema::connection('kadi')->create('accounts', function ($t) {
        $t->integer('id')->primary();
        $t->string('name');
        $t->string('phone')->nullable();
        $t->string('email');
        $t->string('password')->nullable();
        $t->string('outh')->nullable();
        $t->string('google_id')->nullable();
    });

    Mail::fake();
    Http::fake();

    KadiApi::shouldReceive('createCustomer')->andReturn(['customer_id' => 4242]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => ['id' => 4242, 'balance' => 0]]);
});

test('job inserts the bcrypt hash — never plaintext — into kadi accounts', function () {
    $user = User::factory()->create(['linked_id' => null, 'phone' => '254712345678']);

    Cache::put("user.kadi_password_hash.{$user->id}", Hash::make('registration-secret'), now()->addMinutes(30));

    (new ProcessVerifiedUser($user))->handle();

    $row = DB::connection('kadi')->table('accounts')->where('id', 4242)->first();

    expect($row)->not->toBeNull();
    expect($row->password)->toStartWith('$2y$');
    expect($row->password)->not->toBe('registration-secret');
    expect(Hash::check('registration-secret', $row->password))->toBeTrue();
});

test('job clears the hash cache after a successful insert', function () {
    $user = User::factory()->create(['linked_id' => null, 'phone' => '254712345678']);

    Cache::put("user.kadi_password_hash.{$user->id}", Hash::make('registration-secret'), now()->addMinutes(30));

    (new ProcessVerifiedUser($user))->handle();

    expect(Cache::has("user.kadi_password_hash.{$user->id}"))->toBeFalse();
});

test('google-only users insert with a null password', function () {
    $user = User::factory()->create(['linked_id' => null, 'phone' => null]);

    (new ProcessVerifiedUser($user))->handle();

    $row = DB::connection('kadi')->table('accounts')->where('id', 4242)->first();

    expect($row->password)->toBeNull();
});
