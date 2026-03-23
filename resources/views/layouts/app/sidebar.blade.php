<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @fluxAppearance
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">

        <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header class="group flex items-center justify-between in-data-flux-sidebar-collapsed-desktop:justify-center">
                        
                {{-- 1. The Logo Link --}}
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 in-data-flux-sidebar-collapsed-desktop:group-hover:hidden">
                    @if($global_logo ?? false) 
                        <img src="{{ asset('storage/' . $global_logo) }}" 
                            alt="Logo" 
                            class="h-8 w-8 rounded-full object-cover border border-zinc-200 dark:border-zinc-700 shrink-0">
                    @else
                        <x-app-logo class="h-8 w-8 shrink-0" /> 
                    @endif

                    {{-- App name hides immediately when the sidebar collapses --}}
                    <span class="font-bold text-sm truncate dark:text-white in-data-flux-sidebar-collapsed-desktop:hidden">
                        {{ config('app.name') }}
                    </span>
                </a>

                {{-- 2. The Toggle Button --}}
                <flux:sidebar.collapse class="
                    in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2
                    in-data-flux-sidebar-collapsed-desktop:hidden 
                    in-data-flux-sidebar-collapsed-desktop:group-hover:flex
                "/>
                
            </flux:sidebar.header>
                        
            <flux:sidebar.nav>
                {{-- ADMIN --}}
                @if(Auth::user()->role === 'admin')
                        <flux:sidebar.item icon="home"                      
                            :href="route('admin.dashboard')"
                            :current="request()->routeIs('admin.dashboard')"
                            >
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                        <flux:sidebar.group expandable heading="User Management" class="grid" icon="users">
    
                            <flux:sidebar.item 
                                :href="route('users.index')"
                                :current="request()->routeIs('users.index')"
                                icon="user"
                                wire:navigate>
                                {{ __('All Users') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item 
                                :href="route('users.verifications')"
                                :current="request()->routeIs('users.verifications')"
                                icon="finger-print"
                                wire:navigate>
                                {{ __('Verifications') }}
                            </flux:sidebar.item>

                        </flux:sidebar.group>

                        <flux:sidebar.group expandable heading="Barangay Officials" class="grid" icon="briefcase">

                            <flux:sidebar.item
                                href="{{ route('officials.index') }}" 
                                :current="request()->routeIs('officials.index')"
                                icon="identification"
                                wire:navigate
                            >
                                Current Officials
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                href="{{ route('officials.former') }}" 
                                :current="request()->routeIs('officials.former')"
                                icon="archive-box-arrow-down"
                                wire:navigate
                            >
                                Former Officials
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                href="{{ route('positions.posIndex') }}" 
                                :current="request()->routeIs('positions.posIndex')"
                                icon="briefcase"
                                wire:navigate
                            >
                                Positions
                            </flux:sidebar.item>

                        </flux:sidebar.group>

                        <flux:sidebar.group expandable heading="Residents" class="grid" icon="users">

                            <flux:sidebar.item
                                href="{{ route('admin.residents.index') }}" 
                                :current="Route::is('admin.residents.index')"
                                icon="user-group"
                                wire:navigate
                            >
                                Resident List
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                href="{{ route('admin.residents.household') }}" 
                                :current="Route::is('admin.residents.household')"
                                icon="building-office-2"
                                wire:navigate
                            >
                                Households 
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                href="{{ route('admin.residents.requests') }}" 
                                :current="Route::is('admin.residents.requests')"
                                icon="clipboard-document-check"
                                wire:navigate
                            >
                                Pending Approvals
                            </flux:sidebar.item>

                        </flux:sidebar.group>

                        <flux:sidebar.group expandable heading="Requests" class="grid" icon="inbox">

                            <flux:sidebar.item
                                href="{{ route('admin.documents.index') }}" 
                                :current="request()->routeIs('admin.documents.*')"
                                :badge="$pendingDocsCount > 0 ? $pendingDocsCount : null"
                                icon="document-text"
                                wire:navigate
                            >
                                Documents
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                href="{{ route('admin.complaints.index') }}" 
                                :current="request()->routeIs('admin.complaints.*')"
                                :badge="$pendingComplaintsCount > 0 ? $pendingComplaintsCount : null"
                                icon="exclamation-triangle"
                                wire:navigate
                            >
                                Complaints
                            </flux:sidebar.item>

                        </flux:sidebar.group>

                        <flux:sidebar.group expandable heading="Announcements" class="grid" icon="megaphone">

                            <flux:sidebar.item
                                href="{{ route('admin.announcements.index') }}" 
                                :current="request()->routeIs('admin.announcements.index')"
                                icon="newspaper"
                                wire:navigate
                            >
                                All Announcements
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                href="{{ route('admin.announcements.archived') }}" 
                                :current="request()->routeIs('admin.announcements.archived')"
                                icon="archive-box"
                                wire:navigate
                            >
                                Archived
                            </flux:sidebar.item>


                        </flux:sidebar.group>

                        <flux:sidebar.group expandable heading="Settings" class="grid" icon="cog-6-tooth">
                            <flux:sidebar.item icon="building-library"
                                :href="route('settings.index')"
                                :current="request()->routeIs('settings.index')"
                                wire:navigate>
                                {{ __('Brgy. Profile') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="wrench-screwdriver"
                                :href="route('settings.backup')"
                                :current="request()->routeIs('settings.backup')"
                                wire:navigate>
                                {{ __('Maintenance') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="command-line"
                                :href="route('settings.logs')"
                                :current="request()->routeIs('settings.logs*')"
                                wire:navigate>
                                {{ __('Activity Logs') }}
                            </flux:sidebar.item>
                        
                            <flux:sidebar.item icon="square-3-stack-3d"
                                href="{{ route('settings.request', ['type' => 'document']) }}" 
                                :current="request()->routeIs('settings.request') && request('type', 'document') === 'document'"
                                wire:navigate
                            >
                                {{ __('Document Categories') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="exclamation-triangle"
                                href="{{ route('settings.request', ['type' => 'complaint']) }}" 
                                :current="request()->routeIs('settings.request') && request('type') === 'complaint'"
                                wire:navigate
                            >
                                {{ __('Complaint Case') }}
                            </flux:sidebar.item>

                            <flux:sidebar.item icon="chat-bubble-left-right"
                                :href="route('admin.chatbot.faqs')"
                                :current="request()->routeIs('admin.chatbot.faqs')"
                                wire:navigate>
                                {{ __('Chatbot FAQs') }}
                            </flux:sidebar.item>
                        </flux:sidebar.group>

                @endif

                {{-- OFFICIAL --}}
                @if(Auth::user()->role === 'official')

                        <flux:sidebar.item icon="home"
                            :href="route('official.dashboard')"
                            :current="request()->routeIs('official.dashboard')"
                            >
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item
                            href="{{ route('official.announcements.index') }}" 
                            :current="request()->routeIs('official.announcements.*')"
                            icon="megaphone"
                            wire:navigate
                        >
                            Announcements
                        </flux:sidebar.item>

                        <flux:sidebar.group expandable heading="Requests" class="grid" icon="inbox">

                            <flux:sidebar.item
                                href="{{ route('official.documents.index') }}" 
                                :current="request()->routeIs('official.documents.*')"
                                :badge="$pendingDocsCount > 0 ? $pendingDocsCount : null"
                                icon="document-text"
                                wire:navigate
                            >
                                Documents
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                href="{{ route('official.complaints.index') }}" 
                                :current="request()->routeIs('official.complaints.*')"
                                :badge="$pendingComplaintsCount > 0 ? $pendingComplaintsCount : null"
                                icon="exclamation-triangle"
                                wire:navigate
                            >
                                Complaints
                            </flux:sidebar.item>

                        </flux:sidebar.group>
                        
                        <flux:sidebar.group expandable heading="Residents" class="grid" icon="users">

                            <flux:sidebar.item
                                href="{{ route('official.residents.index') }}" 
                                :current="Route::is('official.residents.index')"
                                icon="user-group"
                                wire:navigate
                            >
                                Resident List
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                href="{{ route('official.residents.household') }}" 
                                :current="Route::is('official.residents.household')"
                                icon="building-office-2"
                                wire:navigate
                            >
                                Households 
                            </flux:sidebar.item>

                        </flux:sidebar.group>

                @endif

                {{-- RESIDENT --}}
                @if(Auth::user()->role === 'resident')

                        <flux:sidebar.item icon="home"
                            :href="route('resident.dashboard')"
                            :current="request()->routeIs('resident.dashboard')"
                            wire:navigate>
                            {{ __('Dashboard') }}
                        </flux:sidebar.item>

                        {{-- Add this inside the Resident section, right after their "Requests" group --}}

                        <flux:sidebar.item icon="megaphone"
                            :href="route('resident.announcements.index')"
                            :current="request()->routeIs('resident.announcements.*')"
                            wire:navigate>
                            {{ __('Announcement') }}
                        </flux:sidebar.item>

                        <flux:sidebar.group expandable heading="Requests" class="grid" icon="inbox">

                            <flux:sidebar.item
                                href="{{ route('resident.requests.index', ['type' => 'documents']) }}" 
                                :current="request()->routeIs('resident.requests.index') && request('type', 'documents') === 'documents'"
                                :badge="($pendingDocs ?? 0) > 0 ? $pendingDocs : null"
                                icon="document-text"
                                wire:navigate
                            >
                                Documents
                            </flux:sidebar.item>

                            <flux:sidebar.item
                                href="{{ route('resident.complaints.index', ['type' => 'complaints']) }}" 
                                :current="request()->routeIs('resident.complaints.index') && request('type') === 'complaints'"
                                :badge="($pendingComplaints ?? 0) > 0 ? $pendingComplaints : null"
                                icon="exclamation-triangle"
                                wire:navigate
                            >
                                Complaints
                            </flux:sidebar.item>

                        </flux:sidebar.group>

                @endif
            </flux:sidebar.nav>

            <flux:sidebar.spacer />
                        
        </flux:sidebar>

        <flux:header class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 gap-4">
            
            {{-- Mobile Toggle --}}
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <div class="flex-1 overflow-x-auto min-w-0">
                {{ $header ?? '' }}
            </div>

            <flux:button 
                x-data 
                x-on:click="$flux.appearance = $flux.dark ? 'light' : 'dark'" 
                variant="subtle" 
                square 
                class="!bg-transparent hover:!bg-zinc-100 dark:hover:!bg-zinc-800 text-zinc-500 dark:text-zinc-400 border-none" 
                aria-label="Toggle dark mode"
            >
                <flux:icon.sun x-show="$flux.dark" class="h-5 w-5" />
                <flux:icon.moon x-show="! $flux.dark" class="h-5 w-5" />
            </flux:button>

            <livewire:notification-bell />

            {{-- Safely wrapped in a hidden container for mobile --}}
            <div class="hidden lg:block">
                <x-desktop-user-menu 
                    :name="auth()->user()->resident?->full_name ?? auth()->user()->name" 
                    :avatar="auth()->user()->profile_photo_url"
                    :initials="auth()->user()->getInitialsAttribute()"
                />
            </div>

            {{-- Mobile User Menu --}}
            <div class="lg:hidden flex shrink-0">
                <flux:dropdown position="top" align="end">
                    <flux:profile 
                        :avatar="auth()->user()->profile_photo_url"
                        :initials="auth()->user()->getInitialsAttribute()" 
                        icon-trailing="chevron-down" 
                    />
                    
                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar 
                                        :src="auth()->user()->profile_photo_url"
                                        :name="auth()->user()->name" 
                                        :initials="auth()->user()->getInitialsAttribute()" 
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
        <flux:main>
            <div class="flex-1">
                {{ $slot }}
            </div>

            {{-- mt-auto pushes the footer to the bottom of the flex container --}}
            <div class="mt-auto pt-8">
                @include('partials.footerDashboard')
            </div>
        </flux:main>

        @if(!auth()->check() || auth()->user()->role === 'resident')
            <livewire:resident.floating-chat />
        @elseif(auth()->check() && auth()->user()->role === 'admin')
            <livewire:admin.chat-manager />
        @endif

        {{-- Free Custom Toast Notification --}}
        <div x-data="{ show: false, message: '', title: '', type: 'success' }"
             x-on:notify.window="
                title = $event.detail.title;
                message = $event.detail.message;
                type = $event.detail.type || 'success';
                show = true;
                setTimeout(() => show = false, 3000);
             "
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:translate-x-4"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             class="fixed bottom-4 right-4 z-50 flex w-full max-w-sm flex-col gap-2 rounded-xl border border-zinc-200 bg-white p-4 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
        >
            <div class="flex items-start gap-3">
                {{-- Success Icon --}}
                <template x-if="type === 'success'">
                    <flux:icon.check-circle class="h-6 w-6 text-green-500" />
                </template>
                
                {{-- Danger Icon --}}
                <template x-if="type === 'danger'">
                    <flux:icon.x-circle class="h-6 w-6 text-red-500" />
                </template>

                <div>
                    <h3 x-text="title" class="text-sm font-semibold text-zinc-900 dark:text-white"></h3>
                    <p x-text="message" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"></p>
                </div>
                
                <button @click="show = false" class="ml-auto text-zinc-400 hover:text-zinc-500">
                    <flux:icon.x-mark class="h-5 w-5" />
                </button>
            </div>
        </div>

        @fluxScripts
    </body>
</html>