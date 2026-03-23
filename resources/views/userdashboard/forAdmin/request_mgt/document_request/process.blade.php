<x-layouts::app :title="__('Process Document')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.documents.index') }}">Document Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.documents.show', $documentRequest->id) }}">{{ $documentRequest->tracking_code }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Workbench</flux:breadcrumbs.item>
        </flux:breadcrumbs> 
    </x-slot>

    {{-- Header --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div class="space-y-1">
            <flux:heading size="xl" level="1">Document Workbench</flux:heading>
            <flux:text variant="subtle">
                Drafting <span class="font-medium text-zinc-900 dark:text-white">{{ $documentRequest->documentType->name ?? 'Document' }}</span> 
                for <span class="font-medium text-zinc-900 dark:text-white">{{ $documentRequest->user->name ?? $documentRequest->guest_name ?? 'Resident' }}</span>
            </flux:text>
        </div>
        
        {{-- Status Indicator --}}
        <flux:badge rounded color="blue" size="sm">Phase: Processing</flux:badge>
    </div>

    {{-- Main Processing Form --}}
    <form action="{{ route('admin.documents.update', $documentRequest->id) }}" method="POST" class="space-y-10">
        @csrf
        @method('PUT')
        
        {{-- Hidden input to automatically update the status when they save from the workbench --}}
        <input type="hidden" name="status" value="processing">

        {{-- Section 1: Official Document Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Official Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Fill in the official control numbers, validity dates, and applicable ordinances.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label>Control / OR Number</flux:label>
                            <flux:input name="control_number" value="{{ old('control_number', $documentRequest->control_number) }}" placeholder="e.g., BRGY-2026-001" />
                            <flux:error name="control_number" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Validity Period</flux:label>
                            <flux:select name="validity_period">
                                <option value="3_months" {{ old('validity_period') == '3_months' ? 'selected' : '' }}>3 Months</option>
                                <option value="6_months" {{ old('validity_period') == '6_months' ? 'selected' : '' }}>6 Months</option>
                                <option value="1_year" {{ old('validity_period') == '1_year' ? 'selected' : '' }}>1 Year</option>
                            </flux:select>
                            <flux:error name="validity_period" />
                        </flux:field>

                        <flux:field class="sm:col-span-2">
                            <flux:label>Applicable Ordinance (Optional)</flux:label>
                            <flux:input name="ordinance_number" value="{{ old('ordinance_number', $documentRequest->ordinance_number) }}" placeholder="e.g., Barangay Ordinance No. 15 Series of 2023" />
                            <flux:error name="ordinance_number" />
                        </flux:field>
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Section 2: Resident & Request Data (Editable) --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Document Content</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Review and edit the information that will be printed directly onto the physical document.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-6">
                        
                        <flux:field>
                            <flux:label>Full Name on Document</flux:label>
                            <flux:input name="printed_name" value="{{ old('printed_name', $documentRequest->user->name ?? $documentRequest->guest_name) }}" />
                            <flux:error name="printed_name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Purpose of Request</flux:label>
                            {{-- Auto-filled from their original request, but editable by the admin --}}
                            <flux:textarea name="purpose" rows="3">{{ old('purpose', $documentRequest->purpose) }}</flux:textarea>
                            <flux:error name="purpose" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Additional Remarks / Findings</flux:label>
                            <flux:textarea name="remarks" rows="2" placeholder="Any specific findings to include on the clearance/certificate?">{{ old('remarks', $documentRequest->remarks) }}</flux:textarea>
                            <flux:error name="remarks" />
                        </flux:field>

                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col-reverse sm:flex-row justify-end items-center gap-4 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ route('admin.documents.show', $documentRequest->id) }}" variant="subtle">Cancel</flux:button>
            
            <flux:button 
                href="{{ route('admin.documents.preview', $documentRequest->id) }}" 
                target="_blank" 
                variant="outline" 
                icon="document-magnifying-glass">
                Generate Draft Preview
            </flux:button>

            <flux:button type="submit" variant="primary" icon="check">
                Save Draft & Mark as Processing
            </flux:button>
        </div>
    </form>
</x-layouts::app>