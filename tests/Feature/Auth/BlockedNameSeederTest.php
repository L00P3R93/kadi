<?php

use App\Models\BlockedName;
use App\Support\NameGuard;
use Database\Seeders\BlockedNameSeeder;

test('the seeder fills the blocklist', function () {
    BlockedName::query()->delete();

    $this->seed(BlockedNameSeeder::class);

    expect(BlockedName::count())->toBeGreaterThan(80)
        ->and(BlockedName::where('term', 'admin')->value('match'))->toBe('word')
        ->and(BlockedName::where('term', NameGuard::normalise('fuck'))->value('match'))->toBe('contains');
});

test('it is safe to run again: no duplicates, and nothing an operator set is overwritten', function () {
    BlockedName::query()->delete();
    $this->seed(BlockedNameSeeder::class);
    $count = BlockedName::count();

    // An operator changed an entry and added their own.
    BlockedName::where('term', 'admin')->update(['match' => 'contains', 'reason' => 'my note']);
    BlockedName::create(['term' => 'my own term']);

    $this->seed(BlockedNameSeeder::class);

    expect(BlockedName::count())->toBe($count + 1)
        ->and(BlockedName::where('term', 'admin')->value('match'))->toBe('contains')
        ->and(BlockedName::where('term', 'admin')->value('reason'))->toBe('my note')
        ->and(NameGuard::isBlocked('my own term'))->toBeTrue();
});

test('every seeded term is stored in the form names are compared in, so it can actually match', function () {
    BlockedName::query()->delete();
    $this->seed(BlockedNameSeeder::class);

    foreach (BlockedName::pluck('term') as $term) {
        expect($term)->not->toBe('')->and(NameGuard::normalise($term))->toBe($term);
    }
});

test('the usual reserved, placeholder and offensive names are blocked, however they are dressed up', function (string $name) {
    $this->seed(BlockedNameSeeder::class);

    expect(NameGuard::isBlocked($name))->toBeTrue("{$name} should be blocked");
})->with([
    'Admin', 'Administrator', 'Super Admin', 'Kadi Support', 'Kadi Online', 'KadiOnline', 'Official Kadi', 'The Moderator', 'Customer Care', 'Help Desk',
    'M-Pesa', 'Safaricom', 'PayPal',
    'Test', 'Test User', 'Guest', 'Anonymous', 'null', 'Undefined', 'asdf', 'Player',
    'sh1t', 'B1tch', 'F u c k', 'xXfuckXx', 'Hitler', 'Malaya',
    'Admin2026', 'Admin_1', '4dm1n',
]);

test('the seeded list does not block ordinary names, including ones that merely contain a blocked word', function (string $name) {
    $this->seed(BlockedNameSeeder::class);

    expect(NameGuard::isBlocked($name))->toBeFalse("{$name} should be allowed");
})->with([
    'Wanjiru Kamau', 'Brian Otieno', 'Grace Achieng', 'Kiptoo Chebet', 'Mohamed Fatuma', 'Njeri Mwangi', 'John Smith', 'Mary-Jane Watson', 'Peter O\'Neil',
    'Scunthorpe', 'Cassandra', 'Class Representative', 'Passion Bassett', 'Analyst Jones', 'Sexton Blake', 'Assistant Njoroge',
    'Kadiri Musa', 'Usermann Klaus', 'Playerson Lee', 'Botha Pieter', 'Shittim Wood', 'Nilsson Erik',
]);
