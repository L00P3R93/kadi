<?php

use App\Models\User;

test('signed-in players get the install button, banner and instructions dialog', function () {
    $html = $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('data-test="pwa-install-nav"')
        ->toContain('data-test="pwa-install-banner"')
        ->toContain('id="pwa-install-dialog"')
        ->toContain('x-on:pwa-install-help.window')
        ->toContain('aria-live="polite"');
});

test('the wallet page has the install pill in its title row, away from the money controls', function () {
    $html = $this->actingAs(User::factory()->create())
        ->get(route('wallet'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('data-test="pwa-install-chip"')
        ->toContain('data-test="pwa-install-nav"')
        ->toContain('id="pwa-install-dialog"')
        ->not->toContain('data-test="pwa-install-banner"'); // the banner is dashboard-only

    // The pill shares a row with the "Vault" heading (no added height) and comes before any wallet form.
    $title = strpos($html, '💰 Vault');
    $chip = strpos($html, 'data-test="pwa-install-chip"');
    expect($chip)->toBeGreaterThan($title);
    expect($chip - $title)->toBeLessThan(400);
});

test('signed-in players get the install pill and menu entry on the home page', function () {
    $html = $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('data-test="pwa-install-chip"')   // desktop nav
        ->toContain('data-test="pwa-install-menu"')            // phone hamburger menu
        ->toContain('data-test="pwa-install-hero"')            // phone/tablet hero, beside Play Kadi
        ->toContain('id="pwa-install-dialog"')
        ->not->toContain('data-test="pwa-install-banner"')     // never over the hero
        ->not->toContain('data-test="pwa-install-nav"');        // the sidebar only exists in the app layout

    // Every variant opts out of Livewire morphing so a re-render can't hide it.
    expect(substr_count($html, 'wire:ignore'))->toBeGreaterThanOrEqual(2);
});

test('every install entry point opts out of livewire morphing', function () {
    $html = view('components.pwa.install-button', ['variant' => 'banner'])->render()
        .view('components.pwa.install-button', ['variant' => 'chip'])->render()
        .view('components.pwa.install-button', ['variant' => 'hero'])->render()
        .view('components.pwa.install-button', ['variant' => 'menu'])->render();

    expect(substr_count($html, 'wire:ignore'))->toBe(4);
});

test('guests never see any install UI', function (string $route) {
    $html = $this->get(route($route))->assertOk()->getContent();

    expect($html)->not->toContain('pwa-install-nav')
        ->not->toContain('pwa-install-banner')
        ->not->toContain('pwa-install-chip')
        ->not->toContain('pwa-install-menu')
        ->not->toContain('pwa-install-hero')
        ->not->toContain('pwa-install-dialog');
})->with(['login', 'register', 'legal.terms', 'home']);

test('the install module is wired into the app bundle', function () {
    $app = file_get_contents(resource_path('js/app.js'));
    $install = file_get_contents(resource_path('js/pwa/install.js'));

    expect($app)->toContain("from './pwa/install.js'")->toContain('initInstall()');

    // Behaviour the store must keep: capture the event, hide when installed or on Firefox desktop.
    expect($install)->toContain("addEventListener('beforeinstallprompt'")
        ->toContain("addEventListener('appinstalled'")
        ->toContain('isFirefoxDesktop')
        ->toContain('display-mode: standalone')
        ->toContain('maxTouchPoints'); // iPadOS reports as a Mac
});
