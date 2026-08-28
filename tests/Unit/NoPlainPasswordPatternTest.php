<?php

use Tests\TestCase;

uses(TestCase::class);

/**
 * Audit C-1 regression guard: the plaintext-password hand-off pattern
 * ("plain_password" cache keys) must never reappear anywhere in application
 * or seeder code. Password material may only move between systems as a
 * one-way hash.
 */
test('no source file references the plain_password pattern', function () {
    $directories = [
        app_path(),
        database_path('seeders'),
    ];

    $violations = [];

    foreach ($directories as $directory) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'plain_password')) {
                $violations[] = $file->getPathname();
            }
        }
    }

    expect($violations)->toBe([]);
});
