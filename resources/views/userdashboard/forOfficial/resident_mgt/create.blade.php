<x-layouts::app :title="__('Create New Resident')">
    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('official.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.residents.index') }}">Residents</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>New Resident</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>
    
    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Register New Resident</flux:heading>
            <flux:subheading>Fill in the details to add a new resident to the registry.</flux:subheading>
        </div>
    </div>

    {{-- Form Start --}}
    <form action="{{ route('official.residents.store') }}" method="POST" class="space-y-10">
        @csrf

        {{-- SECTION 1: Personal Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Personal Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Provide the resident's basic identifying information and contact details.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        
                        <flux:input name="fname" label="First Name" value="{{ old('fname') }}" placeholder="e.g. Juan" required />
                        <flux:input name="mname" label="Middle Name" value="{{ old('mname') }}" placeholder="e.g. Santos" />
                        <flux:input name="lname" label="Last Name" value="{{ old('lname') }}" placeholder="e.g. Dela Cruz" required />
                        <flux:input name="suffix" label="Suffix" value="{{ old('suffix') }}" placeholder="e.g. Jr." />
                        
                        <flux:input type="date" name="birthdate" label="Birthdate" value="{{ old('birthdate') }}" />
                        <flux:input name="birth_place" label="Place of Birth" value="{{ old('birth_place') }}" />

                        <flux:input name="citizenship" label="Citizenship" value="{{ old('citizenship', 'Filipino') }}" />
                        <flux:select name="sex" label="Sex" placeholder="Select sex...">
                            <flux:select.option value="male" :selected="old('sex') == 'male'">Male</flux:select.option>
                            <flux:select.option value="female" :selected="old('sex') == 'female'">Female</flux:select.option>
                        </flux:select>

                        <flux:select name="civil_status" label="Civil Status" placeholder="Select status...">
                            <flux:select.option value="single" :selected="old('civil_status') == 'single'">Single</flux:select.option>
                            <flux:select.option value="married" :selected="old('civil_status') == 'married'">Married</flux:select.option>
                            <flux:select.option value="widowed" :selected="old('civil_status') == 'widowed'">Widowed</flux:select.option>
                            <flux:select.option value="separated" :selected="old('civil_status') == 'separated'">Separated</flux:select.option>
                        </flux:select>
                        <flux:input name="phone_number" label="Phone Number" value="{{ old('phone_number') }}" mask="99999999999" placeholder="09xxxxxxxxx" />

                        <div class="sm:col-span-2">
                            <flux:input name="mother_maiden_name" label="Mother's Maiden Name" value="{{ old('mother_maiden_name') }}" />
                        </div>
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- SECTION 2: Address & Household Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Address & Household</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Assign the resident to an existing house or create a brand new household record.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                {{-- We use x-data to track if the user wants to create a new household --}}
                <flux:card class="p-6" x-data="{ isNewHousehold: {{ old('is_new_household', 'false') }} }">
                    
                    {{-- The Toggle Switch --}}
                    <div class="mb-6 rounded-lg bg-zinc-50 p-4 border border-zinc-200 dark:bg-zinc-800/50 dark:border-zinc-700">
                        <flux:switch 
                            name="is_new_household" 
                            value="true" 
                            x-model="isNewHousehold" 
                            label="Create a New Household" 
                            description="Toggle this on if this resident is the start of a brand new household in the system." 
                        />
                    </div>

                    {{-- STATE A: Existing Household (Shows when toggle is OFF) --}}
                    <div x-show="!isNewHousehold" x-transition.opacity class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        
                        {{-- This should ideally be a searchable dropdown pulling from your Household model --}}
                        <div class="sm:col-span-2">
                            <x-household-search 
                                name="household_id" 
                                x-bind:required="!isNewHousehold" 
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <flux:select name="relation_to_head" label="Relation to Family Head" placeholder="Select relation...">
                                <flux:select.option value="spouse" :selected="old('relation_to_head') == 'spouse'">Spouse</flux:select.option>
                                <flux:select.option value="child" :selected="old('relation_to_head') == 'child'">Child</flux:select.option>
                                <flux:select.option value="sibling" :selected="old('relation_to_head') == 'sibling'">Sibling</flux:select.option>
                                <flux:select.option value="parent" :selected="old('relation_to_head') == 'parent'">Parent</flux:select.option>
                                <flux:select.option value="other" :selected="old('relation_to_head') == 'other'">Other</flux:select.option>
                            </flux:select>
                        </div>
                    </div>

                    {{-- STATE B: New Household Fields (Shows when toggle is ON) --}}
                    <div x-show="isNewHousehold" x-transition.opacity class="grid grid-cols-1 gap-6 sm:grid-cols-2" style="display: none;">
                        
                        <div class="sm:col-span-2 mb-2">
                            <flux:badge color="blue">This resident will automatically be set as the Family Head.</flux:badge>
                        </div>

                        {{-- New Household Number --}}
                        <flux:input 
                            name="household_number" 
                            label="New Household Number" 
                            value="{{ old('household_number') }}" 
                            placeholder="e.g. 205-B"
                            x-bind:required="isNewHousehold"
                        />

                        {{-- Sitio Selection --}}
                        <flux:select name="sitio" label="Sitio" placeholder="Select sitio..." x-bind:required="isNewHousehold">
                            @if(isset($global_sitios) && is_array($global_sitios))
                                @foreach($global_sitios as $sitio)
                                    <flux:select.option value="{{ $sitio }}" :selected="old('sitio') == $sitio">
                                        {{ $sitio }}
                                    </flux:select.option>
                                @endforeach
                            @endif
                        </flux:select>
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- SECTION 3: Socio-Economic Profile --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Socio-Economic Profile</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Check all sectors, categories, and special classifications that apply to the resident.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @php
                            $toggles = [
                                'solo_parent' => 'Solo Parent',
                                'ofw' => 'OFW (Overseas Worker)',
                                'is_pwd' => 'PWD (Person with Disability)',
                                'is_4ps_grantee' => '4Ps Beneficiary',
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
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-4 border-t border-zinc-200 pt-6 dark:border-zinc-800">
            <flux:button href="{{ route('official.residents.index') }}" variant="subtle">
                Cancel
            </flux:button>
            
            <flux:button type="submit" variant="primary">
                Create Resident
            </flux:button>
        </div>

    </form>
</x-layouts::app>