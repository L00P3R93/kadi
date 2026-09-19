<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Payload for POST /api/v1/push-notifications.
 *
 * Notifications are plain text and end up on players' lock screens, so the content rules are
 * strict: short title/body, no control characters, and the tap target is a same-origin PATH
 * only (never a full URL). Recipients are capped per request.
 */
class SendPushApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authentication is done by the push.api middleware.
        return true;
    }

    protected function prepareForValidation(): void
    {
        $clean = fn (mixed $value) => is_string($value)
            ? trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '')
            : $value;

        $this->merge([
            'title' => $clean($this->input('title')),
            'body' => $clean($this->input('body')),
            'idempotency_key' => $this->header('Idempotency-Key'),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $recipient = $this->input('by', 'linked_id') === 'account_no'
            ? ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_\-]+$/']
            : ['required', 'integer', 'min:1', 'max:2147483647'];

        return [
            'by' => ['sometimes', 'string', 'in:linked_id,account_no'],
            'recipients' => ['required', 'array', 'min:1', 'max:'.config('kadi.push_api.max_recipients')],
            'recipients.*' => $recipient,

            'title' => ['required', 'string', 'max:65'],
            'body' => ['required', 'string', 'max:240'],

            // A path such as /wallet or /dashboard?x=1 — never an absolute or protocol-relative URL.
            'url' => ['nullable', 'string', 'max:200', 'regex:#^/(?!/)[^\s\\\\\x00-\x1F]*$#'],
            'tag' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9._:\-]+$/'],
            'urgency' => ['nullable', 'string', 'in:'.implode(',', config('kadi.push_api.allowed_urgencies'))],
            'ttl' => ['nullable', 'integer', 'min:30', 'max:'.config('kadi.push_api.max_ttl')],

            'idempotency_key' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_\-]{8,64}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.regex' => 'The url must be a site path starting with a single "/", for example /wallet.',
            'idempotency_key.regex' => 'The Idempotency-Key header must be 8-64 characters: letters, digits, "-" or "_".',
        ];
    }
}
