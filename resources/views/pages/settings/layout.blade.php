<x-slot:header>
    <flux:Navbar scrollable>
        <flux:navbar.item :href="route('profile.edit')" icon="user" wire:navigate>{{ __('Profile') }}</flux:navbar.item>
            <flux:navbar.item :href="route('user-password.edit')" icon="key" wire:navigate>{{ __('Password') }}</flux:navbar.item>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <flux:navbar.item :href="route('two-factor.show')" icon="lock-closed" wire:navigate>{{ __('Two-Factor Auth') }}</flux:navbar.item>
            @endif
            <flux:navbar.item :href="route('appearance.edit')" icon="paint-brush" wire:navigate>{{ __('Appearance') }}</flux:navbar.item>
    </flux:Navbar>
</x-slot:header>


<div class="flex items-start max-md:flex-col">


    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
