<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Payload for POST /api/v1/wallet-webhooks. Signature verification happens in
 * VerifyKadiWebhookSignature before this ever runs, so an unauthenticated caller never sees a
 * validation error from here.
 */
class WalletWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'event' => ['required', 'string', 'in:wallet.updated'],
            'event_id' => ['required', 'uuid'],
            'customer_id' => ['required', 'integer', 'min:1'],
            'balance' => ['required', 'numeric'],
            'balance_version' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'in:deposit,withdrawal,withdrawal_reversal,game,adjustment,other'],
            'occurred_at' => ['required', 'date'],
        ];
    }
}
