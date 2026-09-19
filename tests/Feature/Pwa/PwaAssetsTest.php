<?php

function manifest(): array
{
    return json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);
}

test('the manifest is valid JSON with the fields installability needs', function () {
    $manifest = manifest();

    foreach (['id', 'name', 'short_name', 'description', 'start_url', 'scope', 'display', 'background_color', 'theme_color', 'icons'] as $key) {
        expect($manifest)->toHaveKey($key);
    }

    expect($manifest['display'])->toBe('standalone')
        ->and(mb_strlen($manifest['short_name']))->toBeLessThanOrEqual(12)
        ->and($manifest['start_url'])->toStartWith($manifest['scope']);
});

test('every manifest icon exists at its declared size', function () {
    foreach (manifest()['icons'] as $icon) {
        $path = public_path(ltrim($icon['src'], '/'));

        expect(file_exists($path))->toBeTrue("{$icon['src']} is missing");
        expect(implode('x', array_slice(getimagesize($path), 0, 2)))->toBe($icon['sizes']);
    }
});

test('the maskable icon is its own entry rather than a combined purpose', function () {
    $purposes = collect(manifest()['icons'])->pluck('purpose');

    expect($purposes)->toContain('any')->toContain('maskable');
    expect($purposes->contains('any maskable'))->toBeFalse();
});

test('the apple touch icon is opaque and the notification badge is a transparent glyph', function () {
    $apple = imagecreatefrompng(public_path('icons/apple-touch-icon.png'));
    $badge = imagecreatefrompng(public_path('icons/badge-72.png'));

    expect(getimagesize(public_path('icons/apple-touch-icon.png'))[0])->toBe(180);
    expect((imagecolorat($apple, 0, 0) >> 24) & 127)->toBe(0); // iOS paints transparency black

    expect(getimagesize(public_path('icons/badge-72.png'))[0])->toBe(72);
    expect((imagecolorat($badge, 0, 0) >> 24) & 127)->toBe(127); // transparent corner
});

test('the service worker always shows a notification and never touches auth, Livewire or game routes', function () {
    $sw = file_get_contents(public_path('sw.js'));

    expect($sw)->toContain("addEventListener('push'")
        ->toContain('showNotification')
        ->toContain("addEventListener('notificationclick'")
        ->toContain("addEventListener('pushsubscriptionchange'")
        ->toContain('livewire')->toContain('kadig')->toContain('login')->toContain('auth');

    // Cross-origin notification targets must be rejected.
    expect($sw)->toContain('candidate.origin === self.location.origin');
});

test('the service worker fingerprint matches the files clients keep precached', function () {
    // Normalise line endings so Windows and Linux checkouts hash the same.
    $normalize = fn (string $text) => str_replace("\r\n", "\n", $text);
    $sw = $normalize(file_get_contents(public_path('sw.js')));

    preg_match('/^const PRECACHE = .*;$/m', $sw, $precacheLine);
    preg_match('/^\/\/ precache-fingerprint: ([0-9a-f]{16})$/m', $sw, $recorded);

    expect($precacheLine)->not->toBeEmpty('sw.js must declare `const PRECACHE = [...]` on one line')
        ->and($recorded)->not->toBeEmpty('sw.js must record its `// precache-fingerprint:`');

    $actual = substr(hash('sha256', $normalize(file_get_contents(public_path('offline.html')))."\n".$precacheLine[0]), 0, 16);

    expect($recorded[1])->toBe(
        $actual,
        'offline.html or the PRECACHE list changed. Bump VERSION in public/sw.js so browsers re-download it, '
        ."then set `// precache-fingerprint: {$actual}`."
    );
});

test('the service worker version is a simple, non-empty label', function () {
    preg_match("/^const VERSION = '([^']+)';$/m", file_get_contents(public_path('sw.js')), $version);

    expect($version)->not->toBeEmpty()->and($version[1])->toMatch('/^v\d+$/');
});

test('the offline page is self contained', function () {
    $html = file_get_contents(public_path('offline.html'));

    expect($html)->toContain('Try again');
    expect($html)->not->toMatch('#(src|href)=["\']https?://#');
    expect($html)->not->toMatch('#url\(\s*["\']?https?://#');
});

test('the old empty manifest is gone', function () {
    expect(file_exists(public_path('site.webmanifest')))->toBeFalse();
});

test('pages expose the manifest, pwa meta tags and a csrf token', function () {
    $html = $this->get(route('login'))->assertOk()->getContent();

    expect($html)->toContain('<link rel="manifest" href="/manifest.webmanifest">')
        ->toContain('href="/icons/apple-touch-icon.png"')
        ->toContain('name="apple-mobile-web-app-capable"')
        ->toContain('name="mobile-web-app-capable"')
        ->toContain('viewport-fit=cover')
        ->toContain('name="csrf-token"')
        ->not->toContain('site.webmanifest');
});

test('the vapid public key meta only appears when push is configured', function () {
    config(['webpush.vapid.public_key' => null]);
    expect($this->get(route('login'))->getContent())->not->toContain('vapid-public-key');

    config(['webpush.vapid.public_key' => 'BExampleKey']);
    expect($this->get(route('login'))->getContent())->toContain('<meta name="vapid-public-key" content="BExampleKey">');
});
