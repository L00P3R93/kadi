<?php

namespace App\Http\Requests;

/**
 * Payload for POST /api/v1/push-broadcasts: the same content rules as a targeted push (see
 * SendPushApiRequest), without recipients, plus `dry_run`. A real broadcast requires an
 * Idempotency-Key so a retried request can never announce to everyone twice.
 */
class SendPushBroadcastRequest extends SendPushApiRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        unset($rules['by'], $rules['recipients'], $rules['recipients.*']);

        $rules['dry_run'] = ['sometimes', 'boolean'];
        $rules['idempotency_key'] = [$this->boolean('dry_run') ? 'nullable' : 'required', 'string', 'regex:/^[A-Za-z0-9_\-]{8,64}$/'];
        $rules['ttl'] = ['nullable', 'integer', 'min:30', 'max:'.config('kadi.push_api.max_ttl')];

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return parent::messages() + [
            'idempotency_key.required' => 'A broadcast needs an Idempotency-Key header, so a retry cannot announce to everyone twice.',
        ];
    }
}
