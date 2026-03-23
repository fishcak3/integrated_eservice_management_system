<x-layouts::app :title="__('Edit Resident')">
    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('official.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.residents.index') }}">Residents</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.residents.show', $resident->id) }}">{{ $resident->fname }} {{ $resident->lname }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>
    
    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl" level="1">Edit {{ $resident->fname }} {{ $resident->lname }}</flux:heading>
            <flux:subheading>Update resident details and information.</flux:subheading>
        </div>
    </div>

    {{-- Form Start --}}
    <form action="{{ route('official.residents.update', $resident->id) }}" method="POST" class="space-y-10">
        @csrf
        @method('PUT')

        {{-- SECTION 1: Personal Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Personal Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Update the resident's basic identifying information and contact details.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        
                        <flux:input name="fname" label="First Name" value="{{ old('fname', $resident->fname) }}" required />
                        <flux:input name="mname" label="Middle Name" value="{{ old('mname', $resident->mname) }}" />
                        <flux:input name="lname" label="Last Name" value="{{ old('lname', $resident->lname) }}" required />
                        <flux:input name="suffix" label="Suffix" value="{{ old('suffix', $resident->suffix) }}" placeholder="e.g. Jr." />

                        <flux:input type="date" name="birthdate" label="Birthdate" value="{{ old('birthdate', $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->format('Y-m-d') : '') }}" />
                        <flux:input name="birth_place" label="Place of Birth" value="{{ old('birth_place', $resident->birth_place) }}" />

                        <flux:input name="citizenship" label="Citizenship" value="{{ old('citizenship', $resident->citizenship) }}" />
                        <flux:select name="sex" label="Sex" placeholder="Select sex...">
                            <flux:select.option value="male" :selected="old('sex', $resident->sex) == 'male'">Male</flux:select.option>
                            <flux:select.option value="female" :selected="old('sex', $resident->sex) == 'female'">Female</flux:select.option>
                            <flux:select.option value="other" :selected="old('sex', $resident->sex) == 'other'">Other</flux:select.option>
                        </flux:select>

                        <flux:select name="civil_status" label="Civil Status" placeholder="Select status...">
                            <flux:select.option value="single" :selected="old('civil_status', $resident->civil_status) == 'single'">Single</flux:select.option>
                            <flux:select.option value="married" :selected="old('civil_status', $resident->civil_status) == 'married'">Married</flux:select.option>
                            <flux:select.option value="widowed" :selected="old('civil_status', $resident->civil_status) == 'widowed'">Widowed</flux:select.option>
                        </flux:select>
                        <flux:input name="phone_number" label="Phone Number" value="{{ old('phone_number', $resident->phone_number) }}" mask="99999999999" placeholder="09xxxxxxxxx" />

                        <div class="sm:col-span-2">
                            <flux:input name="mother_maiden_name" label="Mother's Maiden Name" value="{{ old('mother_maiden_name', $resident->mother_maiden_name) }}" />
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
                    Assign the resident to an existing house and specify their relationship to the household head.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <x-household-search 
                            name="household_id" 
                            :initialId="old('household_id', $resident->household_id)"
                            :initialName="$resident->household?->household_number ?? ''"
                            required
                        />

                        <flux:select name="relation_to_head" label="Relation to Family Head" placeholder="Select relation...">
                            <flux:select.option value="head" :selected="old('relation_to_head', $resident->relation_to_head) == 'head'">Head</flux:select.option>
                            <flux:select.option value="spouse" :selected="old('relation_to_head', $resident->relation_to_head) == 'spouse'">Spouse</flux:select.option>
                            <flux:select.option value="child" :selected="old('relation_to_head', $resident->relation_to_head) == 'child'">Child</flux:select.option>
                            <flux:select.option value="sibling" :selected="old('relation_to_head', $resident->relation_to_head) == 'sibling'">Sibling</flux:select.option>
                            <flux:select.option value="parent" :selected="old('relation_to_head', $resident->relation_to_head) == 'parent'">Parent</flux:select.option>
                            <flux:select.option value="other" :selected="old('relation_to_head', $resident->relation_to_head) == 'other'">Other</flux:select.option>
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
                    Sectors, categories, and special classifications the resident belongs to.
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
                                :checked="old($field, $resident->$field) == 1"
                            />
                        @endforeach
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-4 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ route('official.residents.index') }}" variant="subtle">
                Cancel
            </flux:button>
            
            <flux:button type="submit" variant="primary">
                Update Resident
            </flux:button>
        </div>
    </form>

</x-layouts::app>