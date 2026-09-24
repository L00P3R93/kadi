<?php

namespace App\Services;

use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends one SMS through TextSMS (sms.textsms.co.ke).
 *
 * Endpoint : POST services/sendsms/  {apikey, partnerID, message, shortcode, mobile}
 * Success  : 200 {"responses": [{"response-code": 200, "response-description": "Success", ...}]}
 *            (TextSMS spells the key "respose-code" in some responses, so both are read.)
 *
 * Never retried automatically: a retry after a timeout could deliver a second code. Logs show the
 * masked number and TextSMS's status only, never the message (it carries the code) or the key.
 */
class TextSmsService
{
    public function send(string $mobile, string $message): bool
    {
        $mobile = PhoneNumber::normalize($mobile);
        $config = config('services.textsms');

        if (! PhoneNumber::isValid($mobile) || blank($config['api_key'] ?? null) || blank($config['partner_id'] ?? null)) {
            Log::warning('TextSMS not sent: invalid number or TextSMS is not configured.');

            return false;
        }

        try {
            $response = Http::acceptJson()->timeout(10)->post($config['url'], [
                'apikey' => $config['api_key'],
                'partnerID' => (string) $config['partner_id'],
                'message' => $message,
                'shortcode' => $config['shortcode'],
                'mobile' => $mobile,
            ]);
        } catch (\Throwable $e) {
            Log::warning('TextSMS send to '.self::mask($mobile).' failed: '.class_basename($e));

            return false;
        }

        $first = $response->json('responses.0');
        $code = is_array($first) ? (int) ($first['response-code'] ?? $first['respose-code'] ?? 0) : 0;
        $sent = $response->successful() && $code === 200;

        if (! $sent) {
            $description = is_array($first) && is_string($first['response-description'] ?? null)
                ? mb_substr(strip_tags($first['response-description']), 0, 100)
                : '';

            Log::warning('TextSMS send to '.self::mask($mobile).": HTTP {$response->status()}, code {$code} {$description}");
        }

        return $sent;
    }

    /** 254712345678 -> 2547****5678 */
    public static function mask(?string $mobile): string
    {
        $mobile = (string) $mobile;

        return strlen($mobile) >= 8 ? substr($mobile, 0, 4).'****'.substr($mobile, -4) : '****';
    }
}
