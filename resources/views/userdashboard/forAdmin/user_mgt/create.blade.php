<x-layouts::app :title="__('Create User')">

    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <flux:breadcrumbs class="mb-2">
                    <flux:breadcrumbs.item href="{{ route('users.index') }}">Users</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
                </flux:breadcrumbs>

                <flux:heading size="xl" level="1">Create New User</flux:heading>
                <flux:subheading class="mt-1">Create an account or link to an existing resident profile.</flux:subheading>
            </div>
        </div>
    </x-slot>

    {{-- 
        CONTAINER FIX: 
        1. We apply the `x-data` to this main wrapper.
        2. We use `w-full max-w-5xl mx-auto` to center it and prevent it from stretching too wide.
    --}}
    <div 
        class="w-full max-w-5xl mx-auto py-6"
        x-data="{ 
            searchQuery: '', 
            searchResults: [], 
            isSearching: false,
            
            // Form Models - Standard Inputs
            resident_id: '',
            fname: '', mname: '', lname: '', suffix: '',
            birthdate: '', sex: '', civil_status: '',
            phone_number: '', mother_maiden_name: '',
            region: '', province: '', municipality: '', barangay: '',
            purok: '', street: '', zone: '', sitio: '', household_id: '',

            // Form Models - Checkboxes
            solo_parent: false,
            ofw: false,
            is_pwd: false,
            is_4ps: false,
            senior_citizen: false,
            voter: false,
            unemployed: false,
            out_of_school_children: false,
            
            // Search Function
            async search() {
                if (this.searchQuery.length < 2) { 
                    this.searchResults = []; 
                    return; 
                }
                this.isSearching = true;
                try {
                    let response = await fetch('/residents/search?q=' + this.searchQuery);
                    this.searchResults = await response.json();
                } catch (e) {
                    console.error('Search error:', e);
                }
                this.isSearching = false;
            },

            // Auto-fill Function
            fillForm(resident) {
                this.resident_id = resident.id;
                this.fname = resident.fname || '';
                this.mname = resident.mname || '';
                this.lname = resident.lname || '';
                this.suffix = resident.suffix || '';
                
                this.birthdate = resident.birthdate ? resident.birthdate.split('T')[0] : '';
                this.sex = resident.sex ? resident.sex.toLowerCase() : '';
                this.civil_status = resident.civil_status ? resident.civil_status.toLowerCase() : '';
                
                this.phone_number = resident.phone_number || '';
                this.mother_maiden_name = resident.mother_maiden_name || '';
                
                this.region = resident.region || '';
                this.province = resident.province || '';
                this.municipality = resident.municipality || '';
                this.barangay = resident.barangay || '';
                this.purok = resident.purok || '';
                this.street = resident.street || '';
                this.zone = resident.zone || '';
                this.sitio = resident.sitio || '';
                this.household_id = resident.household_id || '';

                this.solo_parent = Boolean(Number(resident.solo_parent));
                this.ofw = Boolean(Number(resident.ofw));
                this.is_pwd = Boolean(Number(resident.is_pwd));
                this.is_4ps = Boolean(Number(resident.is_4ps));
                this.senior_citizen = Boolean(Number(resident.senior_citizen));
                this.voter = Boolean(Number(resident.voter));
                this.unemployed = Boolean(Number(resident.unemployed));
                this.out_of_school_children = Boolean(Number(resident.out_of_school_children));

                this.searchResults = [];
                this.searchQuery = '';
            }
        }"
    >

        {{-- SEARCH BAR SECTION --}}
        <flux:card class="mb-8 border-2 border-indigo-100 dark:border-indigo-900/50">
            <div class="relative">
                <flux:input 
                    x-model="searchQuery" 
                    @input.debounce.300ms="search()" 
                    icon="magnifying-glass" 
                    label="Link to Resident" 
                    placeholder="Search name to auto-fill form..." 
                />

                {{-- Search Results Dropdown --}}
                <div x-show="searchResults.length > 0" 
                    class="absolute z-50 w-full mt-1 overflow-hidden bg-white border rounded-md shadow-lg border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700"
                    style="display: none;"
                    x-transition>
                    <ul class="max-h-60 overflow-y-auto">
                        <template x-for="result in searchResults" :key="result.id">
                            <li @click="!result.has_account && fillForm(result)" 
                                :class="result.has_account ? 'opacity-50 cursor-not-allowed bg-zinc-50 dark:bg-zinc-800' : 'cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/30'"
                                class="px-4 py-3 transition-colors border-b border-zinc-100 last:border-0 flex justify-between items-center">
                                <div>
                                    <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                        <span x-text="result.fname"></span> <span x-text="result.lname"></span>
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        <span x-text="result.barangay || 'No Barangay'"></span> • <span x-text="result.birthdate || 'No B-day'"></span>
                                    </div>
                                </div>
                                <template x-if="result.has_account">
                                    <span class="px-2 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-full dark:bg-red-900/30 dark:text-red-400">
                                        Registered
                                    </span>
                                </template>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
            
            <div class="mt-2 text-xs text-zinc-500 h-5">
                <span x-show="resident_id" class="text-green-600 font-medium flex items-center gap-1" style="display: none;">
                    <flux:icon.check-circle variant="mini" /> Linked to resident ID: <span x-text="resident_id"></span>
                </span>
            </div>
        </flux:card>


        {{-- MAIN FORM --}}
        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            {{-- HIDDEN FIELD FOR RESIDENT ID --}}
            <input type="hidden" name="resident_id" x-model="resident_id">

            {{-- SECTION 1: ACCOUNT CREDENTIALS --}}
            <flux:card>
                <div class="mb-4 border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <flux:heading size="lg">Account Credentials</flux:heading>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <flux:select label="System Role" name="role" required>
                        <flux:select.option value="resident" selected>Resident</flux:select.option>
                        <flux:select.option value="official">Barangay Official</flux:select.option>
                        <flux:select.option value="admin">Administrator</flux:select.option>
                    </flux:select>

                    <flux:input label="Email Address" name="email" type="email" required :value="old('email')" />
                    <flux:input label="Password" name="password" type="password" required />
                    <flux:input label="Confirm Password" name="password_confirmation" type="password" required />
                    
                    <div class="col-span-1 md:col-span-2">
                        <flux:input type="file" label="Profile Photo" name="profile_photo" />
                    </div>
                </div>
            </flux:card>

            {{-- SECTION 2: PERSONAL INFORMATION --}}
            <flux:card>
                <div class="mb-4 border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <flux:heading size="lg">Personal Information</flux:heading>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                        <flux:input label="First Name" name="fname" x-model="fname" required />
                        <flux:input label="Middle Name" name="mname" x-model="mname" />
                        <flux:input label="Last Name" name="lname" x-model="lname" required />
                        <flux:input label="Suffix" name="suffix" x-model="suffix" />
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <flux:input label="Date of Birth" name="birthdate" type="date" x-model="birthdate" required />
                        
                        <flux:select label="Sex" name="sex" x-model="sex" placeholder="Select...">
                            <flux:select.option value="male">Male</flux:select.option>
                            <flux:select.option value="female">Female</flux:select.option>
                        </flux:select>

                        <flux:select label="Civil Status" name="civil_status" x-model="civil_status" placeholder="Select...">
                            <flux:select.option value="single">Single</flux:select.option>
                            <flux:select.option value="married">Married</flux:select.option>
                            <flux:select.option value="widowed">Widowed</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <flux:input label="Phone Number" name="phone_number" x-model="phone_number" mask="99999999999" placeholder="09xxxxxxxxx" />
                        <flux:input label="Mother's Maiden Name" name="mother_maiden_name" x-model="mother_maiden_name" />
                    </div>
                </div>
            </flux:card>

            {{-- SECTION 3: ADDRESS --}}
            <flux:card>
                <div class="mb-4 border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <flux:heading size="lg">Current Address</flux:heading>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <flux:input label="Region" name="region" x-model="region" />
                    <flux:input label="Province" name="province" x-model="province" />
                    <flux:input label="Municipality" name="municipality" x-model="municipality" />
                    
                    <flux:input label="Barangay" name="barangay" x-model="barangay" />
                    <flux:input label="Purok" name="purok" x-model="purok" />
                    <flux:input label="Street" name="street" x-model="street" />
                    
                    <flux:input label="Zone" name="zone" x-model="zone" />
                    <flux:input label="Sitio" name="sitio" x-model="sitio" />
                    <flux:input label="Household ID" name="household_id" x-model="household_id" />
                </div>
            </flux:card>

            {{-- SECTION 4: SECTORS --}}
            <flux:card>
                <div class="mb-4 border-b border-zinc-200 pb-4 dark:border-zinc-700">
                    <flux:heading size="lg">Sectors & Status</flux:heading>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                    <flux:checkbox label="Solo Parent" name="solo_parent" x-model="solo_parent" value="1" />
                    <flux:checkbox label="OFW" name="ofw" x-model="ofw" value="1" />
                    <flux:checkbox label="PWD" name="is_pwd" x-model="is_pwd" value="1" />
                    <flux:checkbox label="4Ps Beneficiary" name="is_4ps" x-model="is_4ps" value="1" />
                    <flux:checkbox label="Senior Citizen" name="senior_citizen" x-model="senior_citizen" value="1" />
                    <flux:checkbox label="Registered Voter" name="voter" x-model="voter" value="1" />
                    <flux:checkbox label="Unemployed" name="unemployed" x-model="unemployed" value="1" />
                    <flux:checkbox label="Out of School Youth" name="out_of_school_children" x-model="out_of_school_children" value="1" />
                </div>
            </flux:card>

            <div class="flex items-center justify-end gap-3 pt-6 pb-20">
                <flux:button href="{{ route('users.index') }}" variant="subtle">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Save User & Profile</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>