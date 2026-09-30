<?php

use App\Support\WalletAmount;

test('wallet amounts are floored to whole shillings', function (float|int|string|null $amount, string $shown) {
    expect(WalletAmount::format($amount))->toBe($shown);
})->with([
    'just under a shilling' => [1499.99, '1,499'],
    'half a shilling' => [1499.5, '1,499'],
    'whole' => [1500, '1,500'],
    'float noise below a whole number' => [99.99999999, '100'],
    'string from the api' => ['2500.75', '2,500'],
    'below one' => [0.99, '0'],
    'missing' => [null, '0'],
]);
