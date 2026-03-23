<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ChatMessage;
use App\Models\User;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ChatManager extends Component
{
    public $activeResidentId = null;
    public $newMessage = '';
    public $isOpen = false; 
    
    // Add the search query property
    public $searchQuery = ''; 

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function selectResident($residentId)
    {
        $this->activeResidentId = $residentId;
        
        // Mark all messages in this conversation as read by the admin
        ChatMessage::where('resident_id', $residentId)
            ->where('is_read_by_admin', false)
            ->update(['is_read_by_admin' => true]);
    }

    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string']);

        ChatMessage::create([
            'resident_id' => $this->activeResidentId,
            'sender_id' => auth()->id(), // The Admin sending it
            'message' => $this->newMessage,
            'is_read_by_admin' => true,
            'is_read_by_resident' => false, // Resident hasn't seen it yet
        ]);

        $this->newMessage = '';
    }

    public function render()
    {
        // 1. Calculate total unread messages for the admin notification badge
        $totalUnread = ChatMessage::where('is_read_by_admin', false)->count();

        // 2. Get users and count their specific unread messages
        $conversations = User::with('resident') 
            ->whereHas('residentMessages')      
            ->withCount(['residentMessages as unread_count' => function ($query) {
                // Count only messages not read by admin
                $query->where('is_read_by_admin', false);
            }])
            ->where('id', '!=', auth()->id())   
            ->when($this->searchQuery, function ($query) {
                $query->whereHas('resident', function ($residentQuery) {
                    $residentQuery->where('fname', 'like', '%' . $this->searchQuery . '%')
                                  ->orWhere('lname', 'like', '%' . $this->searchQuery . '%');
                });
            })
            // Optional: Order by most recently updated or by unread count
            ->orderByDesc('unread_count') 
            ->get();

        $messages = [];
        if ($this->activeResidentId) {
            $messages = ChatMessage::with('sender')
                ->where('resident_id', $this->activeResidentId)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('livewire.admin.chat-manager', [
            'conversations' => $conversations,
            'messages' => $messages,
            'totalUnread' => $totalUnread // Pass this to the view
        ]);
    }
}