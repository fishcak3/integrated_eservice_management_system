<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ChatInquiry;

class AdminRepliedToInquiry extends Notification
{
    use Queueable;

    public $inquiry;

    // Pass the inquiry into the notification so we know WHICH question was answered
    public function __construct(ChatInquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    // Tell Laravel to save this in the database table you just showed me!
    public function via(object $notifiable): array
    {
        return ['database']; 
    }

    // This is the actual data that gets saved in the "data" column of your notifications table
    public function toDatabase(object $notifiable): array
    {
        return [
            'inquiry_id' => $this->inquiry->id,
            'message' => 'The Barangay Admin has replied to your chat inquiry.',
            'url' => route('resident.inquiries') // The link to click the notification
        ];
    }
}