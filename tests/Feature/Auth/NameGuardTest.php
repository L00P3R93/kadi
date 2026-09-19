<?php

use App\Jobs\ProcessVerifiedUser;
use App\Livewire\Profile\Show;
use App\Models\BlockedName;
use App\Models\User;
use App\Services\KadiAccountSync;
use App\Support\NameGuard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Laravel\Fortify\Features;
use Livewire\Livewire;

/**
 * Two promises: a name the operator has blocked cannot be registered or chosen (Google sign-ups are
 * exempt because Google verified the name), and a name change reaches kadi.accounts.name.
 */
function guardRegistration(string $name, string $email = 'new@example.com')
{
    return test()->post(route('register.store'), [
        'name' => $name,
        'email' => $email,
        'phone' => '0700123456',
        'password' => 'password',
        'password_confirmation' => 'password',
        'age_confirmed' => '1',
        'terms' => '1',
    ]);
}

/** A throwaway stand-in for the game database, so cross-database writes can be observed. */
function fakeKadiAccounts(array $rows = []): void
{
    config(['database.connections.kadi' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge('kadi');

    Schema::connection('kadi')->create('accounts', function ($t) {
        $t->integer('id')->primary();
        $t->string('name');
        $t->string('email');
    });

    foreach ($rows as $row) {
        DB::connection('kadi')->table('accounts')->insert($row);
    }
}

// --- Matching --------------------------------------------------------------

test('names are normalised so simple evasions collapse to the same thing', function (string $input, string $expected) {
    expect(NameGuard::normalise($input))->toBe($expected);
})->with([
    'case' => ['ADMIN', 'admin'],
    'accents' => ['Ádmïn', 'admin'],
    'lookalikes' => ['4dm1n', 'admin'],
    'symbols' => ['@dm!n', 'admin'],
    'stretched' => ['adminnn', 'admin'],
    'separators become spaces' => ['kadi_support', 'kadi suport'],
    'no letters at all' => ['---', ''],
]);

test('without look-alikes digits and symbols are simply dropped', function () {
    expect(NameGuard::normalise('Admin2026', lookalikes: false))->toBe('admin')
        ->and(NameGuard::normalise('Admin!', lookalikes: false))->toBe('admin')
        ->and(NameGuard::normalise('Admin2026'))->toBe('admin o');
});

test('the starter list blocks impersonation, however it is dressed up', function (string $name) {
    expect(NameGuard::isBlocked($name))->toBeTrue("{$name} should be blocked");
})->with([
    'Admin', 'ADMIN', 'admin', 'Kadi Admin', 'The Admin', '4dm1n', 'a.d-m.i n', 'adminnn', 'k a d i', 'Kadi', 'kadionline',
    'Customer Care', 'customercare', 'Kadi_Support', 'Support Team', 'Moderator', 'System', 'Root',
    'Admin123', 'Admin!', 'Kadi2026', 'xX_Admin_Xx', 'Admin1', '1Admin', 'Support 24/7',
]);

test('ordinary names, including ones that only contain a blocked word inside another word, are fine', function (string $name) {
    expect(NameGuard::isBlocked($name))->toBeFalse("{$name} should be allowed");
})->with([
    'John Doe', 'Wanjiru Kamau', 'Sadmin', 'Administration Ola', 'Rooted Tree', 'Kadiri Musa', 'Ownership Sam', 'Systematic Jim', 'Otieno', 'Mary-Jane O\'Neil',
]);

test('a contains entry blocks the term anywhere, a word entry only as a whole word', function () {
    BlockedName::create(['term' => 'badword', 'match' => 'contains']);
    BlockedName::create(['term' => 'boss', 'match' => 'word']);

    expect(NameGuard::isBlocked('xxbadwordxx'))->toBeTrue()
        ->and(NameGuard::isBlocked('Bad Word'))->toBeTrue()      // spacing tricks do not help
        ->and(NameGuard::isBlocked('Big Boss'))->toBeTrue()
        ->and(NameGuard::isBlocked('Bosson'))->toBeFalse();
});

test('terms are stored normalised, and the list changes take effect immediately', function () {
    expect(NameGuard::isBlocked('Fancy Person'))->toBeFalse();

    $blocked = BlockedName::create(['term' => 'F4NCY  Person']);
    expect($blocked->term)->toBe('fancy person')
        ->and(NameGuard::isBlocked('Fancy Person'))->toBeTrue();   // cache was refreshed by the save

    $blocked->delete();
    expect(NameGuard::isBlocked('Fancy Person'))->toBeFalse();     // and by the delete
});

test('an empty list blocks nothing', function () {
    BlockedName::query()->delete();

    expect(NameGuard::isBlocked('Admin'))->toBeFalse();
});

// --- Registration ----------------------------------------------------------

test('registration refuses a blocked name, tells the player nothing about the list, and creates nobody', function () {
    test()->skipUnlessFortifyHas(Features::registration());

    guardRegistration('Kadi Admin')->assertSessionHasErrors('name');

    expect(session('errors')->first('name'))->toBe('That name is not available. Please use your own name.')
        ->and(session('errors')->first('name'))->not->toContain('admin');
    expect(User::where('email', 'new@example.com')->exists())->toBeFalse();
    test()->assertGuest();
});

test('registration still accepts a normal name', function () {
    test()->skipUnlessFortifyHas(Features::registration());

    guardRegistration('Wanjiru Kamau')->assertSessionHasNoErrors();

    test()->assertAuthenticated();
});

test('a name added to the list later is refused at registration straight away', function () {
    test()->skipUnlessFortifyHas(Features::registration());

    guardRegistration('Champion Player', 'a@example.com')->assertSessionHasNoErrors();
    auth()->logout();

    BlockedName::create(['term' => 'champion player']);

    guardRegistration('Champion Player', 'b@example.com')->assertSessionHasErrors('name');
});

// --- Google sign-up is exempt ----------------------------------------------

test('a google sign-up keeps the name google verified, even if it is on the list', function () {
    Queue::fake();

    test()->withSession(['google.pending' => [
        'id' => 'google-999', 'name' => 'Admin', 'email' => 'google-admin@example.com', 'avatar' => null,
        'expires_at' => now()->addMinutes(10)->getTimestamp(),
    ]])->post(route('auth.google.complete.store'), ['age_confirmed' => '1', 'terms' => '1'])->assertSessionHasNoErrors();

    $user = User::where('email', 'google-admin@example.com')->firstOrFail();
    expect($user->name)->toBe('Admin')->and($user->google_id)->toBe('google-999');
    Queue::assertPushed(ProcessVerifiedUser::class);
});

// --- Changing the name: the settings page ----------------------------------

test('the settings page refuses a blocked new name and keeps the old one', function () {
    $user = User::factory()->create(['name' => 'Real Person']);

    Livewire::actingAs($user)->test('pages::settings.profile')
        ->set('name', 'Support Team')->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasErrors('name');

    expect($user->fresh()->name)->toBe('Real Person');
});

test('saving without changing the name is never blocked, even if the list grew since they joined', function () {
    $user = User::factory()->create(['name' => 'Champion Player']);
    BlockedName::create(['term' => 'champion player']);

    Livewire::actingAs($user)->test('pages::settings.profile')
        ->set('email', 'changed-email@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->fresh()->email)->toBe('changed-email@example.com');
});

test('the settings page still allows a normal name change', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    Livewire::actingAs($user)->test('pages::settings.profile')
        ->set('name', 'New Name')->set('email', $user->email)
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('New Name');
});

test('the settings page copies a changed name to kadi.accounts', function () {
    fakeKadiAccounts([['id' => 555, 'name' => 'Old', 'email' => 'old@example.com']]);
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com', 'linked_id' => 555]);

    Livewire::actingAs($user)->test('pages::settings.profile')
        ->set('name', 'Wanjiru Kamau')->set('email', 'old@example.com')
        ->call('updateProfileInformation')->assertHasNoErrors();

    // The game shows a first name only, the same rule the account was created with.
    expect(DB::connection('kadi')->table('accounts')->where('id', 555)->value('name'))->toBe('Wanjiru');
});

test('the game account is found by customer id even when the e-mail changes in the same save', function () {
    fakeKadiAccounts([['id' => 556, 'name' => 'Old', 'email' => 'old@example.com']]);
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com', 'linked_id' => 556]);

    Livewire::actingAs($user)->test('pages::settings.profile')
        ->set('name', 'Brian Otieno')->set('email', 'brand-new@example.com')
        ->call('updateProfileInformation')->assertHasNoErrors();

    expect(DB::connection('kadi')->table('accounts')->where('id', 556)->value('name'))->toBe('Brian');
});

test('an unlinked player is found by the e-mail they had before the save', function () {
    fakeKadiAccounts([['id' => 557, 'name' => 'Old', 'email' => 'old@example.com']]);
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com', 'linked_id' => null]);

    Livewire::actingAs($user)->test('pages::settings.profile')
        ->set('name', 'Grace Achieng')->set('email', 'moved@example.com')
        ->call('updateProfileInformation')->assertHasNoErrors();

    expect(DB::connection('kadi')->table('accounts')->where('id', 557)->value('name'))->toBe('Grace');
});

test('nothing is written to kadi.accounts when the name did not change', function () {
    fakeKadiAccounts([['id' => 558, 'name' => 'Untouched', 'email' => 'same@example.com']]);
    $user = User::factory()->create(['name' => 'Same Name', 'email' => 'same@example.com', 'linked_id' => 558]);

    Livewire::actingAs($user)->test('pages::settings.profile')
        ->set('email', 'same@example.com')->call('updateProfileInformation')->assertHasNoErrors();

    expect(DB::connection('kadi')->table('accounts')->where('id', 558)->value('name'))->toBe('Untouched');
});

test('a game database that is down never stops a profile save', function () {
    // The kadi connection points at a database that has no accounts table: the write throws.
    config(['database.connections.kadi' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge('kadi');
    $user = User::factory()->create(['name' => 'Old Name', 'linked_id' => 559]);

    Livewire::actingAs($user)->test('pages::settings.profile')
        ->set('name', 'Still Saved')->set('email', $user->email)
        ->call('updateProfileInformation')->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Still Saved');
});

// --- Changing the name: the profile page -----------------------------------

test('the profile page refuses a blocked new name', function () {
    $user = User::factory()->create(['name' => 'Real Person', 'linked_id' => null]);

    Livewire::actingAs($user)->test(Show::class)
        ->set('name', 'Kadi Official')
        ->call('updateProfile')
        ->assertHasErrors('name');

    expect($user->fresh()->name)->toBe('Real Person');
});

test('the profile page copies a changed name to kadi.accounts', function () {
    fakeKadiAccounts([['id' => 600, 'name' => 'Old', 'email' => 'p@example.com']]);
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'p@example.com', 'linked_id' => null]);

    Livewire::actingAs($user)->test(Show::class)
        ->set('name', 'Otieno Junior')
        ->call('updateProfile')->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Otieno Junior')
        ->and(DB::connection('kadi')->table('accounts')->where('email', 'p@example.com')->value('name'))->toBe('Otieno');
});

// --- The helper ------------------------------------------------------------

test('the account name is the first word of the full name', function (string $full, string $expected) {
    expect(KadiAccountSync::accountName($full))->toBe($expected);
})->with([['Wanjiru Kamau', 'Wanjiru'], ['  Brian   Otieno ', 'Brian'], ['Madonna', 'Madonna'], ['', '']]);

// --- Operator commands -----------------------------------------------------

test('blocked-names:add blocks a name at once, and stores it normalised', function () {
    test()->artisan('blocked-names:add', ['terms' => ['Big B0SS', 'Grand Wizard'], '--reason' => 'impersonation'])->assertSuccessful();

    expect(NameGuard::isBlocked('big boss'))->toBeTrue()
        ->and(NameGuard::isBlocked('Grand Wizard'))->toBeTrue()
        ->and(BlockedName::where('term', NameGuard::normalise('Big Boss'))->value('reason'))->toBe('impersonation');
});

test('blocked-names:add --contains makes a broad entry, and re-adding updates rather than duplicates', function () {
    test()->artisan('blocked-names:add', ['terms' => ['forbiddenword']])->assertSuccessful();
    expect(NameGuard::isBlocked('xxforbiddenwordxx'))->toBeFalse();

    test()->artisan('blocked-names:add', ['terms' => ['forbiddenword'], '--contains' => true])->assertSuccessful();

    expect(NameGuard::isBlocked('xxforbiddenwordxx'))->toBeTrue()
        ->and(BlockedName::where('term', NameGuard::normalise('forbiddenword'))->count())->toBe(1);
});

test('blocked-names:add skips a term with no letters', function () {
    test()->artisan('blocked-names:add', ['terms' => ['---']])->expectsOutputToContain('no letters')->assertSuccessful();

    expect(BlockedName::where('term', '')->exists())->toBeFalse();
});

test('blocked-names:remove unblocks, and complains about unknown entries', function () {
    BlockedName::create(['term' => 'temporary']);
    expect(NameGuard::isBlocked('Temporary'))->toBeTrue();

    test()->artisan('blocked-names:remove', ['terms' => ['Temporary']])->assertSuccessful();
    expect(NameGuard::isBlocked('Temporary'))->toBeFalse();

    test()->artisan('blocked-names:remove', ['terms' => ['never-there']])->assertFailed();
});

test('blocked-names:list shows the entries', function () {
    test()->artisan('blocked-names:list')->expectsOutputToContain('kadionline')->assertSuccessful();

    BlockedName::query()->delete();
    test()->artisan('blocked-names:list')->expectsOutputToContain('empty')->assertSuccessful();
});

test('blocked-names:check tells the operator which entry matched', function () {
    test()->artisan('blocked-names:check', ['name' => 'Kadi Support'])->expectsOutputToContain('blocked')->assertFailed();
    test()->artisan('blocked-names:check', ['name' => 'Wanjiru Kamau'])->expectsOutputToContain('is allowed')->assertSuccessful();
});

test('the seeded starter list is in place after migrating', function () {
    expect(BlockedName::count())->toBeGreaterThanOrEqual(10)
        ->and(BlockedName::where('term', 'admin')->exists())->toBeTrue();
});

test('the cached list is what the guard reads, so a request does not hit the database each time', function () {
    NameGuard::terms();

    DB::enableQueryLog();
    NameGuard::isBlocked('Some Name');
    NameGuard::isBlocked('Another Name');

    expect(collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'blocked_names')))->toBeEmpty();
    expect(Cache::has('name-guard.terms'))->toBeTrue();
});
