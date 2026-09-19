<?php

use App\Http\Requests\SendPushApiRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/**
 * docs/push-api.md is the contract the game server's developers build against. These tests fail
 * when a claim in it stops being true. Assertions are booleans with a message on purpose, so a
 * failure names the one wrong claim instead of printing the whole document.
 */
function pushApiDoc(): string
{
    return str_replace("\r\n", "\n", file_get_contents(base_path('docs/push-api.md')));
}

function pushApiDocMentions(string $needle): bool
{
    return str_contains(pushApiDoc(), $needle);
}

test('every repository path the push api doc points at exists', function () {
    preg_match_all('/`((?:app|resources|public|database|tests|routes|config)\/[A-Za-z0-9_\-.\/]*)`/', pushApiDoc(), $matches);

    $missing = collect($matches[1])->unique()->reject(fn (string $path) => file_exists(base_path($path)))->values()->all();

    expect($missing)->toBe([]);
});

test('the endpoint, route, command and env vars the doc names exist', function () {
    expect(Route::has('api.v1.push-notifications.store'))->toBeTrue()
        ->and(pushApiDocMentions('/api/v1/push-notifications'))->toBeTrue()
        ->and(route('api.v1.push-notifications.store', absolute: false))->toBe('/api/v1/push-notifications');

    expect(in_array('push-api:key', array_keys(Artisan::all()), true))->toBeTrue()
        ->and(pushApiDocMentions('push-api:key'))->toBeTrue();

    $example = file_get_contents(base_path('.env.example'));

    foreach (['PUSH_API_KEY_HASHES', 'PUSH_API_ALLOWED_IPS'] as $variable) {
        expect(pushApiDocMentions($variable))->toBeTrue("the doc no longer mentions {$variable}");
        expect(preg_match('/^'.$variable.'=$/m', $example))->toBe(1, "{$variable} must be present and blank in .env.example");
    }
});

test('the doc and the code agree on the limits', function () {
    $config = config('kadi.push_api');

    expect($config['max_recipients'])->toBe(100)
        ->and($config['requests_per_minute'])->toBe(600)
        ->and($config['failed_attempts_per_minute'])->toBe(20)
        ->and($config['per_user_per_hour'])->toBe(30)
        ->and($config['default_ttl'])->toBe(900)
        ->and($config['max_ttl'])->toBe(86400);

    $rules = (new SendPushApiRequest)->rules();
    expect($rules['title'])->toContain('max:65')
        ->and($rules['body'])->toContain('max:240');

    foreach (['| Recipients per request | 100 |', '| Requests per minute, per API key | 600 |', '| Failed authentications per minute, per IP | 20 |', '| Pushes per player per hour | 30 |', '| Title / body length | 65 / 240 characters |', '30 to 86400 seconds (default 900)', '**24 hours**'] as $claim) {
        expect(pushApiDocMentions($claim))->toBeTrue("the doc no longer states `{$claim}`");
    }
});

test('every request field in the rules is documented', function () {
    $fields = collect(array_keys((new SendPushApiRequest)->rules()))
        ->map(fn (string $field) => explode('.', $field)[0])
        ->reject(fn (string $field) => $field === 'idempotency_key') // sent as the Idempotency-Key header
        ->unique();

    expect(pushApiDocMentions('Idempotency-Key'))->toBeTrue();

    foreach ($fields as $field) {
        expect(pushApiDocMentions($field))->toBeTrue("`{$field}` is validated but not documented");
    }
});
