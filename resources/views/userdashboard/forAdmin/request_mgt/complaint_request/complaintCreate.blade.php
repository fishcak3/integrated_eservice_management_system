<x-layouts::app :title="__('File Complaint')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('admin.requests.index', ['type' => 'complaints']) }}">Complaint Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>     
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">

        {{-- Header --}}
        <div>
            <flux:heading size="lg">File New Complaint</flux:heading>
            <flux:subheading>Record a new blotter or incident report.</flux:subheading>
        </div>

        <flux:card class="max-w-3xl mx-auto w-full">
            {{-- Initialize Alpine Data for Toggling Complainant Mode --}}
            <form id="create-complaint-form" action="{{ route('complaints.store') }}" method="POST" class="space-y-6"
                  x-data="{ isWalkIn: false }">
                @csrf

                {{-- SECTION 1: COMPLAINANT INFO --}}
                <div class="space-y-4">
                    <flux:heading size="md" class="border-b border-zinc-200 dark:border-zinc-700 pb-2">1. Complainant Information</flux:heading>

                    {{-- TOGGLE: Complainant Mode --}}
                    <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div>
                            <flux:label>Complainant Source</flux:label>
                            <div class="text-xs text-zinc-500">Is the complainant a registered resident?</div>
                        </div>
                        <flux:switch x-model="isWalkIn" label="Walk-in / Non-Resident" />
                    </div>

                    {{-- SCENARIO A: Registered Resident (Hidden if Walk-in) --}}
                    <div x-show="!isWalkIn" x-transition>
                        <flux:field>
                            <flux:label>Select Resident</flux:label>
                            <flux:select name="complainant_id" placeholder="Search resident name..." searchable>
                                <flux:select.option value="">Select a resident...</flux:select.option>
                                @foreach ($residents as $resident)
                                    <flux:select.option value="{{ $resident->id }}">
                                        {{ $resident->resident->lname ?? '' }}, {{ $resident->resident->fname ?? $resident->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="complainant_id" />
                        </flux:field>
                    </div>

                    {{-- SCENARIO B: Walk-in Details (Shown only if Walk-in) --}}
                    <div x-show="isWalkIn" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border border-dashed border-zinc-300 dark:border-zinc-700 rounded-lg">
                        <div class="col-span-1 md:col-span-2">
                            <flux:input name="walkin_name" label="Complainant Name" placeholder="Full Name (e.g. Juan Dela Cruz)" />
                        </div>
                        <flux:input name="walkin_phone" label="Contact Number" placeholder="0912..." />
                        <flux:input name="walkin_address" label="Address" placeholder="House No., Street, Brgy..." />
                        <div class="col-span-1 md:col-span-2 text-xs text-zinc-500 italic">
                            * These details will be saved manually for this record.
                        </div>
                    </div>
                </div>

                <flux:separator />

                {{-- SECTION 2: RESPONDENT INFO --}}
                <div class="space-y-4">
                    <flux:heading size="md" class="border-b border-zinc-200 dark:border-zinc-700 pb-2">2. Respondent Information</flux:heading>
                    
                    <flux:field>
                        <flux:label>Respondent Name (The person being complained about)</flux:label>
                        
                        {{-- 1. Add the 'list' attribute here pointing to the ID below --}}
                        <flux:input 
                            name="respondent_name" 
                            list="respondent_list" 
                            placeholder="Search resident or type a new name..." 
                            autocomplete="off" 
                            required 
                        />
                        
                        {{-- 2. Create the Datalist --}}
                        <datalist id="respondent_list">
                            @foreach($residents as $resident)
                                {{-- Format: "Lastname, Firstname" --}}
                                <option value="{{ $resident->resident->lname ?? '' }}, {{ $resident->resident->fname ?? $resident->name }}">
                            @endforeach
                        </datalist>

                        <flux:description>Select from the list or type a name if they are not a resident.</flux:description>
                        <flux:error name="respondent_name" />
                    </flux:field>
                </div>

                <flux:separator />

                {{-- SECTION 3: INCIDENT DETAILS --}}
                <div class="space-y-4">
                    <flux:heading size="md" class="border-b border-zinc-200 dark:border-zinc-700 pb-2">3. Incident Details</flux:heading>

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

                        {{-- Date --}}
                        <flux:field>
                            <flux:label>Date of Incident</flux:label>
                            <flux:input type="date" name="incident_date" max="{{ date('Y-m-d') }}" required />
                            <flux:error name="incident_date" />
                        </flux:field>
                    </div>

                    {{-- Location --}}
                    <flux:field>
                        <flux:label>Location</flux:label>
                        <flux:input name="location" placeholder="Specific area (e.g. Zone 4, Basketball Court)" required />
                        <flux:error name="location" />
                    </flux:field>

                    {{-- Narrative --}}
                    <flux:field>
                        <flux:label>Narrative / Details</flux:label>
                        <flux:textarea name="incident_details" rows="5" placeholder="Describe exactly what happened..." required />
                        <flux:description>Include time, specific actions, and any witnesses if available.</flux:description>
                        <flux:error name="incident_details" />
                    </flux:field>
                </div>

            </form>
        </flux:card>

        {{-- ACTIONS --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-zinc-200 dark:border-zinc-700 max-w-3xl mx-auto w-full">
            <flux:button href="{{ route('admin.requests.index', ['type' => 'complaints']) }}" variant="ghost">Cancel</flux:button>
            <flux:button type="submit" form="create-complaint-form" variant="primary">
                File Complaint
            </flux:button>
        </div>    
    </div>
</x-layouts::app>