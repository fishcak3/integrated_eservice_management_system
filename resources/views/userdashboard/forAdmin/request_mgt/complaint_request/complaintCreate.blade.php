<x-layouts::app :title="__('File Complaint')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('admin.complaints.index', ['type' => 'complaints']) }}">Complaint Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>      
    </x-slot>

    {{-- Page Header --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">File New Complaint</flux:heading>
            <flux:text variant="subtle">Record a new blotter or incident report.</flux:text>
        </div>
    </div>

    @if(session('error'))
        <flux:card class="mb-6 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-900">
            <flux:heading class="text-red-600 dark:text-red-400">{{ session('error') }}</flux:heading>
        </flux:card>
    @endif

    {{-- MAIN FORM --}}
    <form id="create-complaint-form" action="{{ route('admin.complaints.store') }}" method="POST" class="space-y-10" x-data="{ mode: 'have_account' }">
        @csrf

        {{-- Section 1: Complainant Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Complainant Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Select the type of complainant and provide their identifying details.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Complainant Type Selection --}}
                    <div>
                        <flux:radio.group x-model="mode" name="mode" label="Complainant Type" variant="segmented" size="sm">
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

                        <flux:input name="complainant_name" label="Full Name" placeholder="e.g. Juan Dela Cruz" x-bind:disabled="mode !== 'walk_in'" />
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:input name="complainant_phone" label="Phone Number" placeholder="0912..." x-bind:disabled="mode !== 'walk_in'" />
                            <flux:input name="complainant_address" label="Address" placeholder="Barangay, Street..." x-bind:disabled="mode !== 'walk_in'" />
                        </div>
                        <flux:description class="text-xs">These details will be saved manually for this record.</flux:description>
                    </div>

                    {{-- SCENARIO C: Registered Resident but no account --}}
                    <div x-show="mode === 'registered_resident'" x-transition x-cloak class="space-y-4 p-4 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-lg">
                        <x-resident-search name="resident_id" x-bind:disabled="mode !== 'registered_resident'" />
                        <flux:error name="resident_id" />
                        <flux:description class="text-xs">Select a registered resident profile.</flux:description>
                    </div>

                </flux:card>
            </div>
        </div>

        {{-- Section 2: Respondent Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Respondent Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Identify the person being complained about.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    <flux:field>
                        <flux:label>Respondent</flux:label>
                        
                        {{-- Using your custom search component --}}
                        <x-resident-search name="respondent_id" />
                        
                        <flux:error name="respondent_id" />
                        <flux:description>Search and select the registered resident.</flux:description>
                    </flux:field>
                </flux:card>
            </div>
        </div>

        {{-- Section 3: Incident Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Incident Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Provide the specifics of the complaint, including date, time, location, and a detailed narrative.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Complaint Type --}}
                        <flux:field>
                            <flux:label>Nature of Complaint</flux:label>
                            <flux:select name="complaint_type_id" placeholder="Choose type..." required>
                                @foreach ($complaintTypes as $type)
                                    <flux:select.option value="{{ $type->id }}">
                                        {{ $type->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="complaint_type_id" />
                        </flux:field>

                        {{-- Date & Time --}}
                        <flux:field>
                            <flux:label>Date and Time of Incident</flux:label>
                            <flux:input type="datetime-local" name="incident_at" max="{{ date('Y-m-d\TH:i') }}" required />
                            <flux:error name="incident_at" />
                        </flux:field>
                    </div>

                    {{-- Location --}}
                    <flux:field>
                        <flux:label>Location</flux:label>
                        <flux:input name="location" placeholder="Specific area (e.g. Zone 4, Basketball Court)" required />
                        <flux:error name="location" />
                    </flux:field>

                    {{-- Narrative --}}
                    <flux:field x-data="{ details: '' }">
                        <flux:label>Narrative / Details</flux:label>
                        <flux:textarea 
                            name="incident_details" 
                            rows="5" 
                            placeholder="Describe exactly what happened..." 
                            required 
                            x-model="details"
                            maxlength="1000"
                        />
                        <div class="flex items-start justify-between mt-1">
                            <flux:description>Include time, specific actions, and any witnesses if available.</flux:description>
                            
                            {{-- Display the dynamic character count --}}
                            <span class="text-xs text-zinc-500">
                                <span x-text="details.length"></span> / 1000 characters
                            </span>
                        </div>
                        <flux:error name="incident_details" />
                    </flux:field>

                </flux:card>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6">
            <flux:button href="{{ route('admin.complaints.index', ['type' => 'complaints']) }}" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">File Complaint</flux:button>
        </div>  
    </form>

</x-layouts::app>