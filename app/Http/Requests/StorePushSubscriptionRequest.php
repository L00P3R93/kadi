<?php

namespace App\Http\Requests;

use App\Support\PushEndpoint;
use Illuminate\Foundation\Http\FormRequest;
use NotificationChannels\WebPush\PushSubscription;

class StorePushSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access is enforced by the auth middleware on the route.
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $base64Url = 'regex:/^[A-Za-z0-9_\-]+={0,2}$/';

        return [
            'endpoint' => [
                'required',
                'string',
                'url:https',
                'max:'.PushSubscription::ENDPOINT_MAX_LENGTH,
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! PushEndpoint::isAllowed($value)) {
                        $fail('The push endpoint is not from a supported push service.');
                    }
                },
            ],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:255', $base64Url],
            'keys.auth' => ['required', 'string', 'max:255', $base64Url],
            'contentEncoding' => ['nullable', 'string', 'in:aesgcm,aes128gcm'],
        ];
    }
}
