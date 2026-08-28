<?php

use App\Models\User;

test('profile page renders the security tab with all sections', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('profile'))
        ->assertOk()
        ->assertSee(__('Current Password'), false)
        ->assertSee(__('Two-Factor Authentication'), false)
        ->assertSee(__('Enable 2FA'), false)
        ->assertSee(__('Passkeys'), false)
        ->assertSee(__('No passkeys yet'), false);
});

test('profile security sections require password confirmation before showing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertOk()
        ->assertSee(__('Confirm your password'), false)
        ->assertDontSee(__('No passkeys yet'), false)
        ->assertDontSee(__('Enable 2FA'), false);
});
