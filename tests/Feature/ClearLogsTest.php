<?php

use Illuminate\Support\Facades\File;

/**
 * logs:clear truncates log files, so every test runs against a throwaway storage directory: the
 * real storage/logs is never touched.
 */
beforeEach(function () {
    $this->logDir = sys_get_temp_dir().'/kadi-logs-test-'.bin2hex(random_bytes(6));
    File::makeDirectory($this->logDir.'/logs', 0777, true);
    $this->app->useStoragePath($this->logDir);

    $this->makeLog = function (string $name, string $content = "[2026-09-19] local.INFO: something happened\n") {
        File::put($this->logDir.'/logs/'.$name, $content);
    };
    $this->content = fn (string $name) => File::get($this->logDir.'/logs/'.$name);
});

afterEach(function () {
    File::deleteDirectory($this->logDir);
});

test('with no names and no --all it fails fast and changes nothing', function () {
    ($this->makeLog)('laravel.log');

    $this->artisan('logs:clear')->expectsOutputToContain('pass --all')->assertFailed();

    expect(($this->content)('laravel.log'))->not->toBe('');
});

test('names and --all together are refused', function () {
    ($this->makeLog)('laravel.log');

    $this->artisan('logs:clear', ['names' => ['laravel'], '--all' => true, '--force' => true])->assertFailed();

    expect(($this->content)('laravel.log'))->not->toBe('');
});

test('--all clears every application log but skips the dev-tool browser.log', function () {
    ($this->makeLog)('laravel.log');
    ($this->makeLog)('laravel-2026-09-18.log');
    ($this->makeLog)('mpesa.log');
    ($this->makeLog)('browser.log');

    $this->artisan('logs:clear', ['--all' => true, '--force' => true])->assertSuccessful();

    expect(($this->content)('laravel.log'))->toBe('')
        ->and(($this->content)('laravel-2026-09-18.log'))->toBe('')
        ->and(($this->content)('mpesa.log'))->toBe('')
        ->and(($this->content)('browser.log'))->not->toBe('');
});

test('the files are emptied, never deleted', function () {
    ($this->makeLog)('laravel.log');

    $this->artisan('logs:clear', ['--all' => true, '--force' => true])->assertSuccessful();

    expect(File::exists($this->logDir.'/logs/laravel.log'))->toBeTrue()
        ->and(File::size($this->logDir.'/logs/laravel.log'))->toBe(0);
});

test('a name matches the flat file and its rotated daily files, and nothing else', function () {
    ($this->makeLog)('laravel.log');
    ($this->makeLog)('laravel-2026-09-17.log');
    ($this->makeLog)('laravel-2026-09-18.log');
    ($this->makeLog)('laravelextra.log');          // a different log that merely starts with the same letters
    ($this->makeLog)('mpesa.log');
    ($this->makeLog)('mpesa-2026-09-18.log');

    $this->artisan('logs:clear', ['names' => ['laravel'], '--force' => true])->assertSuccessful();

    expect(($this->content)('laravel.log'))->toBe('')
        ->and(($this->content)('laravel-2026-09-17.log'))->toBe('')
        ->and(($this->content)('laravel-2026-09-18.log'))->toBe('')
        ->and(($this->content)('laravelextra.log'))->not->toBe('')
        ->and(($this->content)('mpesa.log'))->not->toBe('')
        ->and(($this->content)('mpesa-2026-09-18.log'))->not->toBe('');
});

test('several names can be given at once', function () {
    ($this->makeLog)('laravel.log');
    ($this->makeLog)('mpesa.log');
    ($this->makeLog)('other.log');

    $this->artisan('logs:clear', ['names' => ['laravel', 'mpesa'], '--force' => true])->assertSuccessful();

    expect(($this->content)('laravel.log'))->toBe('')
        ->and(($this->content)('mpesa.log'))->toBe('')
        ->and(($this->content)('other.log'))->not->toBe('');
});

test('the dev-tool log can still be cleared when named explicitly', function () {
    ($this->makeLog)('browser.log');

    $this->artisan('logs:clear', ['names' => ['browser'], '--force' => true])->assertSuccessful();

    expect(($this->content)('browser.log'))->toBe('');
});

test('a name that matches nothing says so', function () {
    ($this->makeLog)('laravel.log');

    $this->artisan('logs:clear', ['names' => ['nope'], '--force' => true])
        ->expectsOutputToContain('No log files found for "nope"')->assertSuccessful();

    expect(($this->content)('laravel.log'))->not->toBe('');
});

test('names that are paths or patterns are refused, so nothing outside storage/logs can be reached', function (string $name) {
    File::put($this->logDir.'/secret.log', 'keep me');
    ($this->makeLog)('laravel.log');

    $this->artisan('logs:clear', ['names' => [$name], '--force' => true])->assertFailed();

    expect(File::get($this->logDir.'/secret.log'))->toBe('keep me')->and(($this->content)('laravel.log'))->not->toBe('');
})->with(['../secret', '../../etc/passwd', 'laravel.log', '*', 'lara*', 'a/b']);

test('--dry-run lists the files with sizes and changes nothing, without asking', function () {
    ($this->makeLog)('laravel.log', str_repeat('x', 2048));
    ($this->makeLog)('mpesa.log', 'small');
    $before = File::lastModified($this->logDir.'/logs/laravel.log');

    $this->artisan('logs:clear', ['--all' => true, '--dry-run' => true])
        ->expectsOutputToContain('laravel.log')
        ->expectsOutputToContain('Nothing was changed')
        ->assertSuccessful();   // no expectsConfirmation(): a prompt here would fail the test

    expect(($this->content)('laravel.log'))->toBe(str_repeat('x', 2048))
        ->and(($this->content)('mpesa.log'))->toBe('small')
        ->and(File::lastModified($this->logDir.'/logs/laravel.log'))->toBe($before);
});

test('it asks before clearing, and declining changes nothing', function () {
    ($this->makeLog)('laravel.log');

    $this->artisan('logs:clear', ['--all' => true])
        ->expectsConfirmation('Are you sure you want to run this command?', 'no')
        ->assertFailed();

    expect(($this->content)('laravel.log'))->not->toBe('');
});

test('confirming clears the logs', function () {
    ($this->makeLog)('laravel.log');

    $this->artisan('logs:clear', ['--all' => true])
        ->expectsConfirmation('Are you sure you want to run this command?', 'yes')
        ->assertSuccessful();

    expect(($this->content)('laravel.log'))->toBe('');
});

test('--force skips the question', function () {
    ($this->makeLog)('laravel.log');

    $this->artisan('logs:clear', ['--all' => true, '--force' => true])->assertSuccessful();

    expect(($this->content)('laravel.log'))->toBe('');
});

test('an empty logs directory is not an error', function () {
    $this->artisan('logs:clear', ['--all' => true, '--force' => true])->expectsOutputToContain('No matching log files')->assertSuccessful();
});

test('only log files are considered, not other files or folders', function () {
    File::put($this->logDir.'/logs/.gitignore', '*');
    File::put($this->logDir.'/logs/notes.txt', 'keep');
    File::makeDirectory($this->logDir.'/logs/archive.log');

    $this->artisan('logs:clear', ['--all' => true, '--force' => true])->assertSuccessful();

    expect(File::get($this->logDir.'/logs/notes.txt'))->toBe('keep')->and(File::get($this->logDir.'/logs/.gitignore'))->toBe('*');
});
