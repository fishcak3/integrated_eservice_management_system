<x-layouts::app :title="__('New Request')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('official.documents.index') }}">Document Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Page Header --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">New Document Request</flux:heading>
            <flux:text variant="subtle">Create a new request on behalf of a resident.</flux:text>
        </div>
    </div>

    @if(session('error'))
        <flux:card class="mb-6 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-900">
            <flux:heading class="text-red-600 dark:text-red-400">{{ session('error') }}</flux:heading>
        </flux:card>
    @endif

    {{-- MAIN FORM --}}
    <form id="create-request-form" action="{{ route('official.documents.store') }}" method="POST" class="space-y-10" x-data="{ mode: 'have_account' }">
        @csrf

        {{-- Section 1: Requestor Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Requestor Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Select the type of requestor and provide their identifying details.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Requestor Type Selection --}}
                    <div>
                        <flux:radio.group x-model="mode" name="mode" label="Requestor Type" variant="segmented" size="sm">
                            <flux:radio value="have_account" label="Registered Resident" />
                            <flux:radio value="registered_resident" label="Resident (No Account)" />
                            <flux:radio value="walk_in" label="Walk-in Guest" />
                        </flux:radio.group>
                    </div>

                    {{-- SCENARIO A: Registered Resident with Account --}}
                    <div x-show="mode === 'have_account'" x-transition x-cloak>
                        <flux:field>
                            <x-account-search name="user_id" x-bind:disabled="mode !== 'have_account'" />
                            <flux:error name="user_id" />
                        </flux:field>
                    </div>

                    {{-- SCENARIO B: Walk-in Details --}}
                    <div x-show="mode === 'walk_in'" x-transition x-cloak class="space-y-4 p-4 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-lg">
                        <flux:heading size="sm" class="text-zinc-500">Walk-in Information</flux:heading>

                        <flux:input name="requestor_name" label="Full Name" placeholder="e.g. Juan Dela Cruz" x-bind:disabled="mode !== 'walk_in'" />
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:input name="requestor_phone" label="Phone Number" placeholder="0912..." x-bind:disabled="mode !== 'walk_in'" />
                            <flux:input name="requestor_address" label="Address" placeholder="Barangay, Street..." x-bind:disabled="mode !== 'walk_in'" />
                        </div>
                        <flux:description class="text-xs">These details will be saved with the request manually.</flux:description>
                    </div>

                    {{-- SCENARIO C: Registered Resident but no account --}}
                    <div x-show="mode === 'registered_resident'" x-transition x-cloak class="space-y-4 p-4 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-lg">
                        <x-resident-search name="resident_id" x-bind:disabled="mode !== 'registered_resident'" />
        
                        <flux:error name="resident_id" />
                        <flux:description class="text-xs">These details will be saved with the request manually.</flux:description>
                    </div>

                </flux:card>
            </div>
        </div>

        {{-- Section 2: Document Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Document Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Select the specific document being requested and outline the purpose.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Document Type --}}
                    <flux:field>
                        <flux:label>Document Type</flux:label>
                        <flux:select name="document_type_id" placeholder="Choose document type..." required>
                            @foreach ($documentTypes as $type)
                                <flux:select.option value="{{ $type->id }}">
                                    {{ $type->name }} (₱{{ number_format($type->fee, 2) }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="document_type_id" />
                    </flux:field>

                    {{-- Purpose --}}
                    <flux:field x-data="{ purpose: '' }">
                        <flux:label>Purpose</flux:label>
                        
                        <flux:textarea 
                            name="purpose" 
                            rows="3" 
                            placeholder="e.g. For employment requirements, School enrollment..." 
                            required 
                            x-model="purpose"
                            maxlength="225" 
                        />
                        
                        <div class="flex items-start justify-between mt-1">
                            <flux:description>Why is the resident requesting this document?</flux:description>
                            
                            <span class="text-xs text-zinc-500">
                                <span x-text="purpose.length"></span> / 225 characters
                            </span>
                        </div>
                        
                        <flux:error name="purpose" />
                    </flux:field>

                </flux:card>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6">
            <flux:button href="{{ route('official.documents.index') }}" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Create Request</flux:button>
        </div>
    </form>

</x-layouts::app>