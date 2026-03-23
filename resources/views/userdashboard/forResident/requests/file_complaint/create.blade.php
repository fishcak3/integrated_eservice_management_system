<x-layouts::app :title="__('File a Complaint')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('resident.requests.index') }}">Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>File a Complaint</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0 sm:p-4">
        
        {{-- Header Section --}}
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 dark:bg-red-500/20 text-red-600 dark:text-red-400 rounded-lg">
                        <flux:icon name="shield-exclamation" class="w-6 h-6" />
                    </div>
                    <flux:heading size="xl">File a Complaint</flux:heading>
                </div>
                <flux:subheading class="mt-2">Submit a formal report or grievance to the barangay office for mediation or investigation.</flux:subheading>
            </div>
            
            <flux:button href="{{ route('resident.requests.index') }}" icon="arrow-left" variant="subtle">
                Back to List
            </flux:button>
        </div>

        {{-- Form Container --}}
        <flux:card>
            <form action="{{ route('resident.complaints.store') }}" method="POST" class="flex flex-col gap-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                    
                    {{-- 1. Complaint Type Selection --}}
                    <flux:field>
                        <flux:label>Type of Complaint</flux:label>
                        <flux:select name="complaint_type_id" placeholder="Select category..." required>
                            <option value="" disabled selected>Select category...</option>
                            @foreach($complaintTypes as $type)
                                <option value="{{ $type->id }}">
                                    {{ $type->name }} — ({{ ucfirst($type->severity_level) }} Severity)
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="complaint_type_id" />
                    </flux:field>

                    {{-- 2. Incident Date --}}
                    <flux:field>
                        <flux:label>Date of Incident</flux:label>
                        {{-- Set max to today so they can't report a future date --}}
                        <flux:input 
                            type="date" 
                            name="incident_date" 
                            value="{{ old('incident_date') }}" 
                            max="{{ date('Y-m-d') }}"
                            required 
                        />
                        <flux:error name="incident_date" />
                    </flux:field>

                    <div>
                        {{-- 3. Resident Involved --}}
                        <x-resident-search name="resident_id" required="true" />

                        <flux:error name="resident_id" />
                    </div>

                    {{-- 4. Location --}}
                    <flux:field>
                        <flux:label>Location of Incident</flux:label>
                        <flux:input 
                            type="text" 
                            name="location" 
                            placeholder="e.g. Sitio Riverside, near the basketball court" 
                            value="{{ old('location') }}" 
                            required 
                        />
                        <flux:error name="location" />
                    </flux:field>
                </div>

                <flux:separator class="my-2" />

                {{-- 5. Incident Details --}}
                <flux:field>
                    <flux:label>Incident Details</flux:label>
                    <flux:textarea 
                        name="incident_details" 
                        rows="6" 
                        placeholder="Please describe exactly what happened in as much detail as possible. Include times, actions, and any witnesses if applicable..." 
                        required 
                    >{{ old('incident_details') }}</flux:textarea>
                    <flux:error name="incident_details" />
                    
                    <div class="flex gap-2 items-start mt-3 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-600 dark:text-zinc-400">
                        <flux:icon name="information-circle" class="w-5 h-5 text-indigo-500 shrink-0" />
                        <p>
                            Your report will be reviewed confidentially by the barangay officials. You will receive a notification if a hearing (Lupon) is scheduled or if further information is required.
                        </p>
                    </div>
                </flux:field>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button href="{{ route('resident.requests.index') }}" variant="ghost">
                        Cancel
                    </flux:button>
                    
                    <flux:button type="submit" variant="primary" icon="paper-airplane">
                        Submit Complaint
                    </flux:button>
                </div>

            </form>
        </flux:card>
    </div>
</x-layouts::app>