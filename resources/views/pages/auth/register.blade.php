<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Resident Create account')" :description="__('Enter your details below to create your account')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input
                    name="first_name"
                    :label="__('First Name')"
                    :value="old('first_name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="first_name"
                    :placeholder="__('First name')"
                />

                <flux:input
                    name="middle_name"
                    :label="__('Middle Name')"
                    :value="old('middle_name')"
                    type="text"
                    required
                    autocomplete="middle_name"
                    :placeholder="__('Middle name')"
                />
                
                <div class="md:col-span-2">
                    <flux:input
                        name="last_name"
                        :label="__('Last Name')"
                        :value="old('last_name')"
                        type="text"
                        required
                        autocomplete="last_name"
                        :placeholder="__('Last name')"  
                    />
                </div>
            </div>

            <div class="space-y-4">
                <flux:input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
                />

                    <flux:input
                        name="password"
                        :label="__('Password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Password')"
                        viewable
                    />

                    <flux:input
                        name="password_confirmation"
                        :label="__('Confirm password')"
                        type="password"
                        required
                        autocomplete="new-password"
                        :placeholder="__('Confirm')"
                        viewable
                    />
            </div>

            <div class="flex items-center justify-end mt-2">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>