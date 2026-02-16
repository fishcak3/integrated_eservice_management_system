<x-layouts::app :title="__('Create New Resident')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('residents.index') }}">Residents</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">Register New Resident</flux:heading>
                <flux:subheading>Fill in the details to add a new resident to the registry.</flux:subheading>
            </div>
            
            <flux:button href="{{ route('residents.index') }}" icon="arrow-left" variant="subtle">
                Back to List
            </flux:button>
        </div>

        {{-- Form Start --}}
        <form action="{{ route('residents.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- SECTION 1: Personal Information --}}
            <flux:card>
                <div class="mb-6 border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <flux:heading size="lg">Personal Information</flux:heading>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    {{-- First Name --}}
                    <flux:input 
                        name="fname" 
                        label="First Name" 
                        value="{{ old('fname') }}" 
                        placeholder="e.g. Juan"
                        required 
                    />

                    {{-- Middle Name --}}
                    <flux:input 
                        name="mname" 
                        label="Middle Name" 
                        value="{{ old('mname') }}" 
                    />

                    {{-- Last Name --}}
                    <flux:input 
                        name="lname" 
                        label="Last Name" 
                        value="{{ old('lname') }}" 
                        placeholder="e.g. Dela Cruz"
                        required 
                    />

                    {{-- Suffix --}}
                    <flux:input 
                        name="suffix" 
                        label="Suffix" 
                        value="{{ old('suffix') }}" 
                        placeholder="e.g. Jr." 
                    />

                    {{-- Birthdate --}}
                    <flux:input 
                        type="date" 
                        name="birthdate" 
                        label="Birthdate" 
                        value="{{ old('birthdate') }}" 
                    />

                    {{-- Sex --}}
                    <flux:select name="sex" label="Sex" placeholder="Select sex...">
                        <flux:select.option value="male" :selected="old('sex') == 'male'">Male</flux:select.option>
                        <flux:select.option value="female" :selected="old('sex') == 'female'">Female</flux:select.option>
                    </flux:select>

                    {{-- Civil Status --}}
                    <flux:select name="civil_status" label="Civil Status" placeholder="Select status...">
                        <flux:select.option value="single" :selected="old('civil_status') == 'single'">Single</flux:select.option>
                        <flux:select.option value="married" :selected="old('civil_status') == 'married'">Married</flux:select.option>
                        <flux:select.option value="widowed" :selected="old('civil_status') == 'widowed'">Widowed</flux:select.option>
                        <flux:select.option value="separated" :selected="old('civil_status') == 'separated'">Separated</flux:select.option>
                    </flux:select>

                    {{-- Contact Number --}}
                    <flux:input 
                        name="phone_number" 
                        label="Phone Number" 
                        value="{{ old('phone_number') }}" 
                        mask="99999999999" 
                        placeholder="09xxxxxxxxx" 
                    />

                    {{-- Mother's Maiden Name --}}
                    <div class="md:col-span-2">
                        <flux:input 
                            name="mother_maiden_name" 
                            label="Mother's Maiden Name" 
                            value="{{ old('mother_maiden_name') }}" 
                        />
                    </div>
                </div>
            </flux:card>

            {{-- SECTION 2: Address Information --}}
            <flux:card>
                <div class="mb-6 border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <flux:heading size="lg">Address & Household</flux:heading>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    <flux:input name="purok" label="Purok" value="{{ old('purok') }}" />
                    <flux:input name="sitio" label="Sitio" value="{{ old('sitio') }}" />
                    <flux:input name="street" label="Street" value="{{ old('street') }}" />
                    <flux:input name="zone" label="Zone" value="{{ old('zone') }}" />
                    
                    <flux:input name="barangay" label="Barangay" value="{{ old('barangay') }}" />
                    <flux:input name="municipality" label="Municipality" value="{{ old('municipality') }}" />
                    <flux:input name="province" label="Province" value="{{ old('province') }}" />

                    {{-- Household ID --}}
                    <flux:input 
                        name="household_id" 
                        label="Household ID #" 
                        value="{{ old('household_id') }}" 
                        description="Group members under one ID"
                        class="bg-zinc-50 dark:bg-zinc-900"
                    />
                </div>
            </flux:card>

            {{-- SECTION 3: Socio-Economic Profile --}}
            <flux:card>
                <div class="mb-6 border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <flux:heading size="lg">Socio-Economic Profile</flux:heading>
                    <flux:subheading>Check all that apply to the resident.</flux:subheading>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @php
                        $toggles = [
                            'solo_parent' => 'Solo Parent',
                            'ofw' => 'OFW (Overseas Worker)',
                            'is_pwd' => 'PWD (Person with Disability)',
                            'is_4ps' => '4Ps Beneficiary',
                            'senior_citizen' => 'Senior Citizen',
                            'voter' => 'Registered Voter',
                            'out_of_school_children' => 'Out of School Youth/Child',
                            'osa' => 'OSA',
                            'unemployed' => 'Unemployed',
                            'laborforce' => 'Part of Labor Force',
                            'isy_isc' => 'ISY / ISC',
                        ];
                    @endphp

                    @foreach($toggles as $field => $label)
                        <flux:checkbox 
                            name="{{ $field }}" 
                            value="1" 
                            label="{{ $label }}" 
                            :checked="old($field) ? true : false"
                        />
                    @endforeach
                </div>
            </flux:card>

            {{-- Footer Actions --}}
            <div class="flex items-center justify-end gap-4 pt-4">
                <flux:button href="{{ route('residents.index') }}" variant="subtle">
                    Cancel
                </flux:button>
                
                <flux:button type="submit" variant="primary">
                    Create Resident
                </flux:button>
            </div>

        </form>
    </div>
</x-layouts::app>