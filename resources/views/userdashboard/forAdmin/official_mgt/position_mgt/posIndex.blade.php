<x-layouts::app :title="__('Position Management')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">

        {{-- Header Section --}}
        <div>
            <flux:heading size="lg">Position Management</flux:heading>
            <flux:subheading>Define official positions, capacities, and roles.</flux:subheading>
        </div>

        {{-- Main Content Card --}}
        <flux:card class="flex-1 p-0 overflow-hidden">
            
            {{-- Toolbar --}}
            <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center border-b border-zinc-200 dark:border-zinc-700">
                <div class="relative w-full sm:w-80">
                    <form method="GET">
                        <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search positions..." /> 
                    </form>
                </div>
                <flux:button href="{{ route('positions.posCreate') }}" variant="primary" icon="plus">Add Position</flux:button>
            </div>

            {{-- Table --}}
            <flux:table :paginate="$positions">
                <flux:table.columns>
                    <flux:table.column>Position Title</flux:table.column>
                    <flux:table.column>Max Members</flux:table.column> {{-- Replaced 'Rank' with this --}}
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($positions as $position)
                        <flux:table.row :key="$position->id">
                            
                            {{-- Title --}}
                            <flux:table.cell class="font-medium text-gray-900 dark:text-white">
                                {{ $position->title }}
                            </flux:table.cell>

                            {{-- Max Members (From Schema) --}}
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $position->max_members }} allowed</flux:badge>
                            </flux:table.cell>

                            {{-- Description --}}
                            <flux:table.cell class="text-gray-500 truncate max-w-xs">
                                {{ $position->description ?? '-' }}
                            </flux:table.cell>

                            {{-- Status (Fixed Logic) --}}
                            <flux:table.cell>
                                @if($position->is_active) 
                                    <flux:badge color="green" size="sm" inset="top bottom">Active</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm" inset="top bottom">Inactive</flux:badge>
                                @endif
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="xs" variant="ghost" />
                                    
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('positions.posEdit', $position->id) }}" icon="pencil-square">
                                            Edit
                                        </flux:menu.item>

                                        <flux:menu.separator />

                                        <form action="{{ route('positions.destroy', $position->id) }}" method="POST" onsubmit="return confirm('Delete this position?');">
                                            @csrf
                                            @method('DELETE')
                                            <flux:menu.item type="submit" icon="trash" variant="danger">
                                                Delete
                                            </flux:menu.item>
                                        </form>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-gray-500 py-6">
                                No positions defined yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</x-layouts::app>