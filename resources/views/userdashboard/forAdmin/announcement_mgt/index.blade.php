<x-layouts::app :title="__('Announcements')">

    <x-slot:header>
        <flux:navbar scrollable>
            <flux:navbar.item 
                href="{{ route('announcements.index') }}" 
                :current="request()->routeIs('announcements.index')"
                icon="newspaper"
                wire:navigate
            >
                Notices
            </flux:navbar.item>
        </flux:navbar>
    </x-slot:header>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Header & Toolbar --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">Announcements</flux:heading>
                <flux:subheading>Manage news, updates, and emergency alerts.</flux:subheading>
            </div>
            <flux:button href="{{ route('announcements.create') }}" variant="primary" icon="plus">
                New Announcement
            </flux:button>
        </div>

        <flux:card class="flex-1 p-0 overflow-hidden">
            {{-- Search Bar --}}
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                <form method="GET">
                    <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search titles..." class="max-w-sm" />
                </form>
            </div>

            {{-- Table --}}
            <flux:table :paginate="$announcements">
                <flux:table.columns>
                    <flux:table.column>Title</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Publish Date</flux:table.column> {{-- Changed from Priority --}}
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($announcements as $item)
                        <flux:table.row :key="$item->id">
                            {{-- Title & Image --}}
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    @if($item->cover_image)
                                        <div class="h-10 w-10 rounded bg-zinc-100 dark:bg-zinc-800 bg-cover bg-center shrink-0" 
                                             style="background-image: url('{{ Storage::url($item->cover_image) }}')">
                                        </div>
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-800 shrink-0">
                                            <flux:icon name="newspaper" class="text-zinc-400" size="sm" />
                                        </div>
                                    @endif
                                    
                                    <div class="flex flex-col">
                                        <span class="font-medium truncate max-w-[200px]">{{ $item->title }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            {{-- Status Badge --}}
                            <flux:table.cell>
                                @php
                                    $statusColor = match($item->status) {
                                        'published' => 'green',
                                        'archived' => 'zinc',
                                        default => 'zinc',
                                    };
                                    
                                    // Visual check for expiration
                                    if($item->expires_at && $item->expires_at->isPast()) {
                                        $statusColor = 'red';
                                        $statusLabel = 'Expired';
                                    } else {
                                        $statusLabel = ucfirst($item->status);
                                    }
                                @endphp
                                <flux:badge :color="$statusColor" size="sm" inset="top bottom">{{ $statusLabel }}</flux:badge>
                            </flux:table.cell>

                            {{-- Dates (Replaces Priority) --}}
                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium">
                                        {{ $item->publish_at ? $item->publish_at->format('M d, Y') : 'Draft' }}
                                    </span>
                                    @if($item->expires_at)
                                        <span class="text-xs text-zinc-500">
                                            Exp: {{ $item->expires_at->format('M d, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="xs" variant="ghost" />
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('announcements.show', $item) }}" icon="eye">View</flux:menu.item>
                                        <flux:menu.item href="{{ route('announcements.edit', $item) }}" icon="pencil-square">Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <form action="{{ route('announcements.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this?');">
                                            @csrf @method('DELETE')
                                            <flux:menu.item type="submit" icon="trash" class="text-red-600">Delete</flux:menu.item>
                                        </form>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center text-zinc-500 py-6">No announcements found.</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</x-layouts::app>