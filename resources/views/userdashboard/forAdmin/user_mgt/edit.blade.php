<x-layouts::app :title="__('Edit User')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('users.index') }}">Users</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $user->email }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        <div class="mb-6">
            <flux:heading size="lg">Edit User Account</flux:heading>
            <flux:subheading>Update account credentials and resident profile information.</flux:subheading>
        </div>

        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-8">
                
                {{-- SECTION 1: ACCOUNT CREDENTIALS --}}
                <flux:card>
                    <div class="mb-4">
                        <flux:heading size="md">Account Credentials</flux:heading>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        
                        {{-- Role Selection --}}
                        <flux:select label="System Role" name="role" placeholder="Select role...">
                            <flux:select.option value="resident" :selected="old('role', $user->role) === 'resident'">Resident</flux:select.option>
                            <flux:select.option value="official" :selected="old('role', $user->role) === 'official'">Barangay Official</flux:select.option>
                            <flux:select.option value="admin" :selected="old('role', $user->role) === 'admin'">Administrator</flux:select.option>
                        </flux:select>

                        <flux:input label="Email Address" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                        
                        {{-- Password Section (Optional on Edit) --}}
                        <flux:input label="New Password" name="password" type="password" placeholder="Leave blank to keep current password" />
                        <flux:input label="Confirm New Password" name="password_confirmation" type="password" placeholder="Retype new password" />
                        
                        {{-- Profile Photo --}}
                        <div class="col-span-1 md:col-span-2">
                            @if($user->profile_photo)
                                <div class="flex items-center gap-4 mb-4">
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Current Profile" class="w-16 h-16 object-cover rounded-full ring-2 ring-zinc-200 dark:ring-zinc-700">
                                    <div class="text-sm text-zinc-500">
                                        <p>Current Photo</p>
                                    </div>
                                </div>
                            @endif
                            <flux:input type="file" label="Update Profile Photo" name="profile_photo" />
                        </div>
                    </div>
                </flux:card>

                {{-- 
                    RESIDENT INFORMATION 
                    We use optional($user->resident) to prevent errors if this is an admin without a resident profile 
                --}}
                @php $res = $user->resident; @endphp

                {{-- SECTION 2: PERSONAL INFORMATION --}}
                <flux:card>
                    <div class="mb-4">
                        <flux:heading size="md">Personal Information</flux:heading>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <flux:input label="First Name" name="fname" value="{{ old('fname', optional($res)->fname) }}" required />
                            <flux:input label="Middle Name" name="mname" value="{{ old('mname', optional($res)->mname) }}" />
                            <flux:input label="Last Name" name="lname" value="{{ old('lname', optional($res)->lname) }}" required />
                            <flux:input label="Suffix" name="suffix" value="{{ old('suffix', optional($res)->suffix) }}" />
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            {{-- Date Input: Ensure YYYY-MM-DD format --}}
                            <flux:input label="Date of Birth" name="birthdate" type="date" 
                                value="{{ old('birthdate', optional($res)->birthdate ? \Carbon\Carbon::parse($res->birthdate)->format('Y-m-d') : '') }}" required />
                            
                            <flux:select label="Sex" name="sex" placeholder="Select...">
                                <flux:select.option value="male" :selected="old('sex', optional($res)->sex) === 'male'">Male</flux:select.option>
                                <flux:select.option value="female" :selected="old('sex', optional($res)->sex) === 'female'">Female</flux:select.option>
                            </flux:select>

                            <flux:select label="Civil Status" name="civil_status" placeholder="Select...">
                                <flux:select.option value="single" :selected="old('civil_status', optional($res)->civil_status) === 'single'">Single</flux:select.option>
                                <flux:select.option value="married" :selected="old('civil_status', optional($res)->civil_status) === 'married'">Married</flux:select.option>
                                <flux:select.option value="widowed" :selected="old('civil_status', optional($res)->civil_status) === 'widowed'">Widowed</flux:select.option>
                                <flux:select.option value="separated" :selected="old('civil_status', optional($res)->civil_status) === 'separated'">Separated</flux:select.option>
                            </flux:select>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <flux:input label="Phone Number" name="phone_number" value="{{ old('phone_number', optional($res)->phone_number) }}" />
                            <flux:input label="Mother's Maiden Name" name="mother_maiden_name" value="{{ old('mother_maiden_name', optional($res)->mother_maiden_name) }}" />
                        </div>
                    </div>
                </flux:card>

                {{-- SECTION 3: ADDRESS --}}
                <flux:card>
                    <div class="mb-4">
                        <flux:heading size="md">Current Address</flux:heading>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <flux:input label="Region" name="region" value="{{ old('region', optional($res)->region) }}" />
                        <flux:input label="Province" name="province" value="{{ old('province', optional($res)->province) }}" />
                        <flux:input label="Municipality" name="municipality" value="{{ old('municipality', optional($res)->municipality) }}" />
                        
                        <flux:input label="Barangay" name="barangay" value="{{ old('barangay', optional($res)->barangay) }}" />
                        <flux:input label="Purok" name="purok" value="{{ old('purok', optional($res)->purok) }}" />
                        <flux:input label="Street" name="street" value="{{ old('street', optional($res)->street) }}" />
                        
                        <flux:input label="Zone" name="zone" value="{{ old('zone', optional($res)->zone) }}" />
                        <flux:input label="Sitio" name="sitio" value="{{ old('sitio', optional($res)->sitio) }}" />
                        <flux:input label="Household ID" name="household_id" value="{{ old('household_id', optional($res)->household_id) }}" />
                    </div>
                </flux:card>

                {{-- SECTION 4: SECTORAL --}}
                <flux:card>
                    <div class="mb-4">
                        <flux:heading size="md">Sectors & Status</flux:heading>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
                        <flux:checkbox label="Solo Parent" name="solo_parent" value="1" :checked="old('solo_parent', (bool) optional($res)->solo_parent)" />
                        <flux:checkbox label="OFW" name="ofw" value="1" :checked="old('ofw', (bool) optional($res)->ofw)" />
                        <flux:checkbox label="PWD" name="is_pwd" value="1" :checked="old('is_pwd', (bool) optional($res)->is_pwd)" />
                        <flux:checkbox label="4Ps Beneficiary" name="is_4ps" value="1" :checked="old('is_4ps', (bool) optional($res)->is_4ps)" />
                        <flux:checkbox label="Senior Citizen" name="senior_citizen" value="1" :checked="old('senior_citizen', (bool) optional($res)->senior_citizen)" />
                        <flux:checkbox label="Registered Voter" name="voter" value="1" :checked="old('voter', (bool) optional($res)->voter)" />
                        <flux:checkbox label="Unemployed" name="unemployed" value="1" :checked="old('unemployed', (bool) optional($res)->unemployed)" />
                        <flux:checkbox label="Out of School Youth" name="out_of_school_children" value="1" :checked="old('out_of_school_children', (bool) optional($res)->out_of_school_children)" />
                    </div>
                </flux:card>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3">
                    <flux:button href="{{ route('users.index') }}" variant="subtle">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save Changes</flux:button>
                </div>
            </div>
        </form>
    </div>

</x-layouts::app>