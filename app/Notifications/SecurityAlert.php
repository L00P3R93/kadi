<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SecurityAlert extends Notification
{
    use Queueable;

    public function __construct(public string $change) {}

    /**
     * In-app delivery only; email is handled separately by the
     * themed SecurityAlertEmail mailable.
     *
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
            'change' => $this->change,
        ];
    }
}
