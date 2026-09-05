<?php

use App\Facades\KadiApi;
use App\Jobs\ProcessVerifiedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
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
});

test('job inserts the bcrypt hash — never plaintext — into kadi accounts', function () {
    KadiApi::shouldReceive('createCustomer')->andReturn(['customer_id' => 4242]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => ['id' => 4242, 'balance' => 0]]);

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
    KadiApi::shouldReceive('createCustomer')->andReturn(['customer_id' => 4242]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => ['id' => 4242, 'balance' => 0]]);

    $user = User::factory()->create(['linked_id' => null, 'phone' => '254712345678']);

    Cache::put("user.kadi_password_hash.{$user->id}", Hash::make('registration-secret'), now()->addMinutes(30));

    (new ProcessVerifiedUser($user))->handle();

    expect(Cache::has("user.kadi_password_hash.{$user->id}"))->toBeFalse();
});

test('google-only users insert with a null password', function () {
    KadiApi::shouldReceive('createCustomer')->andReturn(['customer_id' => 4242]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => ['id' => 4242, 'balance' => 0]]);

    $user = User::factory()->create(['linked_id' => null, 'phone' => null]);

    (new ProcessVerifiedUser($user))->handle();

    $row = DB::connection('kadi')->table('accounts')->where('id', 4242)->first();

    expect($row->password)->toBeNull();
});

test('job is idempotent — running twice does not create duplicate accounts', function () {
    KadiApi::shouldReceive('createCustomer')->andReturn(['customer_id' => 4242]);
    KadiApi::shouldReceive('getCustomer')->andReturn(['data' => ['id' => 4242, 'balance' => 0]]);

    $user = User::factory()->create(['linked_id' => null, 'phone' => '254712345678']);

    Cache::put("user.kadi_password_hash.{$user->id}", Hash::make('secret'), now()->addHours(24));

    (new ProcessVerifiedUser($user))->handle();
    (new ProcessVerifiedUser($user))->handle();

    $count = DB::connection('kadi')->table('accounts')->count();
    expect($count)->toBe(1);

    $row = DB::connection('kadi')->table('accounts')->where('id', 4242)->first();
    expect($row->email)->toBe($user->email);
});

test('job returns early if user is already linked', function () {
    $user = User::factory()->create(['linked_id' => 9999]);

    (new ProcessVerifiedUser($user))->handle();

    $count = DB::connection('kadi')->table('accounts')->count();
    expect($count)->toBe(0);
});

test('job aborts remaining steps if kadiapi throws an exception', function () {
    KadiApi::shouldReceive('createCustomer')->once()->andThrow(
        ConnectionException::class,
        'Connection timed out'
    );

    $user = User::factory()->create(['linked_id' => null, 'phone' => '254712345678']);

    (new ProcessVerifiedUser($user))->handle();

    expect($user->fresh()->linked_id)->toBeNull();

    $count = DB::connection('kadi')->table('accounts')->count();
    expect($count)->toBe(0);
});
