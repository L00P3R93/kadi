<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use NotificationChannels\WebPush\PushSubscription;

class DestroyPushSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Access is enforced by the auth middleware; deletion is scoped to the caller's own subscriptions.
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'endpoint' => ['required', 'string', 'max:'.PushSubscription::ENDPOINT_MAX_LENGTH],
        ];
    }
}
