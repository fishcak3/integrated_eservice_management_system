<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @fluxAppearance
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">

        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>

                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 px-2">
                    @if($global_logo ?? false) 
                        <img src="{{ asset('storage/' . $global_logo) }}" 
                            alt="Logo" 
                            class="h-8 w-8 rounded-full object-cover border border-zinc-200 dark:border-zinc-700 shrink-0">
                    @else
                        <x-app-logo class="h-8 w-8 shrink-0" /> 
                    @endif

                    <span class="font-bold text-sm truncate dark:text-white in-data-flux-sidebar-collapsed-desktop:hidden">
                        {{ $global_brgy_name ?? 'Barangay Portal' }}
                    </span>
                </a>

                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2"/>
            </flux:sidebar.header>
                        
            <flux:sidebar.nav>
                {{-- ADMIN --}}
                @if(Auth::user()->role === 'admin')
                        <flux:sidebar.item icon="home"                      
                            :href="route('admin.dashboard')"
                            :current="request()->routeIs('admin.dashboard')"
                            wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="user"
                            :href="route('users.index')"
                            :current="request()->routeIs('users.*')"
                            wire:navigate>
                            {{ __('Account Holder') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="briefcase"
                            :href="route('officials.index')"
                            {{-- Use an array to check for multiple route patterns --}}
                            :current="request()->routeIs(['officials.*', 'positions.*'])"
                            wire:navigate>
                            {{ __('Barangay Officials') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="home-modern"
                            :href="route('residents.index')"
                            :current="request()->routeIs('residents.*')"
                            wire:navigate>
                            {{ __('Residents') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="inbox"
                            :href="route('admin.requests.index')"
                            :current="request()->routeIs('admin.requests.*')"
                            wire:navigate>
                            {{ __('Requests') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="megaphone"
                            :href="route('announcements.index')"
                            :current="request()->routeIs('announcements.*')"
                            wire:navigate>
                            {{ __('Announcements') }}
                        </flux:sidebar.item>

                        <flux:sidebar.group expandable heading="Settings" class="grid">
                            <flux:sidebar.item icon="cog-6-tooth"
                                :href="route('settings.index')"
                                :current="request()->routeIs('settings.*')"
                                wire:navigate>
                                {{ __('Brgy. Profile Settings') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                @endif

                {{-- OFFICIAL --}}
                @if(Auth::user()->role === 'official')

                        <flux:sidebar.item icon="home"
                            :href="route('dashboard')"
                            :current="request()->routeIs('dashboard')"
                            wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                @endif

                {{-- RESIDENT --}}
                @if(Auth::user()->role === 'resident')

                        <flux:sidebar.item icon="home"
                            :href="route('resident.dashboard')"
                            :current="request()->routeIs('resident.dashboard')"
                            wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="inbox"
                            :href="route('resident.requests.index')"
                            :current="request()->routeIs('requests.index')"
                            wire:navigate>
                            {{ __('Requests') }}
                        </flux:sidebar.item>    

                @endif
            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            <x-desktop-user-menu 
                class="hidden lg:block" 
                :name="auth()->user()->resident?->full_name ?? auth()->user()->name" 
                :avatar="auth()->user()->profile_photo_url"
                :initials="auth()->user()->initials()"
            />
        </flux:sidebar>

        <flux:header class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 gap-4">
            
            {{-- Mobile Toggle --}}
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <div class="flex-1 overflow-x-auto min-w-0">
                {{ $header ?? '' }}
            </div>

            {{-- Dark Mode Toggle --}}
            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />

            {{-- Mobile User Menu --}}
            <div class="lg:hidden flex shrink-0">
                <flux:dropdown position="top" align="end">
                    <flux:profile 
                        :avatar="auth()->user()->profile_photo_url"
                        :initials="auth()->user()->initials()" 
                        icon-trailing="chevron-down" 
                    />
                    
                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar 
                                        :src="auth()->user()->profile_photo_url"
                                        :name="auth()->user()->name" 
                                        :initials="auth()->user()->initials()" 
                                    />
                                    
                                    
                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ Auth::user()->resident?->formatted_name ?? Auth::user()->email }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('profile.edit')" icon="user" wire:navigate>{{ __('Profile') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </flux:header>

        {{-- Main Content Slot --}}
        <flux:main class="flex h-[calc(100dvh-4rem)] w-full flex-1 flex-col gap-6 p-6 lg:p-8">
            {{ $slot }}

            @include('partials.footerDashboard')
        </flux:main>

        @fluxScripts
    </body>
</html>