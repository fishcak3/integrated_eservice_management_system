<x-layouts::app :title="__('Document Requests')">
    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('official.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Document Requests</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                tracking: true,
                requestor: true,
                type: true,
                date: true,
                status: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Document Requests</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">Manage resident document requests and processing.</flux:subheading>
            </div>

        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ url()->current() }}" x-on:change="if (!$event.target.hasAttribute('data-no-submit')) $el.submit()" wire:key="filter-form-documents" class="flex flex-col gap-4">
            
            {{-- Document Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Doc Type Filter Dropdown --}}
                <flux:dropdown>
                    @php $currentDocTypes = (array) request('doc_types', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentDocTypes) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentDocTypes) ? count($currentDocTypes) . ' Types' : 'Doc Type' }}
                    </flux:badge>
                    
                    <flux:menu class="w-56 p-3 space-y-2 max-h-72 overflow-y-auto">
                        <flux:heading size="sm" class="mb-2">Filter Document Type</flux:heading>
                        @if(isset($documentTypes))
                            @foreach($documentTypes as $type)
                                <flux:checkbox name="doc_types[]" value="{{ $type->id }}" label="{{ $type->name }}" :checked="in_array($type->id, $currentDocTypes)" />
                            @endforeach
                        @endif
                    </flux:menu>
                </flux:dropdown>

                {{-- Status Filter Dropdown --}}
                <flux:dropdown>
                    @php $currentStatuses = (array) request('statuses', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentStatuses) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentStatuses) ? count($currentStatuses) . ' Statuses' : 'Status' }}
                    </flux:badge>
                    
                    <flux:menu class="w-48 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Status</flux:heading>
                        <flux:checkbox name="statuses[]" value="pending" label="Pending" :checked="in_array('pending', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="processing" label="Processing" :checked="in_array('processing', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="ready_for_pickup" label="Ready for Pickup" :checked="in_array('ready_for_pickup', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="completed" label="Completed" :checked="in_array('completed', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="rejected" label="Rejected" :checked="in_array('rejected', $currentStatuses)" />
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
                @if(request()->hasAny(['search', 'doc_types', 'statuses', 'date_from', 'date_to']))
                    <flux:button href="{{ route('official.documents.index') }}" size="sm" variant="subtle" icon="x-mark">
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
                        placeholder="Search tracking code or requestor..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                    />
                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.tracking" label="Tracking Code" x-on:change.stop />
                            <flux:checkbox x-model="cols.requestor" label="Requestor" x-on:change.stop />
                            <flux:checkbox x-model="cols.type" label="Type" x-on:change.stop />
                            <flux:checkbox x-model="cols.date" label="Date Submitted" x-on:change.stop />
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
                    <flux:table.column x-show="cols.tracking">Tracking Code</flux:table.column>
                    <flux:table.column x-show="cols.requestor">Requestor</flux:table.column>
                    <flux:table.column x-show="cols.type">Type</flux:table.column>
                    <flux:table.column x-show="cols.date">Date</flux:table.column>
                    <flux:table.column x-show="cols.status">Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($documentRequests as $req)
                        <flux:table.row 
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('official.documents.show', $req->id) }}'"
                        >
                            <flux:table.cell x-show="cols.tracking" class="font-mono text-sm">
                                {{ $req->tracking_code }}
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.requestor">
                                <div class="flex items-center gap-3">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        @if($req->mode_of_request === 'walk-in')
                                            {{ $req->requestor_name }} <span class="text-xs text-zinc-500 font-normal">(Walk-in)</span>
                                        @elseif($req->mode_of_request === 'online')
                                            {{ $req->requestor_name }} <span class="text-xs text-zinc-500 font-normal">(Online)</span>
                                        @else
                                            <span class="text-zinc-500 italic">Unknown Requestor</span>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.type" class="text-zinc-600 dark:text-zinc-300">
                                {{ $req->documentType->name ?? 'Unknown' }}
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.date" class="text-zinc-500">
                                {{ $req->created_at->format('M d, Y') }}
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.status">
                                @php
                                    $color = match($req->status) {
                                        'pending' => 'yellow',
                                        'processing' => 'blue',
                                        'ready_for_pickup' => 'green',
                                        'rejected' => 'red',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge rounded :color="$color" size="sm" inset="top bottom">{{ ucfirst(str_replace('_', ' ', $req->status)) }}</flux:badge>
                            </flux:table.cell>
                            
                            {{-- Actions --}}
                            <flux:table.cell align="end" onclick="event.stopPropagation()">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />

                                    <flux:menu>
                                        <flux:menu.item href="{{ route('official.documents.show', $req->id) }}" icon="eye">
                                            View Details
                                        </flux:menu.item>

                                        <flux:menu.item href="{{ route('official.documents.edit', $req->id) }}" icon="pencil-square">
                                            Edit
                                        </flux:menu.item>

                                        @if($req->status === 'pending')
                                            <flux:menu.separator />
                                            <flux:menu.item href="{{ route('official.documents.process', $req->id) }}" icon="arrow-path">
                                                Process Request
                                            </flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.document-text class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                    @if(request()->hasAny(['search', 'doc_types', 'statuses', 'date_from', 'date_to']))
                                        <p>No document requests found matching your filters.</p>
                                    @else
                                        <p>No document requests found.</p>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                {{ $documentRequests->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>