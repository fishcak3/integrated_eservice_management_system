<?php

namespace App\Livewire\Resident;

use Livewire\Component;
use App\Models\ChatMessage;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class LiveChat extends Component
{
    public $newMessage = '';

    public function mount()
    {
        // Mark all messages as read by resident when they open the page
        ChatMessage::where('resident_id', auth()->id())
            ->where('is_read_by_resident', false)
            ->update(['is_read_by_resident' => true]);
    }

    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string']);

        ChatMessage::create([
            'resident_id' => auth()->id(), // They own the chat
            'sender_id' => auth()->id(),   // They sent the message
            'message' => $this->newMessage,
            'is_read_by_admin' => false,
            'is_read_by_resident' => true,
        ]);

        $this->newMessage = '';
    }

    public function render()
    {
        // Security check: ONLY fetch messages where resident_id matches the logged-in user
        $messages = ChatMessage::with('sender')
            ->where('resident_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.resident.live-chat', [
            'messages' => $messages
        ]);
    }
}