<?php

use App\Livewire\Profile\Security\PasskeyManager;
use App\Models\User;
use Laravel\Passkeys\Passkey;
use Livewire\Livewire;

function createPasskeyFor(User $user, string $name = 'MacBook Pro'): Passkey
{
    return $user->passkeys()->create([
        'name' => $name,
        'credential_id' => bin2hex(random_bytes(32)),
        'credential' => [
            'type' => 'public-key',
            'aaguid' => '00000000-0000-0000-0000-000000000000',
        ],
    ]);
}

function confirmedManager(User $user)
{
    test()->actingAs($user)->withSession(['auth.password_confirmed_at' => time()]);

    return Livewire::test(PasskeyManager::class);
}

test('passkey manager requires fresh password confirmation', function () {
    $user = User::factory()->create();
    createPasskeyFor($user);

    $this->actingAs($user);

    Livewire::test(PasskeyManager::class)
        ->assertSet('accessConfirmed', false)
        ->assertSee(__('Confirm your password'));

    $this->withSession(['auth.password_confirmed_at' => time()]);

    Livewire::test(PasskeyManager::class)
        ->assertSet('accessConfirmed', true);
});

test('access can be confirmed with the current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(PasskeyManager::class)
        ->set('confirmPassword', 'wrong-password')
        ->call('confirmAccess')
        ->assertHasErrors(['confirmPassword'])
        ->assertSet('accessConfirmed', false)
        ->set('confirmPassword', 'password')
        ->call('confirmAccess')
        ->assertSet('accessConfirmed', true);
});

test('mutations are forbidden without a recent confirmation', function () {
    $user = User::factory()->create();
    $passkey = createPasskeyFor($user);

    $this->actingAs($user);

    Livewire::test(PasskeyManager::class)
        ->call('requestRemoval', $passkey->id)
        ->assertStatus(403);

    expect(Passkey::query()->whereKey($passkey->id)->exists())->toBeTrue();
});

test('passkey manager renders the authenticated user passkeys', function () {
    $user = User::factory()->create();
    createPasskeyFor($user, 'iPhone');
    createPasskeyFor($user, 'MacBook Pro');

    confirmedManager($user)
        ->assertSee('iPhone')
        ->assertSee('MacBook Pro')
        ->assertSet('passkeys', function (array $passkeys) {
            expect($passkeys)->toHaveCount(2);

            return true;
        });
});

test('passkey manager does not display other users passkeys', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    createPasskeyFor($other, 'Secret Key');

    confirmedManager($user)
        ->assertDontSee('Secret Key')
        ->assertSet('passkeys', []);
});

test('a passkey can be renamed', function () {
    $user = User::factory()->create();
    $passkey = createPasskeyFor($user, 'Old Name');

    confirmedManager($user)
        ->call('startRenaming', $passkey->id)
        ->assertSet('name', 'Old Name')
        ->set('name', 'Work Laptop')
        ->call('renamePasskey');

    expect($passkey->refresh()->name)->toBe('Work Laptop');
});

test('renaming requires a valid name', function () {
    $user = User::factory()->create();
    $passkey = createPasskeyFor($user, 'Original');

    $manager = confirmedManager($user);

    $manager
        ->call('startRenaming', $passkey->id)
        ->set('name', '')
        ->call('renamePasskey')
        ->assertHasErrors(['name']);

    $manager
        ->set('name', str_repeat('x', 101))
        ->call('renamePasskey')
        ->assertHasErrors(['name']);

    expect($passkey->refresh()->name)->toBe('Original');
});

test('another users passkey cannot be renamed', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $passkey = createPasskeyFor($other, 'Not Yours');

    confirmedManager($user)
        ->call('startRenaming', $passkey->id)
        ->set('name', 'Hijacked')
        ->call('renamePasskey');

    expect($passkey->refresh()->name)->toBe('Not Yours');
});

test('a passkey can be removed after confirmation', function () {
    $user = User::factory()->create();
    $passkey = createPasskeyFor($user, 'Lost Device');

    confirmedManager($user)
        ->call('requestRemoval', $passkey->id)
        ->assertSet('confirmingRemoval', true)
        ->assertSet('pendingRemovalId', $passkey->id)
        ->call('removePasskey')
        ->assertSet('confirmingRemoval', false)
        ->assertSet('pendingRemovalId', null);

    expect(Passkey::query()->whereKey($passkey->id)->exists())->toBeFalse();
});

test('removing one passkey keeps the others', function () {
    $user = User::factory()->create();
    $removed = createPasskeyFor($user, 'Removed');
    $kept = createPasskeyFor($user, 'Kept');

    confirmedManager($user)
        ->call('requestRemoval', $removed->id)
        ->call('removePasskey');

    expect(Passkey::query()->whereKey($removed->id)->exists())->toBeFalse();
    expect(Passkey::query()->whereKey($kept->id)->exists())->toBeTrue();
});

test('another users passkey cannot be removed', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $passkey = createPasskeyFor($other, 'Protected');

    confirmedManager($user)
        ->call('requestRemoval', $passkey->id)
        ->assertSet('confirmingRemoval', false)
        ->call('removePasskey');

    expect(Passkey::query()->whereKey($passkey->id)->exists())->toBeTrue();
});
