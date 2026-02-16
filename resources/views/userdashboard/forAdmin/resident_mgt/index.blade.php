<x-layouts::app :title="__('Resident Management')">

    <x-slot:header>
        <flux:navbar scrollable>
            {{-- We check the Route Name or Request Type for the 'active' state --}}
            <flux:navbar.item 
                href="{{ route('residents.index', ['type' => 'Residents']) }}" 
                :current="Route::is('residents.index')"
                icon="user-group"
                wire:navigate
            >
                All Residents
            </flux:navbar.item>

            <flux:navbar.item 
                href="{{ route('residents.household', ['type' => 'household']) }}" 
                :current="Route::is('residents.household')"
                icon="building-office-2"
                wire:navigate
            >
                Households 
            </flux:navbar.item>
        </flux:navbar>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">

        {{-- Shared Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">Resident Management</flux:heading>
                <flux:subheading>Manage residents and their household information.</flux:subheading>
            </div>
            
            {{-- Show 'New Resident' button only if we are NOT in household view --}}
            @if(!isset($households))
                <flux:button href="{{ route('residents.create') }}" variant="primary" icon="plus">
                    New Resident
                </flux:button>
            @endif
        </div>

        <flux:card class="flex-1 p-0 overflow-hidden">

            {{-- Unified Search Toolbar --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <form method="GET" action="{{ url()->current() }}">
                    
                    {{-- Preserve type param if it exists --}}
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif

                    <flux:input 
                        name="search" 
                        value="{{ request('search') }}" 
                        icon="magnifying-glass" 
                        placeholder="{{ isset($households) ? 'Search Household ID...' : 'Search Residents...' }}" 
                        class="max-w-sm" 
                    />
                </form>
            </div>
            
            {{-- CONDITIONAL VIEW LOGIC: Check if data exists, not just the URL param --}}
            @if(isset($households))
            
                {{-- === HOUSEHOLD TABLE === --}}
                <flux:table :paginate="$households">
                    <flux:table.columns>
                        <flux:table.column>Household ID</flux:table.column>
                        <flux:table.column>Location / Address</flux:table.column>
                        <flux:table.column align="center">Family Members</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($households as $household)
                            <flux:table.row :key="$household->household_id">
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
                                            <flux:icon.hashtag variant="mini" />
                                        </div>
                                        <span class="font-medium text-zinc-900 dark:text-white">
                                            #{{ $household->household_id }}
                                        </span>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $household->purok ? 'Purok ' . $household->purok : ($household->street ?: 'N/A') }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        {{ $household->barangay }}
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell class="text-center">
                                    <flux:badge color="blue" size="sm">
                                        {{ $household->member_count }} Members
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <flux:button 
                                        size="xs" 
                                        href="{{ route('residents.household.show', $household->household_id) }}"
                                        icon-trailing="chevron-right"
                                    >
                                        View Members
                                    </flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center text-gray-500 py-6">
                                    <div class="flex flex-col items-center justify-center">
                                        <flux:icon.home class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                        <p>No households found.</p>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

            @else

                {{-- === RESIDENT TABLE (Default) === --}}
                <flux:table :paginate="$residents">
                    <flux:table.columns>
                        <flux:table.column>Name</flux:table.column>
                        <flux:table.column>Unit / Address</flux:table.column>
                        <flux:table.column>Contact</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($residents as $resident)
                            <flux:table.row :key="$resident->id">
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar src="{{ $resident->avatar_url ?? null }}" initials="{{ substr($resident->fname, 0, 1) }}" />
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">
                                                {{ $resident->fname }} {{ $resident->lname }}
                                            </div>
                                            <div class="text-xs text-gray-500">ID: #{{ $resident->id }}</div>
                                        </div>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $resident->street ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $resident->barangay ?? '' }}</div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    {{ $resident->phone_number ?? 'N/A' }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if($resident->status === 'is_active')
                                        <flux:badge color="green" size="sm" inset="top bottom">Active</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm" inset="top bottom">{{ ucfirst($resident->status) }}</flux:badge>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <flux:dropdown>
                                        <flux:button icon="ellipsis-horizontal" size="xs" variant="ghost" />
                                        <flux:menu>
                                            <flux:menu.item href="{{ route('residents.show', $resident->id) }}" icon="eye">
                                                View Details
                                            </flux:menu.item>

                                            <flux:menu.item href="{{ route('residents.edit', $resident->id) }}" icon="pencil-square">
                                                Edit    
                                            </flux:menu.item>

                                            <flux:menu.separator />

                                            <form action="{{ route('residents.destroy', $resident->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this resident?');">
                                                @csrf
                                                @method('DELETE')
                                                <flux:menu.item type="submit" icon="trash" variant="danger">Delete</flux:menu.item>
                                            </form>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center text-gray-500 py-6">
                                    No residents found.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

            @endif

        </flux:card>
    </div>
</x-layouts::app>