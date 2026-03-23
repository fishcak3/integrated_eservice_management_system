<?php

use App\Models\Resident;
use App\Models\ResidentUpdateRequest;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // --- SHARED MODAL STATE ---
    public bool $showDocumentModal = false;
    public string $currentDocumentUrl = '';
    public string $documentType = '';
    public string $adminNotes = '';

    // --- PROFILE UPDATE STATE ---
    public bool $showApproveUpdateModal = false;
    public bool $showRejectUpdateModal = false;
    public ?int $selectedUpdateId = null;

    // --- NEW RESIDENT STATE ---
    public bool $showApproveResidentModal = false;
    public bool $showRejectResidentModal = false;
    public ?int $selectedResidentId = null;

    // ==========================================
    // COMPUTED PROPERTIES
    // ==========================================

    #[Computed]
    public function pendingUpdates()
    {
        return ResidentUpdateRequest::with(['user', 'resident'])
            ->where('status', 'pending')
            ->latest()
            // Using a custom page name prevents pagination clashes with the residents table
            ->paginate(5, ['*'], 'updatesPage'); 
    }

    #[Computed]
    public function pendingResidents()
    {
        return Resident::where('status', 'pending')
            ->latest()
            ->paginate(5, ['*'], 'residentsPage');
    }

    // ==========================================
    // SHARED METHODS
    // ==========================================

    public function viewDocument(string $path)
    {
        $this->currentDocumentUrl = asset('storage/' . $path);
        $this->documentType = str_ends_with(strtolower($path), '.pdf') ? 'pdf' : 'image';
        $this->showDocumentModal = true;
    }

    // ==========================================
    // PROFILE UPDATE METHODS
    // ==========================================

    public function confirmApproveUpdate(int $requestId)
    {
        $this->selectedUpdateId = $requestId;
        $this->showApproveUpdateModal = true;
    }

    public function confirmRejectUpdate(int $requestId)
    {
        $this->selectedUpdateId = $requestId;
        $this->adminNotes = '';
        $this->showRejectUpdateModal = true;
    }

    public function approveUpdate()
    {
        if (!$this->selectedUpdateId) return;

        $request = ResidentUpdateRequest::with('resident')->findOrFail($this->selectedUpdateId);
        $request->resident->update($request->requested_data);
        
        $request->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        
        $this->showApproveUpdateModal = false;
        $this->selectedUpdateId = null;

        $this->dispatch('request-handled', message: 'Profile update approved.');
    }

    public function rejectUpdate()
    {
        if (!$this->selectedUpdateId) return;

        $this->validate([
            'adminNotes' => 'required|string|max:1000',
        ], [
            'adminNotes.required' => 'Please provide a reason for rejection.',
        ]);

        $request = ResidentUpdateRequest::findOrFail($this->selectedUpdateId);
        
        $request->update([
            'status' => 'rejected',
            'admin_notes' => $this->adminNotes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        
        $this->showRejectUpdateModal = false;
        $this->selectedUpdateId = null;
        $this->adminNotes = '';

        $this->dispatch('request-handled', message: 'Profile update rejected.');
    }

    // ==========================================
    // NEW RESIDENT METHODS
    // ==========================================

    public function confirmApproveResident(int $residentId)
    {
        $this->selectedResidentId = $residentId;
        $this->showApproveResidentModal = true;
    }

    public function confirmRejectResident(int $residentId)
    {
        $this->selectedResidentId = $residentId;
        $this->adminNotes = '';
        $this->showRejectResidentModal = true;
    }

    public function approveResident()
    {
        if (!$this->selectedResidentId) return;

        $resident = Resident::findOrFail($this->selectedResidentId);
        
        $resident->update([
            'status' => 'active',
        ]);
        
        $this->showApproveResidentModal = false;
        $this->selectedResidentId = null;

        $this->dispatch('request-handled', message: 'New resident activated successfully.');
    }

    public function rejectResident()
    {
        if (!$this->selectedResidentId) return;

        $this->validate([
            'adminNotes' => 'required|string|max:1000',
        ], [
            'adminNotes.required' => 'Please provide a reason for rejecting this resident.',
        ]);

        $resident = Resident::findOrFail($this->selectedResidentId);
        
        $resident->update([
            'status' => 'rejected',
            // Assuming you have a notes column. If not, remove the line below.
            'admin_notes' => $this->adminNotes, 
        ]);
        
        $this->showRejectResidentModal = false;
        $this->selectedResidentId = null;
        $this->adminNotes = '';

        $this->dispatch('request-handled', message: 'New resident registration rejected.');
    }
}; ?>

<section class="w-full space-y-10">

    {{-- ========================================== --}}
    {{-- SECTION 1: PENDING NEW RESIDENTS           --}}
    {{-- ========================================== --}}
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">{{ __('Pending Resident Registrations') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Review and activate new residents created by officials.') }}</flux:subheading>
            </div>
        </div>

        <div class="border dark:border-zinc-700 rounded-xl overflow-hidden">
            <flux:table class="whitespace-nowrap bg-transparent">
                <flux:table.columns>
                    <flux:table.column>Resident Name</flux:table.column>
                    <flux:table.column>Created At</flux:table.column>
                    <flux:table.column align="end"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->pendingResidents as $resident)
                        <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            
                            <flux:table.cell class="font-medium text-gray-900 dark:text-white">
                                {{ $resident->fname }} {{ $resident->lname }}
                            </flux:table.cell>

                            <flux:table.cell class="text-zinc-500">
                                {{ $resident->created_at->format('M d, Y h:i A') }}
                            </flux:table.cell>
                            
                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                                    <flux:menu>
                                        <flux:menu.item icon="user" href="{{ route('admin.residents.show', $resident->id) }}">View Profile</flux:menu.item>
                                        <flux:menu.item wire:click="confirmApproveResident({{ $resident->id }})" icon="check-circle">Activate</flux:menu.item>
                                        <flux:separator />
                                        <flux:menu.item wire:click="confirmRejectResident({{ $resident->id }})" icon="x-circle" variant="danger">Reject</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>

                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.check-circle class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                    <p>No pending resident registrations.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
        
        @if($this->pendingResidents->hasPages())
            <div class="mt-4">
                {{ $this->pendingResidents->links() }}
            </div>
        @endif
    </div>

    <flux:separator variant="subtle" />

    {{-- ========================================== --}}
    {{-- SECTION 2: PROFILE UPDATE REQUESTS         --}}
    {{-- ========================================== --}}
    <div>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">{{ __('Profile Update Requests') }}</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">{{ __('Review change requests to official resident records.') }}</flux:subheading>
            </div>
        </div>

        <div class="border dark:border-zinc-700 rounded-xl overflow-hidden">
            <flux:table class="whitespace-nowrap bg-transparent">
                <flux:table.columns>
                    <flux:table.column>Resident</flux:table.column>
                    <flux:table.column>Request Type</flux:table.column>
                    <flux:table.column>Submitted</flux:table.column>
                    <flux:table.column>Document</flux:table.column>
                    <flux:table.column align="end"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($this->pendingUpdates as $update)
                        <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            
                            <flux:table.cell class="font-medium text-gray-900 dark:text-white">
                                {{ $update->user->name ?? 'Resident #' . $update->resident_id }}
                            </flux:table.cell>

                            <flux:table.cell class="text-zinc-500">
                                <flux:badge color="zinc">{{ str($update->request_type)->headline() }}</flux:badge>
                            </flux:table.cell>
                            
                            <flux:table.cell class="text-zinc-500">
                                {{ $update->created_at->format('M d, Y h:i A') }}
                            </flux:table.cell>

                            <flux:table.cell>
                                @if($update->supporting_document)
                                    <flux:button size="sm" variant="ghost" wire:click="viewDocument('{{ $update->supporting_document }}')">
                                        <flux:icon.document-text class="size-4 mr-2" /> View Doc
                                    </flux:button>
                                @else
                                    <span class="text-zinc-400 text-sm italic">None provided</span>
                                @endif
                            </flux:table.cell>
                            
                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                                    <flux:menu>
                                        <flux:menu.item icon="user" href="{{ route('admin.residents.show', $update->resident_id) }}">View Profile</flux:menu.item>
                                        <flux:menu.item wire:click="confirmApproveUpdate({{ $update->id }})" icon="check-circle">Approve</flux:menu.item>
                                        <flux:separator />
                                        <flux:menu.item wire:click="confirmRejectUpdate({{ $update->id }})" icon="x-circle" variant="danger">Reject</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>

                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.check-circle class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                    <p>No pending profile updates.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
        
        @if($this->pendingUpdates->hasPages())
            <div class="mt-4">
                {{ $this->pendingUpdates->links() }}
            </div>
        @endif
    </div>

    {{-- ========================================== --}}
    {{-- MODALS                                     --}}
    {{-- ========================================== --}}

    {{-- APPROVE UPDATE MODAL --}}
    <flux:modal wire:model="showApproveUpdateModal" class="min-w-[400px]">
        <div class="mb-4">
            <flux:heading size="lg">Approve Update</flux:heading>
            <flux:subheading>Are you sure you want to approve this profile update? This will permanently modify the resident's official records.</flux:subheading>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <flux:button variant="ghost" wire:click="$set('showApproveUpdateModal', false)">Cancel</flux:button>
            <flux:button variant="primary" wire:click="approveUpdate">Yes, Approve</flux:button>
        </div>
    </flux:modal>

    {{-- REJECT UPDATE MODAL --}}
    <flux:modal wire:model="showRejectUpdateModal" class="min-w-[400px] md:w-[500px]">
        <form wire:submit="rejectUpdate">
            <div class="mb-6 space-y-4">
                <div>
                    <flux:heading size="lg">Reject Update</flux:heading>
                    <flux:subheading>Are you sure you want to reject this profile update? Please provide a reason so the resident can correct it.</flux:subheading>
                </div>
                <flux:textarea wire:model="adminNotes" label="Reason for Rejection" placeholder="e.g., The provided document is blurry..." rows="3" />
            </div>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showRejectUpdateModal', false)">Cancel</flux:button>
                <flux:button type="submit" variant="danger">Yes, Reject</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- APPROVE NEW RESIDENT MODAL --}}
    <flux:modal wire:model="showApproveResidentModal" class="min-w-[400px]">
        <div class="mb-4">
            <flux:heading size="lg">Activate Resident</flux:heading>
            <flux:subheading>Are you sure you want to approve this new resident? Their status will be set to active.</flux:subheading>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <flux:button variant="ghost" wire:click="$set('showApproveResidentModal', false)">Cancel</flux:button>
            <flux:button variant="primary" wire:click="approveResident">Yes, Activate</flux:button>
        </div>
    </flux:modal>

    {{-- REJECT NEW RESIDENT MODAL --}}
    <flux:modal wire:model="showRejectResidentModal" class="min-w-[400px] md:w-[500px]">
        <form wire:submit="rejectResident">
            <div class="mb-6 space-y-4">
                <div>
                    <flux:heading size="lg">Reject Registration</flux:heading>
                    <flux:subheading>Are you sure you want to reject this resident registration?</flux:subheading>
                </div>
                <flux:textarea wire:model="adminNotes" label="Reason for Rejection" placeholder="e.g., Duplicate record found..." rows="3" />
            </div>
            <div class="flex justify-end gap-2">
                <flux:button variant="ghost" wire:click="$set('showRejectResidentModal', false)">Cancel</flux:button>
                <flux:button type="submit" variant="danger">Yes, Reject</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- DOCUMENT VIEWER MODAL --}}
    <flux:modal wire:model="showDocumentModal" class="md:w-[800px]">
        <div class="mb-4 flex justify-between items-center">
            <flux:heading size="lg">Document Viewer</flux:heading>
            <flux:button size="sm" variant="ghost" wire:click="$set('showDocumentModal', false)">Close</flux:button>
        </div>
        
        <div class="bg-zinc-100 dark:bg-zinc-900 rounded-lg overflow-hidden flex items-center justify-center min-h-[400px]">
            @if($currentDocumentUrl)
                @if($documentType === 'pdf')
                    <iframe src="{{ $currentDocumentUrl }}" class="w-full h-[600px]" frameborder="0"></iframe>
                @else
                    <img src="{{ $currentDocumentUrl }}" alt="Supporting Document" class="max-w-full max-h-[600px] object-contain">
                @endif
            @else
                <p class="text-zinc-500">Loading document...</p>
            @endif
        </div>
    </flux:modal>

    {{-- Success Notification --}}
    <x-action-message on="request-handled">
        <div class="fixed bottom-4 right-4 bg-zinc-900 text-white px-4 py-2 rounded-lg shadow-lg z-50">
            Action completed successfully.
        </div>
    </x-action-message>
</section>