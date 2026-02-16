<x-layouts::app :title="request()->routeIs('positions.*') ? 'Position Management' : 'Official Management'">

    <x-slot:header>
        <flux:navbar scrollable>
            <flux:navbar.item 
                href="{{ route('officials.index') }}" 
                :current="request()->routeIs('officials.index')"
                icon="user-group"
                wire:navigate
            >
                Current Officials
            </flux:navbar.item>

            <flux:navbar.item 
                href="{{ route('officials.former') }}" 
                :current="request()->routeIs('officials.former')"
                icon="archive-box-arrow-down"
                wire:navigate
            >
                Former Officials
            </flux:navbar.item>

            <flux:navbar.item 
                href="{{ route('positions.posIndex') }}" 
                :current="request()->routeIs('positions.posIndex')"
                icon="briefcase"
                wire:navigate
            >
                Positions
            </flux:navbar.item>
        </flux:navbar>
    </x-slot:header>

    {{-- MAIN CONTENT WRAPPER --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">

        {{-- Shared Header (Outside Card) --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                @if(request()->routeIs('positions.posIndex'))
                    <flux:heading size="lg">Position Management</flux:heading>
                    <flux:subheading>Define official positions, capacities, and roles.</flux:subheading>
                @elseif(request()->routeIs('officials.former'))
                    <flux:heading size="lg">Former Officials</flux:heading>
                    <flux:subheading>History of past barangay officials and their terms.</flux:subheading>
                @else
                    <flux:heading size="lg">Barangay Officials</flux:heading>
                    <flux:subheading>Manage current officials, term history, and positions.</flux:subheading>
                @endif
            </div>

            {{-- Dynamic Action Button --}}
            @if(request()->routeIs('positions.posIndex'))
                <flux:button href="{{ route('positions.posCreate') }}" variant="primary" icon="plus">Add Position</flux:button>
            @elseif(request()->routeIs('officials.index'))
                <flux:button href="{{ route('officials.create') }}" variant="primary" icon="plus">Add Official</flux:button>
            @endif
        </div>

        {{-- Content Card --}}
        <flux:card class="flex-1 p-0 overflow-hidden">

            {{-- Search Toolbar --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <div class="relative w-full sm:w-80">
                    <form method="GET">
                        <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search..." class="max-w-sm"/> 
                    </form>
                </div>
            </div>

            {{-- -------------------------------------------------------- --}}
            {{-- CASE 1: POSITIONS TABLE --}}
            {{-- -------------------------------------------------------- --}}
            @if(request()->routeIs('positions.posIndex'))
                <flux:table :paginate="$positions">
                    <flux:table.columns>
                        <flux:table.column>Position Title</flux:table.column>
                        <flux:table.column>Max Members</flux:table.column>
                        <flux:table.column>Description</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse($positions as $position)
                            <flux:table.row :key="$position->id">
                                <flux:table.cell class="font-medium text-gray-900 dark:text-white">
                                    {{ $position->title }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $position->max_members }} allowed</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="text-gray-500 truncate max-w-xs">
                                    {{ $position->description ?? '-' }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($position->is_active) 
                                        <flux:badge color="green" size="sm" inset="top bottom">Active</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm" inset="top bottom">Inactive</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:dropdown>
                                        <flux:button icon="ellipsis-horizontal" size="xs" variant="ghost" />
                                        <flux:menu>
                                            <flux:menu.item href="{{ route('positions.posEdit', $position->id) }}" icon="pencil-square">Edit</flux:menu.item>
                                            <flux:menu.separator />
                                            <form action="{{ route('positions.destroy', $position->id) }}" method="POST" onsubmit="return confirm('Delete this position?');">
                                                @csrf @method('DELETE')
                                                <flux:menu.item type="submit" icon="trash" variant="danger">Delete</flux:menu.item>
                                            </form>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center text-gray-500 py-6">No positions defined yet.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

            {{-- -------------------------------------------------------- --}}
            {{-- CASE 2: FORMER OFFICIALS TABLE --}}
            {{-- -------------------------------------------------------- --}}
            @elseif(request()->routeIs('officials.former'))
                <flux:table :paginate="$officials">
                    <flux:table.columns>
                        <flux:table.column>Name</flux:table.column>
                        <flux:table.column>Former Position</flux:table.column>
                        <flux:table.column>Term Duration</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse($officials as $official)
                            <flux:table.row :key="$official->id">
                                <flux:table.cell class="flex items-center gap-3">
                                    <flux:avatar src="{{ $official->resident->avatar_url ?? '' }}" initials="{{ substr($official->resident->fname, 0, 1) }}" />
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $official->resident->full_name }}</div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $official->position->title }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($official->date_start)->format('M Y') }} - 
                                        {{ $official->date_end ? \Carbon\Carbon::parse($official->date_end)->format('M Y') : 'Unknown' }}
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button href="{{ route('officials.show', $official->id) }}" size="xs" variant="ghost" icon="eye">View</flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center text-gray-500 py-6">No former officials records found.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

            {{-- -------------------------------------------------------- --}}
            {{-- CASE 3: CURRENT OFFICIALS TABLE (Default) --}}
            {{-- -------------------------------------------------------- --}}
            @else
                <flux:table :paginate="$officials">
                    <flux:table.columns>
                        <flux:table.column>Official Name</flux:table.column>
                        <flux:table.column>Position</flux:table.column>
                        <flux:table.column>Term</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse($officials as $official)
                            <flux:table.row :key="$official->id">
                                <flux:table.cell class="flex items-center gap-3">
                                    <flux:avatar src="{{ $official->resident->avatar_url ?? '' }}" initials="{{ substr($official->resident->fname, 0, 1) }}" />
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $official->resident->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $official->user?->email ?? 'No email linked' }}</div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $official->position->title }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($official->date_start)->format('M d, Y') }} - 
                                        {{ $official->date_end ? \Carbon\Carbon::parse($official->date_end)->format('M d, Y') : 'Present' }}
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($official->is_active)
                                        <flux:badge color="green" size="sm" inset="top bottom">Active</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm" inset="top bottom">Inactive</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:dropdown>
                                        <flux:button icon="ellipsis-horizontal" size="xs" variant="ghost" />
                                        <flux:menu>
                                            <flux:menu.item href="{{ route('officials.show', $official->id) }}" icon="eye">Show</flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger">Remove</flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center text-gray-500 py-6">No officials found.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            @endif

        </flux:card>
    </div>
</x-layouts::app>