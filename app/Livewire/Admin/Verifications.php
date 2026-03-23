<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Verifications extends Component
{
    use WithPagination;

    public bool $showDocumentModal = false;
    public string $currentDocumentUrl = '';
    public string $documentType = '';

    // NEW: Properties for our confirmation modals
    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public ?int $selectedUserId = null;

    #[Computed]
    public function pendingVerifications()
    {
        return User::where('verification_status', 'pending')
            ->whereNotNull('supporting_document')
            ->latest()
            ->paginate(10, ['*'], 'verificationsPage');
    }

    public function viewDocument(string $path)
    {
        $this->currentDocumentUrl = asset('storage/' . $path);
        $this->documentType = str_ends_with(strtolower($path), '.pdf') ? 'pdf' : 'image';
        $this->showDocumentModal = true;
    }

    // NEW: Open the Approve Modal
    public function confirmApprove(int $userId)
    {
        $this->selectedUserId = $userId;
        $this->showApproveModal = true;
    }

    // NEW: Open the Reject Modal
    public function confirmReject(int $userId)
    {
        $this->selectedUserId = $userId;
        $this->showRejectModal = true;
    }

    // UPDATED: Actual approval logic
    public function approveVerification()
    {
        if (!$this->selectedUserId) return;

        $user = User::findOrFail($this->selectedUserId);
        $user->update(['verification_status' => 'verified']);
        
        $this->showApproveModal = false;
        $this->selectedUserId = null;

        $this->dispatch('request-handled', message: 'User verified successfully.');
    }

    // UPDATED: Actual rejection logic
    public function rejectVerification()
    {
        if (!$this->selectedUserId) return;

        $user = User::findOrFail($this->selectedUserId);
        $user->update(['verification_status' => 'rejected']);
        
        $this->showRejectModal = false;
        $this->selectedUserId = null;

        $this->dispatch('request-handled', message: 'User verification rejected.');
    }

    public function render()
    {
        return view('livewire.admin.verifications');
    }
}