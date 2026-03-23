<x-layouts::app>

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item>Settings</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('settings.backup') }}">Backup & Maintenance</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    <div class="flex w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Header Text --}}
        <div class="mb-2 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div class="space-y-2">
                <flux:heading size="xl" level="1">Backup & Maintenance</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Manage system backups, clear application cache, and toggle maintenance mode.
                </flux:text>
            </div>
        </div>

        {{-- SUCCESS / ERROR MESSAGES --}}
        @if(session('status'))
            <div class="p-3 bg-green-100 text-green-700 rounded-lg border border-green-200 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 bg-red-100 text-red-700 rounded-lg border border-red-200 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{-- Main Layout Container --}}
        <div class="space-y-10">

            {{-- SECTION 1: System Backups --}}
            <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
                <div class="px-4 sm:px-0">
                    <flux:heading size="lg">System Backups</flux:heading>
                    <flux:text variant="subtle" class="mt-1">
                        Generate and download database backups to keep barangay records safe from data loss.
                    </flux:text>
                </div>

                <div class="md:col-span-2">
                    <flux:card>
                        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-700">
                            <div>
                                <flux:heading size="md">Backup Files</flux:heading>
                            </div>
                            
                            {{-- Form to Generate Backup --}}
                            <form action="{{ route('settings.backup.generate') }}" method="POST" class="cursor-pointer">
                                @csrf
                                <flux:button type="submit" variant="primary" icon="arrow-down-tray" size="sm">
                                    Generate New Backup
                                </flux:button>
                            </form>
                        </div>
                        
                        <div class="p-6">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column>File Name</flux:table.column>
                                    <flux:table.column>Size</flux:table.column>
                                    <flux:table.column>Date Created</flux:table.column>
                                    <flux:table.column>Actions</flux:table.column>
                                </flux:table.columns>
                                
                                <flux:table.rows>
                                    {{-- Loop through the backups dynamically --}}
                                    @forelse ($backups as $backup)
                                        <flux:table.row>
                                            <flux:table.cell class="font-medium">{{ $backup['name'] }}</flux:table.cell>
                                            <flux:table.cell>{{ $backup['size'] }}</flux:table.cell>
                                            <flux:table.cell>{{ $backup['date'] }}</flux:table.cell>
                                            <flux:table.cell>
                                                <div class="flex items-center gap-2">
                                                    {{-- Download Button --}}
                                                    <flux:button href="{{ route('settings.backup.download', $backup['name']) }}" size="sm" variant="ghost" icon="arrow-down-tray" class="text-green-600">Download</flux:button>
                                                    
                                                    {{-- Delete Button Form --}}
                                                    <form class="cursor-pointer" action="{{ route('settings.backup.delete', $backup['name']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this backup file?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <flux:button type="submit" size="sm" variant="ghost" icon="trash" class="text-red-600">Delete</flux:button>
                                                    </form>
                                                </div>
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @empty
                                        <flux:table.row>
                                            <flux:table.cell colspan="4" class="text-center text-zinc-500 py-6">
                                                No backups found. Click "Generate New Backup" to create one.
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforelse
                                </flux:table.rows>
                            </flux:table>
                        </div>
                    </flux:card>
                </div>
            </div>

            <flux:separator variant="subtle" />

            {{-- SECTION 2: Cache & Optimization --}}
            <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                <div class="px-4 sm:px-0">
                    <flux:heading size="lg">Cache & Optimization</flux:heading>
                    <flux:text variant="subtle" class="mt-1">
                        Clear the system cache if the application is not displaying your latest uploaded logos or text updates.
                    </flux:text>
                </div>

                <div class="md:col-span-2">
                    <flux:card class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between border-b border-zinc-200 dark:border-zinc-700 pb-4">
                                <div>
                                    <p class="font-medium text-sm text-zinc-900 dark:text-white">Application Cache</p>
                                    <p class="text-xs text-zinc-500">Clears general system cache.</p>
                                </div>
                                <form class="cursor-pointer" action="{{ route('settings.cache.clear') }}" method="POST">
                                    @csrf
                                    <flux:button type="submit" size="sm" variant="outline">Clear Cache</flux:button>
                                </form>
                            </div>
                            
                            <div class="flex items-center justify-between pt-2">
                                <div>
                                    <p class="font-medium text-sm text-zinc-900 dark:text-white">View Cache</p>
                                    <p class="text-xs text-zinc-500">Fixes broken layouts or missing components.</p>
                                </div>
                                <form class="cursor-pointer" action="{{ route('settings.views.clear') }}" method="POST">
                                    @csrf
                                    <flux:button type="submit" size="sm" variant="outline">Clear Views</flux:button>
                                </form>
                            </div>
                        </div>
                    </flux:card>
                </div>
            </div>

            <flux:separator variant="subtle" />

            {{-- SECTION 3: Maintenance Mode --}}
            <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                <div class="px-4 sm:px-0">
                    <flux:heading size="lg" class="text-amber-600 dark:text-amber-500">Maintenance Mode</flux:heading>
                    <flux:text variant="subtle" class="mt-1">
                        Put the system in maintenance mode. Residents will see a "Be right back" screen instead of the normal interface.
                    </flux:text>
                </div>

                <div class="md:col-span-2">
                    <flux:card class="p-6">
                        <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-400 p-4 rounded-lg mb-6 text-sm">
                            <strong>Warning:</strong> Enabling this will prevent residents from logging in or submitting requests until disabled. Admin access remains available.
                        </div>
                        
                        <form action="{{ route('settings.maintenance.toggle') }}" method="POST">
                            @csrf
                            <div class="flex items-center justify-between">
                                <flux:text class="font-medium">
                                    System Status: 
                                    @if(app()->isDownForMaintenance())
                                        <span class="text-red-600 font-bold ml-1">OFFLINE</span>
                                    @else
                                        <span class="text-green-600 font-bold ml-1">ONLINE</span>
                                    @endif
                                </flux:text>
                                
                                {{-- Button automatically adapts based on system state --}}
                                @if(app()->isDownForMaintenance())
                                    <flux:button type="submit" variant="primary" class="bg-green-600 hover:bg-green-700">Bring Online</flux:button>
                                @else
                                    {{-- Using your custom Confirmation Modal --}}
                                    <x-confirm-modal 
                                        name="offline-confirm-modal" 
                                        title="Enable Maintenance Mode?" 
                                        confirmText="Take Offline" 
                                        confirmVariant="danger"
                                    >
                                        {{-- The button that triggers the modal --}}
                                        <x-slot name="trigger">
                                            <flux:button variant="danger">Take Offline</flux:button>
                                        </x-slot>

                                        {{-- The main slot text inside the modal --}}
                                        Are you sure you want to put the system offline? Residents will not be able to log in or access barangay services until you bring the system back online.
                                    </x-confirm-modal>
                                @endif
                            </div>
                        </form>
                    </flux:card>
                </div>
            </div>

        </div>
    </div>
</x-layouts::app>