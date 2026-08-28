<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationsBell extends Component
{
    public bool $open = false;

    public function show(): void
    {
        $this->open = true;
    }

    public function hide(): void
    {
        $this->open = false;
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function markAsRead(string $id): void
    {
        $notification = Auth::user()->notifications()->whereKey($id)->first();

        if ($notification && $notification->read_at === null) {
            $notification->markAsRead();
        }
    }

    public function render(): Factory|View
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->take(20)
            ->get();

        $unreadCount = Auth::user()->unreadNotifications()->count();

        return view('livewire.notifications-bell', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'unreadLabel' => $unreadCount > 99 ? '99+' : (string) $unreadCount,
        ]);
    }
}
