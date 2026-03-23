<?php

namespace App\Livewire\Resident;

use Livewire\Component;
use App\Models\ChatMessage;
use App\Models\ChatbotFaq;
use Illuminate\Support\Facades\Auth;

class FloatingChat extends Component
{
    public $isOpen = false;
    public $newMessage = '';
    public $faqs = [];

    public function mount()
    {
        $this->faqs = ChatbotFaq::pluck('response_auth', 'keyword')->toArray();

    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
        
        if ($this->isOpen && Auth::check()) {
            ChatMessage::where('resident_id', auth()->id())
                ->where('is_read_by_resident', false)
                ->update(['is_read_by_resident' => true]);
        }
    }

    public function sendFaq($keyword)
    {
        if (Auth::check()) {
            $this->newMessage = $keyword;
            $this->sendMessage();
        }
    }

    public function sendMessage()
    {
        $this->validate(['newMessage' => 'required|string']);
        $input = strtolower(trim($this->newMessage));

        // 1. Save the user's message
        if (Auth::check()) {
            ChatMessage::create([
                'resident_id' => auth()->id(),
                'sender_id' => auth()->id(),
                'message' => $this->newMessage,
                'is_read_by_admin' => false,
                'is_read_by_resident' => true,
            ]);
        }

        // 2. Check the FAQ Database for a match
        $foundMatch = false;
        $botResponse = '';

        foreach ($this->faqs as $question => $answer) {
            if (str_contains($input, strtolower($question))) {
                $botResponse = $answer;
                $foundMatch = true;
                break;
            }
        }

        // 3. Determine Bot Behavior
        if ($foundMatch) {
            if (Auth::check()) {
                 $this->saveBotReply($botResponse);
            }
        } else {
            if (Auth::check()) {
                $lastReply = ChatMessage::where('resident_id', auth()->id())
                    ->where(function($query) {
                        $query->where('sender_id', '!=', auth()->id())
                              ->orWhereNull('sender_id'); 
                    })
                    ->orderBy('created_at', 'desc')
                    ->first();

                $adminHasTakenOver = $lastReply && !is_null($lastReply->sender_id);

                if (!$adminHasTakenOver) {
                    $botResponse = "I'm not sure about that. I have forwarded your question to the Barangay Admin. You will receive a reply here soon!";
                    $this->saveBotReply($botResponse);
                }
                
            } else {
                $this->dispatch('guest-bot-reply', message: "I'm not sure about that. Please log in so I can connect you to an Admin.");
            }
        }

        $this->newMessage = '';
    }

    private function saveBotReply($message)
    {
        ChatMessage::create([
            'resident_id' => auth()->id(),
            'sender_id' => null, 
            'message' => $message,
            'is_read_by_admin' => true, 
            'is_read_by_resident' => true,
        ]);
    }

    public function render()
    {
        $messages = [];
        $unreadCount = 0;
        
        if (Auth::check()) {
            // Auto-read new messages if the chat window is currently open while polling
            if ($this->isOpen) {
                ChatMessage::where('resident_id', auth()->id())
                    ->where('is_read_by_resident', false)
                    ->update(['is_read_by_resident' => true]);
            }

            $messages = ChatMessage::with('sender')
                ->where('resident_id', auth()->id())
                ->orderBy('created_at', 'asc')
                ->get();
                
            // Count unread messages (will naturally be 0 if the chat is open)
            $unreadCount = ChatMessage::where('resident_id', auth()->id())
                ->where('is_read_by_resident', false)
                ->count();
        }

        return view('livewire.resident.floating-chat', [
            'messages' => $messages,
            'unreadCount' => $unreadCount
        ]);
    }
}