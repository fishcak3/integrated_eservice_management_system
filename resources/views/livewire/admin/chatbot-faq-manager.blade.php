<div x-data="{ faqToDelete: null }" >

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Chatbot</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Chatbot FAQ Management</flux:heading>
            <flux:subheading size="lg" class="mb-6">Manage the automated responses for your barangay chatbot.</flux:subheading>
        </div>
        <flux:button wire:click="createFaq" variant="primary" icon="plus">Add New FAQ</flux:button>
    </div>

    {{-- Data Table --}}
    <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
        <flux:table class="whitespace-nowrap bg-transparent">
            <flux:table.columns>
                {{-- Added pl-6 here --}}
                <flux:table.column class="pl-6">Keyword Trigger</flux:table.column>
                <flux:table.column>Auth User Response</flux:table.column>
                <flux:table.column>Guest Response</flux:table.column>
                {{-- Added pr-6 here --}}
                <flux:table.column align="end" class="pr-6"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($faqs as $faq)
                    <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                        
                        {{-- Added pl-6 here --}}
                        <flux:table.cell class="pl-6 font-semibold text-zinc-900 dark:text-white">
                            {{ $faq->keyword }}
                        </flux:table.cell>
                        
                        <flux:table.cell class="truncate max-w-xs whitespace-normal text-zinc-600 dark:text-zinc-300">
                            {{ Str::limit($faq->response_auth, 60) }}
                        </flux:table.cell>
                        
                        <flux:table.cell class="truncate max-w-xs whitespace-normal text-zinc-600 dark:text-zinc-300">
                            {{ Str::limit($faq->response_guest, 60) }}
                        </flux:table.cell>
                        
                        {{-- Added pr-6 here --}}
                        <flux:table.cell align="end" class="pr-6">
                            <flux:dropdown>
                                <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-zinc-900 dark:hover:text-white" />

                                <flux:menu align="end">
                                    <flux:menu.item wire:click="editFaq({{ $faq->id }})" icon="pencil-square">
                                        Edit
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item 
                                        x-on:click="faqToDelete = {{ $faq->id }}; $flux.modal('delete-modal').show()" 
                                        icon="trash" 
                                        class="text-red-600 hover:text-red-700 dark:text-red-400"
                                    >
                                        Delete
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>

                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4" class="text-center text-zinc-500 py-8">
                            <div class="flex flex-col items-center justify-center">
                                <flux:icon.chat-bubble-left-ellipsis class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                <p>No FAQs found. Click "Add New FAQ" to get started.</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Create/Edit Modal using Flux --}}
    <flux:modal wire:model="isModalOpen" :name="'faq-modal'" class="md:w-3/4 lg:w-1/2">
        <div class="p-6">
            <flux:heading size="lg" class="mb-4">{{ $faqId ? 'Edit FAQ' : 'Create New FAQ' }}</flux:heading>

            <form wire:submit.prevent="saveFaq" class="space-y-4">
                <flux:input wire:model="keyword" label="Keyword Trigger" placeholder="e.g., clearance" required />
                <p class="text-xs text-zinc-500 -mt-2">The bot will trigger this response if the user's message contains this word.</p>

                <flux:textarea wire:model="response_auth" label="Response for Logged-In Users" rows="3" placeholder="e.g., Click here to request your clearance." required />
                
                <flux:textarea wire:model="response_guest" label="Response for Guests (Not Logged In)" rows="3" placeholder="e.g., Please register or log in to request a clearance online." required />

                <div class="flex justify-end gap-2 mt-6">
                    <flux:button wire:click="$set('isModalOpen', false)" variant="subtle">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save FAQ</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-modal" class="md:w-96">
        <div class="p-6">
            <flux:heading size="lg" class="mb-2">Delete FAQ</flux:heading>
            <flux:subheading class="mb-6">Are you sure you want to delete this FAQ? This action cannot be undone.</flux:subheading>

            <div class="flex justify-end gap-2">
                <flux:button x-on:click="$flux.modal('delete-modal').close()" variant="subtle">Cancel</flux:button>
                
                {{-- Calls your existing Livewire method using the ID stored in Alpine, then closes modal --}}
                <flux:button x-on:click="$wire.deleteFaq(faqToDelete); $flux.modal('delete-modal').close()" variant="danger">
                    Delete FAQ
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>