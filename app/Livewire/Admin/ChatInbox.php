<?php

use App\Models\ChatInquiry;

class ChatInbox extends Component
{
    public $replyText = []; // Array to hold replies for different inquiry IDs

    public function sendReply($inquiryId)
    {
        $inquiry = ChatInquiry::findOrFail($inquiryId);
        
        $inquiry->update([
            'admin_reply' => $this->replyText[$inquiryId],
            'status' => 'answered'
        ]);

        // Clear the input
        unset($this->replyText[$inquiryId]);
        
        // Optional: Send a notification to the user here!
    }

    public function render()
    {
        return view('livewire.admin.chat-inbox', [
            'pendingInquiries' => ChatInquiry::with('user')->where('status', 'needs_admin')->get()
        ]);
    }
}