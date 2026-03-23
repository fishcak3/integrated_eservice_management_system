<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ChatbotFaq;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ChatbotFaqManager extends Component
{
    public $faqs;
    public $faqId = null;
    public $keyword;
    public $response_auth;
    public $response_guest;
    
    public $isModalOpen = false;

    protected $rules = [
        'keyword' => 'required|string|max:255',
        'response_auth' => 'required|string',
        'response_guest' => 'required|string',
    ];

    public function mount()
    {
        $this->loadFaqs();
    }

    public function loadFaqs()
    {
        $this->faqs = ChatbotFaq::orderBy('created_at', 'desc')->get();
    }

    public function createFaq()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    public function editFaq($id)
    {
        $faq = ChatbotFaq::findOrFail($id);
        $this->faqId = $id;
        $this->keyword = $faq->keyword;
        $this->response_auth = $faq->response_auth;
        $this->response_guest = $faq->response_guest;
        
        $this->isModalOpen = true;
    }

    public function saveFaq()
    {
        $this->validate();

        ChatbotFaq::updateOrCreate(
            ['id' => $this->faqId],
            [
                'keyword' => strtolower(trim($this->keyword)),
                'response_auth' => trim($this->response_auth),
                'response_guest' => trim($this->response_guest),
            ]
        );

        $this->isModalOpen = false;
        $this->resetInputFields();
        $this->loadFaqs();

        // Dispatch an event to Alpine!
        $this->dispatch('notify', 
            title: 'Saved!', 
            message: 'The FAQ has been saved successfully.', 
            type: 'success'
        );
    }

    public function deleteFaq($id)
    {
        ChatbotFaq::findOrFail($id)->delete();
        $this->loadFaqs();

        $this->dispatch('notify', 
            title: 'Deleted', 
            message: 'The FAQ has been permanently deleted.', 
            type: 'danger' 
        );
    }

    private function resetInputFields()
    {
        $this->faqId = null;
        $this->keyword = '';
        $this->response_auth = '';
        $this->response_guest = '';

    }

    public function render()
    {
        return view('livewire.admin.chatbot-faq-manager');
    }
}