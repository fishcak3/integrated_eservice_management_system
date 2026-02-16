<x-layouts::app :title="__('New Request')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('admin.requests.index') }}">Document Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        {{-- Header --}}
        <div>
            <flux:heading size="lg">New Document Request</flux:heading>
            <flux:subheading>Create a new request on behalf of a resident.</flux:subheading>
        </div>

        <flux:card class="max-w-2xl mx-auto w-full">
            {{-- ✅ STEP 1: Add ID to form --}}
            <form id="create-request-form" 
                  action="{{ route('admin.requests.store') }}" 
                  method="POST" 
                  class="space-y-6" 
                  x-data="{ isWalkIn: false }">
                @csrf

                {{-- TOGGLE: Request Mode --}}
                <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div>
                        <flux:label>Request Mode</flux:label>
                        <div class="text-xs text-zinc-500">Is the requestor already in the system?</div>
                    </div>
                    {{-- Toggle Switch --}}
                    <flux:switch x-model="isWalkIn" label="Walk-in Guest (No Account)" />
                </div>

                {{-- SCENARIO A: Registered Resident (Hidden if Walk-in) --}}
                <div x-show="!isWalkIn" x-transition>
                    <flux:field>
                        <flux:label>Select Resident</flux:label>
                        <flux:select name="user_id" placeholder="Search resident..." searchable>
                            <flux:select.option value="">Select a resident...</flux:select.option>
                            @foreach ($users as $user)
                                @php
                                    $fullName = $user->resident 
                                            ? "{$user->resident->fname} {$user->resident->lname}" 
                                            : $user->email;
                                @endphp
                                <flux:select.option value="{{ $user->id }}">
                                    {{ $fullName }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:error name="user_id" />
                    </flux:field>
                </div>

                {{-- SCENARIO B: Walk-in Details (Shown only if Walk-in) --}}
                <div x-show="isWalkIn" x-transition class="space-y-4 p-4 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-lg">
                    <flux:heading size="sm" class="text-zinc-500">Walk-in Information</flux:heading>

                    <flux:input name="requestor_name" label="Full Name" placeholder="e.g. Juan Dela Cruz" />
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input name="requestor_phone" label="Phone Number" placeholder="0912..." />
                        <flux:input name="requestor_address" label="Address" placeholder="Barangay, Street..." />
                    </div>
                    <flux:description class="text-xs">These details will be saved with the request manually.</flux:description>
                </div>

                <flux:separator />

                {{-- 2. Select Document Type (Always Visible) --}}
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

                {{-- 3. Purpose (Always Visible) --}}
                <flux:field>
                    <flux:label>Purpose</flux:label>
                    <flux:textarea name="purpose" rows="3" placeholder="e.g. For employment requirements, School enrollment..." required />
                    <flux:description>Why is the resident requesting this document?</flux:description>
                    <flux:error name="purpose" />
                </flux:field>

            </form>
        </flux:card>

        <div class="flex items-center justify-end gap-3 pt-4 border-zinc-200 dark:border-zinc-700 max-w-2xl mx-auto w-full">
            <flux:button href="{{ route('admin.requests.index') }}" variant="ghost">Cancel</flux:button>
            
            <flux:button type="submit" form="create-request-form" variant="primary">
                Create Request
            </flux:button>
        </div>

    </div>
</x-layouts::app>