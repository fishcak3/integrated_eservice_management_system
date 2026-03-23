<x-layouts::app :title="__('Announcements')">
    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('official.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>All Announcements</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                title: true,
                status: true,
                publish_date: true,
                expiry_date: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Announcements</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">View all news, updates, and emergency alerts.</flux:subheading>
            </div>
            
            <flux:button href="{{ route('official.announcements.create') }}" variant="primary" icon="plus">New Draft</flux:button>
        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ url()->current() }}" x-on:change="if (!$event.target.hasAttribute('data-no-submit')) $el.submit()" wire:key="filter-form-announcements" class="flex flex-col gap-4">
            
            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Status Filter Dropdown --}}
                <flux:dropdown>
                    @php $currentStatuses = (array) request('statuses', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentStatuses) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentStatuses) ? count($currentStatuses) . ' Statuses' : 'Status' }}
                    </flux:badge>
                    
                    <flux:menu class="w-48 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Status</flux:heading>
                        <flux:checkbox name="statuses[]" value="draft" label="Draft" :checked="in_array('draft', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="published" label="Published" :checked="in_array('published', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="archived" label="Archived" :checked="in_array('archived', $currentStatuses)" />
                    </flux:menu>
                </flux:dropdown>

                {{-- Date Filter Dropdown --}}
                <flux:dropdown>
                    <flux:badge as="button" rounded color="{{ request('date_from') && request('date_to') ? 'blue' : 'zinc' }}" icon="calendar" size="lg" class="cursor-pointer">
                        Date Range
                    </flux:badge>
                    
                    <flux:menu class="p-4 w-auto min-w-[320px] space-y-4" @click.stop>
                        <flux:heading size="sm" class="mb-2">Filter Date Range</flux:heading>
                        
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <div class="flex-1">
                                <flux:input type="date" label="From Date" name="date_from" value="{{ request('date_from') }}" data-no-submit />
                            </div>
                            <div class="flex-1">
                                <flux:input type="date" label="To Date" name="date_to" value="{{ request('date_to') }}" data-no-submit />
                            </div>
                        </div>

                        <div class="mt-2 flex justify-end">
                            <flux:button type="submit" variant="primary" size="sm" class="w-full sm:w-auto">Apply Dates</flux:button>
                        </div>
                    </flux:menu>
                </flux:dropdown>
                
                {{-- Clear Filters Button --}}
                @if(request()->hasAny(['search', 'statuses', 'date_from', 'date_to']))
                    <flux:button href="{{ route('official.announcements.index') }}" size="sm" variant="subtle" icon="x-mark">
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
                            <flux:checkbox x-model="cols.publish_date" label="Publish Date" x-on:change.stop />
                            <flux:checkbox x-model="cols.expiry_date" label="Expiry/Archive Date" x-on:change.stop />
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </form>

        {{-- Table Container --}}
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <flux:table class="whitespace-nowrap bg-transparent">
                <flux:table.columns>
                    <flux:table.column x-show="cols.title">Title</flux:table.column>
                    <flux:table.column x-show="cols.status">Status</flux:table.column>
                    <flux:table.column x-show="cols.publish_date">Publish Date</flux:table.column>
                    <flux:table.column x-show="cols.expiry_date">Expiry/Archive Date</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @forelse($announcements as $item)
                        <flux:table.row 
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('official.announcements.show', $item) }}'"
                        >
                            <flux:table.cell x-show="cols.title" class="font-medium text-gray-900 dark:text-white">
                                <span class="capitalize truncate max-w-[200px] block">{{ $item->title }}</span>
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.status">
                                @php
                                    $statusColor = match($item->status) {
                                        'published' => 'green',
                                        'archived' => 'zinc',
                                        default => 'zinc', // Draft will use zinc/gray
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
                            
                            <flux:table.cell x-show="cols.publish_date" class="text-zinc-600 dark:text-zinc-300">
                                {{ $item->publish_at ? $item->publish_at->format('M d, Y') : 'Draft' }}
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.expiry_date" class="text-zinc-600 dark:text-zinc-300">
                                {{ $item->expires_at ? $item->expires_at->format('M d, Y') : 'No Expiry' }}
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end" onclick="event.stopPropagation()">
                                @if($item->user_id === auth()->id())
                                    <flux:dropdown>
                                        <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />

                                        <flux:menu>
                                            <flux:menu.item href="{{ route('official.announcements.show', $item) }}" icon="eye">
                                                View
                                            </flux:menu.item>
                                            <flux:menu.item href="{{ route('official.announcements.edit', $item) }}" icon="pencil-square">
                                                Edit
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                @else
                                    <flux:button href="{{ route('official.announcements.show', $item) }}" size="sm" variant="ghost" icon="eye">
                                        View
                                    </flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.megaphone class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                    @if(request()->hasAny(['search', 'statuses', 'date_from', 'date_to']))
                                        <p>No announcements found matching your filters.</p>
                                    @else
                                        <p>No announcements found.</p>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>