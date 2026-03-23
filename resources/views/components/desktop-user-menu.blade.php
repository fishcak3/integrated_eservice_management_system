<flux:dropdown position="bottom" align="start">
    
    {{-- 1. The Trigger: Replaced sidebar.profile with a standard button to match the bell's height --}}
    <flux:button variant="subtle" class="h-10 flex items-center gap-2 pl-1.5 pr-2" data-test="sidebar-menu-button">
        <flux:avatar
            circle
            :src="auth()->user()->profile_photo_url"
            :initials="auth()->user()->getInitialsAttribute()"
            class="w-7 h-7 text-xs"
        />
        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
            {{ auth()->user()->name }}
        </span>
        <flux:icon name="chevron-down" class="w-4 h-4 ml-0.5 text-zinc-500" variant="micro" />
    </flux:button>

    {{-- 2. The Content --}}
    <flux:menu>
        <div class="flex items-center gap-3 px-2 py-1.5 text-start text-sm">
            <flux:avatar
                circle
                :src="auth()->user()->profile_photo_url"
                :name="auth()->user()->name"
                :initials="auth()->user()->getInitialsAttribute()"
            />
            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
            </div>
        </div>
        
        <flux:menu.separator />
        
        <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>
            {{ __('Profile') }}
        </flux:menu.item>
        
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item
                as="button"
                type="submit"
                icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer"
                data-test="logout-button"
            >
                {{ __('Log Out') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>