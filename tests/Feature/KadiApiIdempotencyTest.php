<?php

use App\Services\KadiApiService;
use Illuminate\Support\Facades\Http;

const IDEMPOTENCY_BASE = 'https://api.kadi-kings.co.ke/api/v1/';

function idempotencyKeysSentTo(string $urlPattern): array
{
    $keys = [];

    Http::assertSent(function ($request) use ($urlPattern, &$keys) {
        if (str_starts_with($request->url(), $urlPattern)) {
            $keys[] = $request->header('Idempotency-Key')[0] ?? null;
        }

        return true;
    });

    return $keys;
}

test('post sends a random idempotency key that differs across calls', function () {
    Http::fake([
        IDEMPOTENCY_BASE.'stats/*' => Http::response(['data' => []]),
    ]);

    $service = app(KadiApiService::class);
    $service->post('stats/customers/played', ['customer_id' => 1]);
    $service->post('stats/customers/played', ['customer_id' => 1]);

    $keys = idempotencyKeysSentTo(IDEMPOTENCY_BASE.'stats/');

    expect($keys)->toHaveCount(2)
        ->and($keys[0])->not->toBeNull()
        ->and($keys[0])->not->toBe($keys[1]);
});

test('put sends a random idempotency key that differs across calls', function () {
    Http::fake([
        IDEMPOTENCY_BASE.'some-endpoint' => Http::response(['data' => []]),
    ]);

    $service = app(KadiApiService::class);
    $service->put('some-endpoint', ['foo' => 'bar']);
    $service->put('some-endpoint', ['foo' => 'bar']);

    $keys = idempotencyKeysSentTo(IDEMPOTENCY_BASE.'some-endpoint');

    expect($keys)->toHaveCount(2)
        ->and($keys[0])->not->toBeNull()
        ->and($keys[0])->not->toBe($keys[1]);
});

test('an explicitly passed idempotency key is honored', function () {
    Http::fake([
        IDEMPOTENCY_BASE.'some-endpoint' => Http::response(['data' => []]),
    ]);

    $service = app(KadiApiService::class);
    $service->post('some-endpoint', ['foo' => 'bar'], idempotencyKey: 'my-explicit-key');

    Http::assertSent(function ($request) {
        return $request->header('Idempotency-Key')[0] === 'my-explicit-key';
    });
});

test('createCustomer sends the same deterministic key for the same account across two calls', function () {
    Http::fake([
        IDEMPOTENCY_BASE.'customers' => Http::response(['id' => 1]),
    ]);

    $service = app(KadiApiService::class);
    $service->createCustomer(['account_no' => 'ACC-123', 'name' => 'Jane']);
    $service->createCustomer(['account_no' => 'ACC-123', 'name' => 'Jane']);

    $keys = idempotencyKeysSentTo(IDEMPOTENCY_BASE.'customers');

    expect($keys)->toHaveCount(2)
        ->and($keys[0])->toBe('customer-create-ACC-123')
        ->and($keys[0])->toBe($keys[1]);
});

test('createCustomer sends different keys for different accounts', function () {
    Http::fake([
        IDEMPOTENCY_BASE.'customers' => Http::response(['id' => 1]),
    ]);

    $service = app(KadiApiService::class);
    $service->createCustomer(['account_no' => 'ACC-1']);
    $service->createCustomer(['account_no' => 'ACC-2']);

    $keys = idempotencyKeysSentTo(IDEMPOTENCY_BASE.'customers');

    expect($keys[0])->not->toBe($keys[1]);
});

test('get requests do not carry an idempotency key header', function () {
    Http::fake([
        IDEMPOTENCY_BASE.'customers/*' => Http::response(['id' => 1]),
    ]);

    app(KadiApiService::class)->getCustomer(1);

    Http::assertSent(function ($request) {
        return $request->header('Idempotency-Key') === [];
    });
});

test('sequential post calls do not leak idempotency keys onto the shared client', function () {
    Http::fake([
        IDEMPOTENCY_BASE.'*' => Http::response(['data' => []]),
    ]);

    $service = app(KadiApiService::class);
    $service->post('endpoint-a', [], idempotencyKey: 'key-a');
    $service->post('endpoint-b', [], idempotencyKey: 'key-b');

    Http::assertSent(function ($request) {
        if (str_ends_with($request->url(), 'endpoint-a')) {
            return $request->header('Idempotency-Key') === ['key-a'];
        }

        if (str_ends_with($request->url(), 'endpoint-b')) {
            return $request->header('Idempotency-Key') === ['key-b'];
        }

        return true;
    });
});
