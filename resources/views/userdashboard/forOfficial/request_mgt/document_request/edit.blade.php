<x-layouts::app :title="__('Edit Request')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('official.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.documents.index') }}">Document Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.documents.show', $documentRequest->id) }}">{{ $documentRequest->tracking_code }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Header --}}
    <div class="mb-8">
        <div>
            <flux:heading size="xl" level="1">Edit Document Request</flux:heading>
            <flux:subheading>Update details, status, or add remarks for tracking code: <span class="font-semibold">{{ $documentRequest->tracking_code }}</span></flux:subheading>
        </div>
    </div>

    {{-- Form Start --}}
    <form id="edit-request-form" action="{{ route('official.documents.update', $documentRequest->id) }}" method="POST" class="space-y-10">
        @csrf
        @method('PUT')

        {{-- SECTION 1: Requestor Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Requestor Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Review or update the details of the person requesting the document.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    @if($documentRequest->user_id || $documentRequest->resident_id)
                        {{-- Read-only for Registered Users or Verified Residents --}}
                        <div>
                            @if($documentRequest->user_id)
                                <input type="hidden" name="user_id" value="{{ $documentRequest->user_id }}">
                            @endif
                            
                            @if($documentRequest->resident_id)
                                <input type="hidden" name="resident_id" value="{{ $documentRequest->resident_id }}">
                            @endif

                            <flux:text variant="strong">{{ $documentRequest->requestorDisplayName }}</flux:text>
                            <flux:description class="text-xs mt-1">This request is tied to a registered account or resident profile and the requestor cannot be changed here.</flux:description>
                        </div>
                    @else
                        {{-- Editable fields for Walk-ins --}}
                        <div class="space-y-4 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="space-y-4">
                                <flux:input name="requestor_name" label="Walk-in Full Name" value="{{ old('requestor_name', $documentRequest->requestor_name) }}" required />
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <flux:input name="requestor_phone" label="Phone Number" value="{{ old('requestor_phone', $documentRequest->requestor_phone) }}" />
                                    <flux:input name="requestor_address" label="Address" value="{{ old('requestor_address', $documentRequest->requestor_address) }}" />
                                </div>
                            </div>
                            <flux:description class="text-xs">This request is not tied to any account. You can update the requestor's details here.</flux:description>
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>

        {{-- SECTION 2: Document Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Document Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Update the requested document type and the stated purpose for the request.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="space-y-6">
                        <flux:field>
                            <flux:label>Document Type</flux:label>
                            <flux:select name="document_type_id" required>
                                @foreach ($documentTypes as $type)
                                    <flux:select.option value="{{ $type->id }}" :selected="$type->id == old('document_type_id', $documentRequest->document_type_id)">
                                        {{ $type->name }} (₱{{ number_format($type->fee, 2) }})
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="document_type_id" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Purpose</flux:label>
                            <flux:textarea name="purpose" rows="3" required>{{ old('purpose', $documentRequest->purpose) }}</flux:textarea>
                            <flux:error name="purpose" />
                        </flux:field>
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ route('official.documents.index') }}" variant="subtle">
                Cancel
            </flux:button>
            
            <flux:button type="submit" form="edit-request-form" variant="primary">
                Save Changes
            </flux:button>
        </div>
    </form>

</x-layouts::app>