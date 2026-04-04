<?php

namespace App\Services;

use Nnjeim\World\World;

class CurrencyService
{
    public static function detectCurrency(): array
    {
        $default = ['code' => 'KES', 'symbol' => 'KES', 'name' => 'Kenyan Shilling'];

        try {
            $ip = request()->ip();

            if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
                return $default;
            }

            $geoResponse = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode");
            if (! $geoResponse) {
                return $default;
            }

            $geo         = json_decode($geoResponse, true);
            $countryCode = $geo['countryCode'] ?? 'KE';

            $countryResult = World::countries(['filters' => ['iso2' => $countryCode], 'with' => ['currencies']]);

            if (! $countryResult->success || empty($countryResult->data)) {
                return $default;
            }

            $country  = $countryResult->data->first();
            $currency = $country->currencies->first();

            if (! $currency) {
                return $default;
            }

            return [
                'code'   => $currency->code   ?? 'KES',
                'symbol' => $currency->symbol ?? 'KES',
                'name'   => $currency->name   ?? 'Kenyan Shilling',
            ];
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
