<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Loads config/kadi.php as if the given environment variables were set, whatever the developer's own
 * .env says. env() reads $_ENV, $_SERVER and putenv, so all three are overridden and then restored.
 *
 * @param  array<string, string>  $env
 * @return array<string, mixed>
 */
function kadiConfigWithEnv(array $env): array
{
    $saved = [];

    foreach ($env as $name => $value) {
        $saved[$name] = [$_ENV[$name] ?? null, $_SERVER[$name] ?? null];
        $_ENV[$name] = $_SERVER[$name] = $value;
        putenv("{$name}={$value}");
    }

    try {
        return require config_path('kadi.php');
    } finally {
        foreach ($saved as $name => [$fromEnv, $fromServer]) {
            putenv($name);
            $fromEnv === null ? Arr::forget($_ENV, $name) : $_ENV[$name] = $fromEnv;
            $fromServer === null ? Arr::forget($_SERVER, $name) : $_SERVER[$name] = $fromServer;
        }
    }
}
