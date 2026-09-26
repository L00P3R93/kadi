<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * In-app note that KadiApi granted the signup bonus (after POST customers/{id}/verified).
 * The bell shows `change` as the title.
 */
class SignupBonusGranted extends Notification
{
    use Queueable;

    public function __construct(public string $amount) {}

    /**
     * @return list<string>
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray($notifiable): array
    {
        return [
            'change' => "Signup bonus: KES {$this->amount} added to your vault. Play KES {$this->amount} to unlock it for withdrawal.",
        ];
    }
}
