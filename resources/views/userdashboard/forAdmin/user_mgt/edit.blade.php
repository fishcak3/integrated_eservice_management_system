<x-layouts::app :title="__('Edit User')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('users.index') }}">Users</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('users.show', $user->id) }}">{{ $user->email }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Header --}}
    <div class="mb-8">
        <div>
            <flux:heading size="xl" level="1">Edit {{ $user->resident ? $user->resident->full_name : $user->email }}</flux:heading>
            <flux:subheading>Update system credentials, access levels, and verification data.</flux:subheading>
        </div>
    </div>

    {{-- Form Start --}}
    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf
        @method('PUT')

        {{-- SECTION 1: Profile Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Profile Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Update the user's primary email address and display photo.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <flux:input label="Email Address" name="email" type="email" value="{{ old('email', $user->email) }}" required />
                        </div>

                        {{-- Profile Photo --}}
                        <div class="sm:col-span-2 mt-2">
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Profile Photo</label>
                            @if($user->profile_photo)
                                <div class="flex items-center gap-4 mb-4 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Current Profile" class="w-16 h-16 object-cover rounded-full ring-4 ring-white dark:ring-zinc-900 shadow-sm">
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">Current Photo</p>
                                        <p class="text-xs text-zinc-500">Uploading a new photo will replace this one.</p>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="profile_photo" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700" />

                            @error('profile_photo')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- SECTION 2: Security --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Security</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Update the account password. Leave these fields blank to keep the current password.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <flux:input label="New Password" name="password" type="password" placeholder="Leave blank to keep current" />
                        <flux:input label="Confirm Password" name="password_confirmation" type="password" placeholder="Confirm new password" />
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- SECTION 3: System Access (Role) --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">System Access</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Manage the user's role and permission level within the system.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <flux:radio.group name="role" label="Account Role" variant="cards" class="max-sm:flex-col">
                        <flux:radio value="resident" label="Resident" description="Standard access for barangay residents." :checked="old('role', $user->role) === 'resident'" />
                        <flux:radio value="official" label="Barangay Official" description="Elevated access for handling barangay tasks." :checked="old('role', $user->role) === 'official'" />
                        <flux:radio value="admin" label="Administrator" description="Full control over system settings and data." :checked="old('role', $user->role) === 'admin'" />
                    </flux:radio.group>
                </flux:card>
            </div>
        </div>

        {{-- SECTION 4: Account Verification --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Account Verification</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Manage the user's approval status and supporting identity documents.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-8">
                        
                        {{-- Verification Status --}}
                        <flux:radio.group name="verification_status" label="Verification Status" variant="cards" class="max-sm:flex-col">
                            <flux:radio value="pending" label="Pending" description="Account is awaiting review." :checked="old('verification_status', $user->verification_status) === 'pending'" />
                            <flux:radio value="verified" label="Verified" description="Account has been approved." :checked="old('verification_status', $user->verification_status) === 'verified'" />
                            <flux:radio value="rejected" label="Rejected" description="Account verification was denied." :checked="old('verification_status', $user->verification_status) === 'rejected'" />
                        </flux:radio.group>

                        {{-- Supporting Document --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Supporting Document</label>
                            @if($user->supporting_document)
                                <div class="flex flex-col sm:flex-row gap-4 mb-4 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50">
                                    <a href="{{ route('users.document', $user->id) }}" target="_blank" class="shrink-0 block relative w-full sm:w-32 h-24">
                                        <img src="{{ route('users.document', $user->id) }}" alt="Document" class="w-full h-full object-cover rounded-md border border-zinc-200 dark:border-zinc-700">
                                    </a>
                                    <div class="flex flex-col justify-center">
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">Current Document Uploaded</p>
                                        <p class="text-xs text-zinc-500 mt-1">Uploading a new file will replace this document.</p>
                                        <div class="mt-2">
                                            <flux:button size="xs" variant="subtle" icon="arrow-top-right-on-square" href="{{ route('users.document', $user->id) }}" target="_blank">
                                                View Full
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="supporting_document" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-zinc-100 file:text-zinc-700 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700" />

                            @error('supporting_document')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ route('users.show', $user->id) }}" variant="subtle">
                Cancel
            </flux:button>
            
            <flux:button type="submit" variant="primary">
                Update User
            </flux:button>
        </div>
    </form>

</x-layouts::app>