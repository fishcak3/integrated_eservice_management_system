<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Computed;

class NotificationBell extends Component
{
    // Fetch the 5 most recent notifications
    #[Computed]
    public function notifications()
    {
        return auth()->user()->notifications()->take(5)->get();
    }

    // Get the exact count of unread notifications for the badge
    #[Computed]
    public function unreadCount()
    {
        return auth()->user()->unreadNotifications()->count();
    }

    // Mark a specific notification as read and redirect the user
    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        
        $notification->markAsRead();
        
        if (isset($notification->data['url'])) {
            $this->redirect($notification->data['url'], navigate: true);
        }
    }

    // Clear all unread notifications at once
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}