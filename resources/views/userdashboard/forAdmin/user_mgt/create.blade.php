<x-layouts::app :title="__('Create User')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('users.index') }}">Users</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Page Header --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">Create New User</flux:heading>
            <flux:text variant="subtle">Create an account and link it to an existing resident profile.</flux:text>
        </div>
    </div>

    @if(session('error'))
        <flux:card class="mb-6 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-900">
            <flux:heading class="text-red-600 dark:text-red-400">{{ session('error') }}</flux:heading>
        </flux:card>
    @endif

    {{-- MAIN FORM --}}
    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf

        {{-- Section 1: Link Resident --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Resident Profile</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Search and select the resident from the barangay database to link to this new account.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 border-zinc-200 dark:border-zinc-800">
                    <div>
                        <x-resident-search required="true" />
                        <flux:error name="resident_id" class="mt-2" />
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Section 2: Account Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Account Credentials</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Set the system role, email address, and a secure password for the new user.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Role Selection (Full Width) --}}
                    <div>
                        <flux:radio.group name="role" label="Account Role" variant="cards" class="flex-col sm:flex-row">
                            <flux:radio value="resident" label="Resident" description="Standard access for barangay residents." checked />
                            <flux:radio value="official" label="Barangay Official" description="Elevated access for handling barangay tasks." />
                            <flux:radio value="admin" label="Administrator" description="Full control over system settings and data." />
                        </flux:radio.group>
                    </div>

                    {{-- Email (Full Width) --}}
                    <div>
                        <flux:input label="Email Address" name="email" type="email" required :value="old('email')" />
                    </div>

                    {{-- Passwords (Side-by-Side Grid) --}}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <flux:input name="password" :label="__('Password')" type="password" autocomplete="new-password" :placeholder="__('Password')" viewable />
                        <flux:input name="password_confirmation" :label="__('Confirm password')" type="password" autocomplete="new-password" :placeholder="__('Confirm')" viewable />
                    </div>

                </flux:card>
            </div>
        </div>

        {{-- Section 3: Profile Photo --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Profile Photo</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Upload an optional profile photo for the user. (Max size: 5MB)
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 border-zinc-200 dark:border-zinc-800">
                    <flux:input type="file" label="Profile Photo" name="profile_photo" accept="image/*" />
                </flux:card>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6">
            <flux:button href="{{ route('users.index') }}" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Create Account</flux:button>
        </div>
    </form>

</x-layouts::app>