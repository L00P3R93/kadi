<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

/**
 * /log-viewer shows raw application logs, so it is admin-only. The package would open it to everyone
 * outside production if the gate were missing, hence the tests.
 */
test('the viewLogViewer gate is defined', function () {
    expect(Gate::has('viewLogViewer'))->toBeTrue();
});

test('guests are refused', function () {
    $this->get('/log-viewer')->assertForbidden();
    $this->getJson('/log-viewer/api/files')->assertForbidden();
});

test('signed-in players are refused', function () {
    $player = User::factory()->create()->assignRole('player');

    $this->actingAs($player)->get('/log-viewer')->assertForbidden();
    $this->actingAs($player)->getJson('/log-viewer/api/files')->assertForbidden();
});

test('a user with no role is refused', function () {
    $this->actingAs(User::factory()->create())->get('/log-viewer')->assertForbidden();
});

test('admins and super-admins can open it', function (string $role) {
    $admin = User::factory()->create()->assignRole($role);

    $this->actingAs($admin)->get('/log-viewer')->assertOk();
    $this->actingAs($admin)->getJson('/log-viewer/api/files')->assertOk();
})->with(['admin', 'super-admin']);

test('the viewer only lists the application logs, not system paths or the dev-tool log', function () {
    $config = config('log-viewer');

    expect($config['include_files'])->toBe(['*.log', '**/*.log'])
        ->and($config['exclude_files'])->toContain('browser.log')
        ->and($config['require_auth_in_production'])->toBeTrue();
});
