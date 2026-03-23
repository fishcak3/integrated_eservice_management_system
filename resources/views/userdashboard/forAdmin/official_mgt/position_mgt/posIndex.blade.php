<x-layouts::app title="Position Management">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('positions.posIndex') }}">Positions</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                title: true,
                members: true,
                description: true,
                status: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Position Management</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">Manage available positions in the barangay.</flux:subheading>
            </div>
            
            <flux:button href="{{ route('positions.posCreate') }}" variant="primary" icon="plus">Add Position</flux:button>
        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ url()->current() }}" x-on:change="$el.submit()" class="flex flex-col gap-4">

            {{-- Toolbar: Search & Actions --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Search Input + Clear --}}
                <div class="flex items-center gap-2 flex-1 max-w-sm">
                    
                    <flux:input 
                        name="search" 
                        value="{{ request('search') }}" 
                        icon="magnifying-glass" 
                        placeholder="Search positions..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                    />

                    @if(request()->query())
                        <flux:button href="{{ route('positions.posIndex') }}" size="sm" variant="subtle" icon="x-mark">
                            Clear
                        </flux:button>
                    @endif

                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.title" label="Position Title" x-on:change.stop />
                            <flux:checkbox x-model="cols.members" label="Max Members" x-on:change.stop />
                            <flux:checkbox x-model="cols.description" label="Description" x-on:change.stop />
                            <flux:checkbox x-model="cols.status" label="Status" x-on:change.stop />
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </form>

        {{-- Table Container --}}
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <flux:table class="whitespace-nowrap bg-transparent">
                <flux:table.columns>
                    <flux:table.column x-show="cols.title">Position Title</flux:table.column>
                    <flux:table.column x-show="cols.members">Max Members</flux:table.column>
                    <flux:table.column x-show="cols.description">Description</flux:table.column>
                    <flux:table.column x-show="cols.status">Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @isset($positions)
                        @forelse($positions as $position)
                            <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                                
                                <flux:table.cell x-show="cols.title">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $position->title }}
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell x-show="cols.members">
                                    <flux:badge size="sm" color="zinc">{{ $position->max_members }} allowed</flux:badge>
                                </flux:table.cell>

                                <flux:table.cell x-show="cols.description">
                                    <div class="text-zinc-500 truncate max-w-xs">
                                        {{ $position->description ?? '-' }}
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell x-show="cols.status">
                                    @if($position->is_active) 
                                        <flux:badge color="green" size="sm" inset="top bottom">Active</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm" inset="top bottom">Inactive</flux:badge>
                                    @endif
                                </flux:table.cell>
                                
                                {{-- Actions --}}
                                <flux:table.cell align="end">
                                    <flux:dropdown>
                                        <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />
                                        
                                        <flux:menu>
                                            <flux:menu.item href="{{ route('positions.posEdit', $position->id) }}" icon="pencil-square">
                                                Edit
                                            </flux:menu.item>
                                            
                                            <flux:menu.separator />
                                            
                                            <flux:modal.trigger name="delete-position-{{ $position->id }}">
                                                <flux:menu.item icon="trash" variant="danger">
                                                    Delete
                                                </flux:menu.item>
                                            </flux:modal.trigger>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center text-zinc-500 py-8">
                                    <div class="flex flex-col items-center justify-center">
                                        <flux:icon.briefcase class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                        @if(request()->hasAny(['search', 'statuses']))
                                            <p>No positions found matching your filters.</p>
                                        @else
                                            <p>No positions defined yet.</p>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    @endisset
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            @isset($positions)
                @if($positions->hasPages())
                    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                        {{ $positions->links() }}
                    </div>
                @endif
            @endisset
        </div>
    </div>

    {{-- Delete Modals --}}
    @isset($positions)
        @foreach ($positions as $position)
            <x-delete-modal 
                name="delete-position-{{ $position->id }}" 
                action="{{ route('positions.destroy', $position->id) }}"
            >
                This will permanently delete the position <strong>{{ $position->title }}</strong>. 
                This action cannot be undone.
            </x-delete-modal>
        @endforeach
    @endisset

</x-layouts::app>