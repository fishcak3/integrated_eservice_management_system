<x-layouts::app title="System Settings">

    <x-slot:header>
        <flux:navbar scrollable>
            <flux:navbar.item 
                :href="route('settings.index', ['tab' => 'general'])"
                :current="request()->query('tab', 'general') === 'general'"
                icon="building-office"
                wire:navigate
            >
                Barangay Profile
            </flux:navbar.item>

            <flux:navbar.item 
                :href="route('settings.index', ['tab' => 'backup'])"
                :current="request()->query('tab') === 'backup'"
                icon="circle-stack"
                wire:navigate
            >
                Backup & Maintenance
            </flux:navbar.item>
        </flux:navbar>
    </x-slot:header>

    {{-- 
        Initialize activeTab from the URL parameter. 
        If ?tab=backup is in the URL, this variable starts as 'backup'.
    --}}
    <div x-data="{ activeTab: '{{ request()->query('tab', 'general') }}' }" class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Header Text --}}
        <div>
            <flux:heading size="lg">System Settings</flux:heading>
            <flux:subheading>Manage barangay profile, configurations, and maintenance.</flux:subheading>
        </div>

        {{-- 1. GENERAL SETTINGS --}}
        <div x-show="activeTab === 'general'" class="space-y-6">
            <flux:card>
                <flux:heading size="md" class="mb-6">Barangay Information</flux:heading>
                
                {{-- Display Success Message --}}
                @if (session('status'))
                    <div class="mb-6 p-3 bg-green-100 text-green-700 rounded-lg border border-green-200">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    {{-- Top Section: Logo + Main Info --}}
                    <div class="flex flex-col md:flex-row gap-8 items-start mb-6">
                        
                        {{-- Left Column: Logo Preview & Upload --}}
                        <div class="flex flex-col items-center gap-3 w-full md:w-auto">
                            <div class="relative group">
                                <div class="h-32 w-32 rounded-full overflow-hidden border-4 border-gray-100 shadow-sm bg-gray-50">
                                    @if($logo)
                                        <img src="{{ asset('storage/' . $logo) }}" 
                                             alt="Barangay Logo" 
                                             class="h-full w-full object-cover">
                                    @else
                                        {{-- Placeholder if no logo exists --}}
                                        <div class="flex items-center justify-center h-full w-full text-gray-400">
                                            <flux:icon name="photo" class="h-12 w-12" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                            {{-- Small file input below the image --}}
                            <div class="w-full max-w-[200px]">
                                <flux:input type="file" name="logo" size="sm" label="Change Logo" />
                            </div>
                        </div>

                        {{-- Right Column: Basic Details --}}
                        <div class="flex-1 w-full space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <flux:input name="barangay_name" label="Barangay Name" value="{{ old('barangay_name', $barangay_name) }}" />
                                <flux:input name="municipality" label="Municipality/City" value="{{ old('municipality', $municipality) }}" />
                            </div>
                            
                            <flux:input name="address" label="Barangay Hall Address" value="{{ old('address', $address) }}" />
                        </div>
                    </div>

                    <flux:separator class="my-6" />

                    {{-- Bottom Section: Contact Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <flux:input name="contact_phone" label="Contact Phone" value="{{ old('contact_phone', $contact_phone) }}" icon="phone" />
                        <flux:input name="office_email" label="Office Email" value="{{ old('office_email', $office_email) }}" icon="envelope" />
                    </div>

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary">Save Changes</flux:button>
                    </div>
                </form>
            </flux:card>

            {{-- READ-ONLY SIGNATORIES CARD (Keep this as is) --}}
            <flux:card>
                <div class="mb-4">
                    <flux:heading size="md">Current Signatories</flux:heading>
                    <flux:subheading>These are the currently active officials assigned in the system.</flux:subheading>
                </div>
                
                <div class="space-y-4">
                    <flux:input label="Punong Barangay (Captain)" value="{{ $captain_name }}" readonly icon="user" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input label="Barangay Secretary" value="{{ $secretary_name }}" readonly icon="user" />
                        <flux:input label="Barangay Treasurer" value="{{ $treasurer_name }}" readonly icon="user" />
                    </div>
                </div>
            </flux:card>
        </div>

        {{-- 2. BACKUP --}}
        <div x-show="activeTab === 'backup'" x-cloak class="space-y-6">
            <flux:card>
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-red-100 text-red-600 rounded-lg dark:bg-red-900/30 dark:text-red-400">
                        <flux:icon name="circle-stack" variant="mini" />
                    </div>
                    <div>
                        <flux:heading size="md">Database Backup</flux:heading>
                        <div class="text-sm text-zinc-500 mt-1 mb-4">
                            Download a copy of all resident data, requests, and logs. It is recommended to do this weekly.
                        </div>
                        <flux:button icon="arrow-down-tray">Download SQL Backup</flux:button>
                    </div>
                </div>
            </flux:card>
        </div>

    </div>
</x-layouts::app>