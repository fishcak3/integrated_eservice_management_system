<x-layouts::app :title="__('Archived Announcements')">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item>Announcements</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.announcements.archived') }}">Archived</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                title: true,
                status: true,
                publish: true,
                expiry: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">
        
        {{-- Top Header Area --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Archived Announcements</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">View and manage past, inactive, or expired announcements.</flux:subheading>
            </div>
        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ url()->current() }}" class="flex flex-col gap-4">
            {{-- Announcement Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Date Added Range Filter Dropdown --}}
                <flux:dropdown>
                    <flux:badge as="button" rounded color="{{ request('date_from') || request('date_to') ? 'blue' : 'zinc' }}" icon="calendar" size="lg" class="cursor-pointer">
                        Date Range
                    </flux:badge>
                        
                    <flux:menu class="p-4 w-auto min-w-[320px] space-y-4">
                        <flux:heading size="sm" class="mb-2">Filter Date Range</flux:heading>
                            
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <div class="flex-1">
                                <flux:input type="date" label="From Date" name="date_from" value="{{ request('date_from') }}" />
                            </div>
                                
                            <div class="flex-1">
                                <flux:input type="date" label="To Date" name="date_to" value="{{ request('date_to') }}" />
                            </div>
                        </div>
                        <div class="pt-2">
                            <flux:button type="submit" variant="primary" size="sm" class="w-full">Apply Filters</flux:button>
                        </div>
                    </flux:menu>
                </flux:dropdown>

                {{-- Clear Filters --}}
                @if(request()->hasAny(['search', 'date_from', 'date_to']) && array_filter(request()->only(['search', 'date_from', 'date_to'])))
                    <flux:button href="{{ route('admin.announcements.archived') }}" size="sm" variant="subtle" icon="x-mark">
                        Clear
                    </flux:button>
                @endif
            </div>

            {{-- Toolbar: Search & Actions --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Search Input --}}
                <div class="flex-1 max-w-sm">
                    <flux:input 
                        name="search" 
                        value="{{ request('search') }}" 
                        icon="magnifying-glass" 
                        placeholder="Search titles..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                        onchange="this.form.submit()"
                    />
                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.title" label="Title" x-on:change.stop />
                            <flux:checkbox x-model="cols.status" label="Status" x-on:change.stop />
                            <flux:checkbox x-model="cols.publish" label="Publish Date" x-on:change.stop />
                            <flux:checkbox x-model="cols.expiry" label="Expiry Date" x-on:change.stop />
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </form>

        {{-- Table Container --}}
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <flux:table class="whitespace-nowrap bg-transparent">
                <flux:table.columns>
                    {{-- Checkbox Column --}}
                    <flux:table.column align="center">
                        <flux:checkbox />
                    </flux:table.column>

                    <flux:table.column x-show="cols.title">Title</flux:table.column>
                    <flux:table.column x-show="cols.status">Status</flux:table.column>
                    <flux:table.column x-show="cols.publish">Publish Date</flux:table.column>
                    <flux:table.column x-show="cols.expiry">Expiry/Archive Date</flux:table.column>
                    <flux:table.column align="end"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($archivedAnnouncements as $item)
                        {{-- Added cursor-pointer and onclick to the row --}}
                        <flux:table.row 
                            :key="$item->id"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('admin.announcements.archived.show', $item) }}'"
                        >
                            {{-- Row Checkbox --}}
                            <flux:table.cell onclick="event.stopPropagation()">
                                <div class="flex justify-center">
                                    <flux:checkbox />
                                </div>
                            </flux:table.cell>

                            {{-- Title & Image --}}
                            <flux:table.cell x-show="cols.title">
                                <div class="flex items-center gap-3">
                                    @if($item->cover_image)
                                        <div class="h-10 w-10 rounded bg-zinc-100 dark:bg-zinc-800 bg-cover bg-center shrink-0" 
                                             style="background-image: url('{{ Storage::url($item->cover_image) }}')">
                                        </div>
                                    @else
                                        <flux:icon name="archive-box" size="sm" class="text-zinc-400" />
                                    @endif
                                    
                                    <span class="font-medium capitalize text-gray-900 dark:text-white truncate max-w-[200px]">{{ $item->title }}</span>
                                </div>
                            </flux:table.cell>

                            {{-- Status Badge --}}
                            <flux:table.cell x-show="cols.status">
                                @php
                                    $statusColor = 'zinc';
                                    $statusLabel = ucfirst($item->status);

                                    // Visual check for expiration
                                    if($item->expires_at && $item->expires_at->isPast()) {
                                        $statusColor = 'red';
                                        $statusLabel = 'Expired';
                                    }
                                @endphp
                                <flux:badge rounded color="{{ $statusColor }}" size="sm" class="rounded-full px-2.5">
                                    {{ $statusLabel }}
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Publish Date --}}
                            <flux:table.cell x-show="cols.publish" class="text-zinc-600 dark:text-zinc-300">
                                {{ $item->publish_at ? $item->publish_at->format('M d, Y') : 'Draft' }}
                            </flux:table.cell>

                            {{-- Expiry Date --}}
                            <flux:table.cell x-show="cols.expiry" class="text-zinc-600 dark:text-zinc-300">
                                {{ $item->expires_at ? $item->expires_at->format('M d, Y') : 'No Expiry' }}
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end" onclick="event.stopPropagation()">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('admin.announcements.archived.show', $item) }}" icon="eye">View Details</flux:menu.item>
                                        <flux:menu.item href="{{ route('admin.announcements.archived.edit', $item) }}" icon="arrow-up-circle">
                                            Republish (Review First)
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        
                                        <flux:modal.trigger name="delete-archived-{{ $item->id }}">
                                            <flux:menu.item icon="trash" variant="danger" class="cursor-pointer">
                                                Delete
                                            </flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <flux:icon name="archive-box" size="lg" class="text-zinc-400" />
                                    <span>No archived announcements found.</span>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endempty
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                {{ $archivedAnnouncements->links() }}
            </div>
        </div>
    </div>

    {{-- Render all Modals safely outside the table --}}
    @foreach($archivedAnnouncements as $item)
        <form action="{{ route('admin.announcements.destroy', $item) }}" method="POST">
            @csrf 
            @method('DELETE')

            <x-confirm-modal 
                name="delete-archived-{{ $item->id }}" 
                title="Permanently Delete Announcement" 
                confirmText="Yes, Delete Permanently" 
                confirmVariant="danger"
            >
                This will permanently delete "<strong>{{ $item->title }}</strong>" from the archives. This action cannot be undone. Are you sure?
            </x-confirm-modal>
        </form>
    @endforeach

</x-layouts::app>