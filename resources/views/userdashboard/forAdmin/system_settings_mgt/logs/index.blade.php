<x-layouts::app :title="__('Activity Management')">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item>Settings</flux:breadcrumbs.item>
            <flux:breadcrumbs.item :href="route('settings.logs')">Logs</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                timestamp: true,
                user: true,
                action: true,
                description: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">System Activity Logs</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">Monitor system changes, user actions, and data updates.</flux:subheading>
            </div>
        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ route('settings.logs') }}" x-on:change="$el.submit()" class="flex flex-col gap-4">
            
            {{-- Activity Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Event Type Filter Dropdown --}}
                <flux:dropdown>
                    @php $currentEvents = (array) request('event_types', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentEvents) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentEvents) ? count($currentEvents) . ' Actions' : 'Action Type' }}
                    </flux:badge>
                    
                    <flux:menu class="w-48 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Action</flux:heading>
                        <flux:checkbox name="event_types[]" value="created" label="Created" :checked="in_array('created', $currentEvents)" />
                        <flux:checkbox name="event_types[]" value="updated" label="Updated" :checked="in_array('updated', $currentEvents)" />
                        <flux:checkbox name="event_types[]" value="deleted" label="Deleted" :checked="in_array('deleted', $currentEvents)" />
                    </flux:menu>
                </flux:dropdown>
                
                {{-- Clear Filters Button --}}
                @if(request()->hasAny(['event_types', 'search']))
                    <flux:button href="{{ route('settings.logs') }}" size="sm" variant="subtle" icon="x-mark">
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
                        placeholder="Search logs, users, or models..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                    />
                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.timestamp" label="Timestamp" x-on:change.stop />
                            <flux:checkbox x-model="cols.user" label="User (Causer)" x-on:change.stop />
                            <flux:checkbox x-model="cols.action" label="Action" x-on:change.stop />
                            <flux:checkbox x-model="cols.description" label="Description" x-on:change.stop />
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </form>

        {{-- Table Container --}}
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <flux:table class="whitespace-nowrap bg-transparent">
                <flux:table.columns>
                    <flux:table.column x-show="cols.timestamp">Timestamp</flux:table.column>
                    <flux:table.column x-show="cols.user">User (Causer)</flux:table.column>
                    <flux:table.column x-show="cols.action">Action</flux:table.column>
                    <flux:table.column x-show="cols.description">Description</flux:table.column>
                    <flux:table.column align="end">Details</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($logs as $log)
                        <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors">
                            <flux:table.cell x-show="cols.timestamp" class="text-zinc-500">
                                {{ $log->created_at->format('M d, Y h:i A') }}
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.user">
                                <div class="flex items-center gap-3">
                                    @if($log->causer)
                                        <flux:avatar circle size="sm" src="{{ $log->causer->profile_photo_url }}" initials="{{ substr($log->causer->display_name ?? $log->causer->email, 0, 1) }}" />
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $log->causer->display_name ?? $log->causer->email }}
                                        </span>
                                    @else
                                        <flux:avatar circle size="sm" initials="S" />
                                        <span class="text-zinc-500 italic">System</span>
                                    @endif
                                </div>
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.action">
                                @php
                                    $badgeColor = match($log->event) {
                                        'created' => 'green',
                                        'updated' => 'yellow',
                                        'deleted' => 'red',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge rounded size="sm" color="{{ $badgeColor }}" inset="top bottom">
                                    {{ ucfirst($log->event) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.description" class="text-zinc-600 dark:text-zinc-300">
                                {{ $log->description }}
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:modal.trigger name="view-log-{{ $log->id }}">
                                    <flux:button size="sm" variant="ghost" icon="eye" class="text-zinc-500 hover:text-zinc-900 dark:hover:text-white">View</flux:button>
                                </flux:modal.trigger>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.document-text class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                    @if(request()->hasAny(['search', 'event_types']))
                                        <p>No activity logs found matching your filters.</p>
                                    @else
                                        <p>No activity logs recorded yet.</p>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            @if($logs->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Modals Loop --}}
    @foreach ($logs as $logModal)
        <flux:modal name="view-log-{{ $logModal->id }}" class="max-w-3xl">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Log Details</flux:heading>
                    <flux:subheading>Tracking ID: {{ $logModal->id }} | Subject: {{ class_basename($logModal->subject_type) }}</flux:subheading>
                </div>

                @if($logModal->properties && count($logModal->properties) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if(isset($logModal->properties['old']))
                            <flux:card>
                                <flux:heading size="sm" class="text-red-600 mb-3">Old Values</flux:heading>
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-md p-3 text-sm font-mono overflow-x-auto">
                                    @foreach($logModal->properties['old'] as $key => $value)
                                        <div class="mb-1 border-b border-zinc-200 dark:border-zinc-800 pb-1 last:border-0">
                                            <span class="text-zinc-500">{{ $key }}:</span> 
                                            <span class="text-zinc-900 dark:text-zinc-100">{{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </flux:card>
                        @endif

                        @if(isset($logModal->properties['attributes']))
                            <flux:card>
                                <flux:heading size="sm" class="text-green-600 mb-3">New Values</flux:heading>
                                <div class="bg-zinc-50 dark:bg-zinc-900 rounded-md p-3 text-sm font-mono overflow-x-auto">
                                    @foreach($logModal->properties['attributes'] as $key => $value)
                                        <div class="mb-1 border-b border-zinc-200 dark:border-zinc-800 pb-1 last:border-0">
                                            <span class="text-zinc-500">{{ $key }}:</span> 
                                            <span class="text-zinc-900 dark:text-zinc-100">
                                                @if(isset($logModal->properties['old']) && array_key_exists($key, $logModal->properties['old']) && $logModal->properties['old'][$key] != $value)
                                                    <span class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 px-1 rounded">
                                                        {{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}
                                                    </span>
                                                @else
                                                    {{ is_array($value) ? json_encode($value) : ($value ?? 'null') }}
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </flux:card>
                        @endif
                    </div>
                @else
                    <div class="text-center py-6 text-zinc-500">
                        No detailed attribute changes recorded for this event.
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">Close</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>
    @endforeach

</x-layouts::app>