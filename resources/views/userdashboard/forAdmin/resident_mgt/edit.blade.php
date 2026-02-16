<x-layouts::app :title="__('Edit Resident')">
    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('residents.index') }}">Residents</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $resident->full_name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>
    
        {{-- Header --}}
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" level="1">Edit Resident</flux:heading>
                <flux:subheading>Update the details for {{ $resident->fname }} {{ $resident->lname }}.</flux:subheading>
            </div>

            <flux:button href="{{ route('residents.index') }}" icon="arrow-left" variant="subtle">
                Back to List
            </flux:button>
        </div>

        {{-- Form Start --}}
        <form action="{{ route('residents.update', $resident->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- SECTION 1: Personal Information --}}
            <flux:card>
                <div class="mb-6 flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <flux:heading size="lg">Personal Information</flux:heading>
                    <flux:badge color="zinc">ID: {{ $resident->id }}</flux:badge>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                    
                    {{-- First Name --}}
                    <flux:input 
                        name="fname" 
                        label="First Name" 
                        value="{{ old('fname', $resident->fname) }}" 
                        required 
                    />

                    {{-- Middle Name --}}
                    <flux:input 
                        name="mname" 
                        label="Middle Name" 
                        value="{{ old('mname', $resident->mname) }}" 
                    />

                    {{-- Last Name --}}
                    <flux:input 
                        name="lname" 
                        label="Last Name" 
                        value="{{ old('lname', $resident->lname) }}" 
                        required 
                    />

                    {{-- Suffix --}}
                    <flux:input 
                        name="suffix" 
                        label="Suffix" 
                        value="{{ old('suffix', $resident->suffix) }}" 
                        placeholder="e.g. Jr." 
                    />

                    {{-- Status Dropdown --}}
                    <flux:select name="status" label="Status" placeholder="Select status...">
                        <flux:select.option value="active" :selected="old('status', $resident->status) == 'active'">Active</flux:select.option>
                        <flux:select.option value="inactive" :selected="old('status', $resident->status) == 'inactive'">Inactive</flux:select.option>
                        <flux:select.option value="pending" :selected="old('status', $resident->status) == 'pending'">Pending</flux:select.option>
                        <flux:select.option value="deceased" :selected="old('status', $resident->status) == 'deceased'">Deceased</flux:select.option>
                        <flux:select.option value="transferred" :selected="old('status', $resident->status) == 'transferred'">Transferred Out</flux:select.option>
                    </flux:select>

                    {{-- Birthdate --}}
                    <flux:input 
                        type="date" 
                        name="birthdate" 
                        label="Birthdate" 
                        value="{{ old('birthdate', $resident->birthdate) }}" 
                    />

                    {{-- Sex --}}
                    <flux:select name="sex" label="Sex" placeholder="Select sex...">
                        <flux:select.option value="male" :selected="old('sex', $resident->sex) == 'male'">Male</flux:select.option>
                        <flux:select.option value="female" :selected="old('sex', $resident->sex) == 'female'">Female</flux:select.option>
                    </flux:select>

                    {{-- Civil Status --}}
                    <flux:select name="civil_status" label="Civil Status" placeholder="Select status...">
                        <flux:select.option value="single" :selected="old('civil_status', $resident->civil_status) == 'single'">Single</flux:select.option>
                        <flux:select.option value="married" :selected="old('civil_status', $resident->civil_status) == 'married'">Married</flux:select.option>
                        <flux:select.option value="widowed" :selected="old('civil_status', $resident->civil_status) == 'widowed'">Widowed</flux:select.option>
                        <flux:select.option value="separated" :selected="old('civil_status', $resident->civil_status) == 'separated'">Separated</flux:select.option>
                    </flux:select>

                    {{-- Contact Number --}}
                    <flux:input 
                        name="phone_number" 
                        label="Phone Number" 
                        value="{{ old('phone_number', $resident->phone_number) }}" 
                        mask="99999999999" 
                        placeholder="09xxxxxxxxx" 
                    />

                    {{-- Mother's Maiden Name --}}
                    <div class="md:col-span-2">
                        <flux:input 
                            name="mother_maiden_name" 
                            label="Mother's Maiden Name" 
                            value="{{ old('mother_maiden_name', $resident->mother_maiden_name) }}" 
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
                    <flux:input name="purok" label="Purok" value="{{ old('purok', $resident->purok) }}" />
                    <flux:input name="sitio" label="Sitio" value="{{ old('sitio', $resident->sitio) }}" />
                    <flux:input name="street" label="Street" value="{{ old('street', $resident->street) }}" />
                    <flux:input name="zone" label="Zone" value="{{ old('zone', $resident->zone) }}" />
                    
                    <flux:input name="barangay" label="Barangay" value="{{ old('barangay', $resident->barangay) }}" />
                    <flux:input name="municipality" label="Municipality" value="{{ old('municipality', $resident->municipality) }}" />
                    <flux:input name="province" label="Province" value="{{ old('province', $resident->province) }}" />

                    {{-- Household ID --}}
                    <flux:input 
                        name="household_id" 
                        label="Household ID #" 
                        value="{{ old('household_id', $resident->household_id) }}" 
                        class="bg-zinc-50 dark:bg-zinc-900"
                    />
                </div>
            </flux:card>

            {{-- SECTION 3: Socio-Economic Profile --}}
            <flux:card>
                <div class="mb-6 border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <flux:heading size="lg">Socio-Economic Profile</flux:heading>
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
                            :checked="old($field, $resident->$field) ? true : false"
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
                    Update Resident
                </flux:button>
            </div>
        </form>

        {{-- Danger Zone --}}
        <div class="mt-10">
            <flux:card class="border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-900/20">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <flux:heading size="lg" class="text-red-800 dark:text-red-400">Delete Resident</flux:heading>
                        <flux:text class="text-red-600 dark:text-red-300">Once you delete a resident, there is no going back. Please be certain.</flux:text>
                    </div>
                    
                    <form action="{{ route('residents.destroy', $resident->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this resident?');">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="danger">
                            Delete Resident
                        </flux:button>
                    </form>
                </div>
            </flux:card>
        </div>

</x-layouts::app>