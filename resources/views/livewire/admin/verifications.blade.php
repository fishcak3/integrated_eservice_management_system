{{-- resources/views/livewire/admin/verifications.blade.php --}}

<section aria-label="Identity Verifications Management" class="flex h-full w-full flex-1 flex-col gap-6 p-0">

    {{-- Header Section --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>

            <flux:heading size="xl" class="font-bold">Identity Verifications</flux:heading>

            <flux:subheading class="text-zinc-500 dark:text-zinc-400">Review valid IDs submitted by new users.</flux:subheading>
        </div>
    </header>

    {{-- Table Container --}}
    <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
        <flux:table class="whitespace-nowrap bg-transparent">
            <flux:table.columns>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column>Submitted At</flux:table.column>
                <flux:table.column>Document</flux:table.column>
                <flux:table.column align="end">action</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($this->pendingVerifications as $user)
                    <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                        <flux:table.cell class="font-medium text-gray-900 dark:text-white">
                            {{ $user->email }}
                        </flux:table.cell>
                        
                        <flux:table.cell class="text-zinc-500">
                            {{ $user->updated_at->format('M d, Y h:i A') }}
                        </flux:table.cell>
                        
                        <flux:table.cell>
                            <flux:button size="sm" variant="ghost" wire:click="viewDocument('{{ $user->supporting_document }}')">
                                <flux:icon.document-text class="size-4 mr-2" /> View
                            </flux:button>
                        </flux:table.cell>
                        
                        {{-- UPDATED: Action Dropdown Menu --}}
                        <flux:table.cell align="end">
                            <flux:dropdown position="bottom-end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />

                                <flux:menu>
                                    {{-- NOTE: Update the href below to match your actual route for viewing a user/resident profile! --}}
                                    <flux:menu.item icon="user" href="{{ route('users.show', $user->id) }}">
                                        View Profile
                                    </flux:menu.item>
                                    
                                    <flux:menu.item icon="check-circle" wire:click="confirmApprove({{ $user->id }})">
                                        Approve
                                    </flux:menu.item>
                                    
                                    <flux:menu.separator />
                                    
                                    <flux:menu.item icon="x-circle" variant="danger" wire:click="confirmReject({{ $user->id }})">
                                        Reject
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-zinc-500 py-8">
                            <div class="flex flex-col items-center justify-center">
                                <flux:icon.check-circle class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                <p>No pending identity verifications.</p>
                                <p class="text-sm mt-1">All caught up!</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>

        @if($this->pendingVerifications->hasPages())
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                {{ $this->pendingVerifications->links() }}
            </div>
        @endif
    </div>

    {{-- APPROVE CONFIRMATION MODAL --}}
    <flux:modal wire:model="showApproveModal" class="min-w-[400px]">
        <div class="mb-4">
            <flux:heading size="lg">Approve Verification</flux:heading>
            <flux:subheading>Are you sure you want to approve this user's identity document? They will be granted full access.</flux:subheading>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <flux:button variant="ghost" wire:click="$set('showApproveModal', false)">Cancel</flux:button>
            <flux:button variant="primary" wire:click="approveVerification">Yes, Approve</flux:button>
        </div>
    </flux:modal>

    {{-- REJECT CONFIRMATION MODAL --}}
    <flux:modal wire:model="showRejectModal" class="min-w-[400px]">
        <div class="mb-4">
            <flux:heading size="lg">Reject Verification</flux:heading>
            <flux:subheading>Are you sure you want to reject this ID? The user will need to submit a new document.</flux:subheading>
        </div>
        <div class="flex justify-end gap-2 mt-6">
            <flux:button variant="ghost" wire:click="$set('showRejectModal', false)">Cancel</flux:button>
            <flux:button variant="danger" wire:click="rejectVerification">Yes, Reject</flux:button>
        </div>
    </flux:modal>

    {{-- DOCUMENT VIEWER MODAL --}}
    <flux:modal wire:model="showDocumentModal" class="md:w-[800px]">
        <div class="mb-4 flex justify-between items-center">
            <flux:heading size="lg">Document Viewer</flux:heading>
            <flux:button size="sm" variant="ghost" wire:click="$set('showDocumentModal', false)"></flux:button>
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