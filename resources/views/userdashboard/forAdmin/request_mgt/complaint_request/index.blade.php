<x-layouts::app :title="__('Complaints Management')">
    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item>Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.complaints.index') }}" :current="true">Complaints</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                case: true,
                complainant: true,
                respondent: true,
                type: true,
                status: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Complaints</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">Manage resident blotter complaints.</flux:subheading>
            </div>
            
            <flux:button href="{{ route('admin.complaints.create') }}" variant="primary" icon="plus">File Complaint</flux:button>
        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ url()->current() }}" x-on:change="if (!$event.target.hasAttribute('data-no-submit')) $el.submit()" wire:key="filter-form-complaints" class="flex flex-col gap-4">
            
            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Severity Filter Dropdown --}}
                <flux:dropdown>
                    @php $currentSeverities = (array) request('severities', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentSeverities) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentSeverities) ? count($currentSeverities) . ' Severities' : 'Severity' }}
                    </flux:badge>
                    
                    <flux:menu class="w-48 p-3 space-y-2 max-h-72 overflow-y-auto">
                        <flux:heading size="sm" class="mb-2">Filter Severity</flux:heading>
                        <flux:checkbox name="severities[]" value="critical" label="Critical" :checked="in_array('critical', $currentSeverities)" />
                        <flux:checkbox name="severities[]" value="high" label="High" :checked="in_array('high', $currentSeverities)" />
                        <flux:checkbox name="severities[]" value="medium" label="Medium" :checked="in_array('medium', $currentSeverities)" />
                        <flux:checkbox name="severities[]" value="low" label="Low" :checked="in_array('low', $currentSeverities)" />
                    </flux:menu>
                </flux:dropdown>

                {{-- Status Filter Dropdown --}}
                <flux:dropdown>
                    @php $currentCStatuses = (array) request('complaint_statuses', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentCStatuses) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentCStatuses) ? count($currentCStatuses) . ' Statuses' : 'Status' }}
                    </flux:badge>
                    
                    <flux:menu class="w-48 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Status</flux:heading>
                        <flux:checkbox name="complaint_statuses[]" value="pending" label="Pending" :checked="in_array('pending', $currentCStatuses)" />
                        <flux:checkbox name="complaint_statuses[]" value="investigation" label="Investigation" :checked="in_array('investigation', $currentCStatuses)" />
                        <flux:checkbox name="complaint_statuses[]" value="hearing" label="Hearing" :checked="in_array('hearing', $currentCStatuses)" />
                        <flux:checkbox name="complaint_statuses[]" value="settled" label="Settled" :checked="in_array('settled', $currentCStatuses)" />
                        <flux:checkbox name="complaint_statuses[]" value="dismissed" label="Dismissed" :checked="in_array('dismissed', $currentCStatuses)" />
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
                @if(request()->hasAny(['search', 'severities', 'complaint_statuses', 'date_from', 'date_to']))
                    <flux:button href="{{ route('admin.complaints.index') }}" size="sm" variant="subtle" icon="x-mark">
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
                        placeholder="Search complaints..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                    />
                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.case" label="Case #" x-on:change.stop />
                            <flux:checkbox x-model="cols.complainant" label="Complainant" x-on:change.stop />
                            <flux:checkbox x-model="cols.respondent" label="Respondent" x-on:change.stop />
                            <flux:checkbox x-model="cols.type" label="Type / Severity" x-on:change.stop />
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
                    <flux:table.column x-show="cols.case">Case #</flux:table.column>
                    <flux:table.column x-show="cols.complainant">Complainant</flux:table.column>
                    <flux:table.column x-show="cols.respondent">Respondent</flux:table.column>
                    <flux:table.column x-show="cols.type">Type / Severity</flux:table.column>
                    <flux:table.column x-show="cols.status">Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @forelse($complaints as $case)
                        <flux:table.row 
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('admin.complaints.show', $case->id) }}'"
                        >
                            <flux:table.cell x-show="cols.case" class="font-mono text-sm">
                                {{ $case->case_number }}
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.complainant" class="font-medium text-gray-900 dark:text-white">
                                @if($case->resident)
                                    {{ $case->resident->fname }} {{ $case->resident->lname }}
                                @else
                                    {{ $case->complainant_name ?? 'Unknown' }}
                                @endif
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.respondent" class="text-zinc-600 dark:text-zinc-300">
                                {{ $case->respondent_name ?? 'N/A' }}
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.type">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $case->type?->name ?? 'Unknown Type' }}</span>
                                    @php
                                        $sevColor = match($case->type?->severity_level ?? 'low') {
                                            'critical' => 'red',
                                            'high' => 'orange',
                                            'medium' => 'yellow',
                                            default => 'green',
                                        };
                                    @endphp
                                    <flux:badge rounded :color="$sevColor" size="sm">{{ ucfirst($case->type->severity_level ?? 'low') }}</flux:badge>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.status">
                                <flux:badge rounded color="{{ match($case->status) {
                                    'pending' => 'yellow',
                                    'investigation' => 'blue',
                                    'hearing' => 'orange',
                                    'settled' => 'green',
                                    'dismissed' => 'red',
                                    default => 'zinc'
                                } }}" size="sm" inset="top bottom">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</flux:badge>
                            </flux:table.cell>
                            
                            {{-- Actions --}}
                            <flux:table.cell align="end" onclick="event.stopPropagation()">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />

                                    <flux:menu>
                                        <flux:menu.item href="{{ route('admin.complaints.show', $case->id) }}" icon="eye">
                                            View Details
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.document-text class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                    @if(request()->hasAny(['search', 'severities', 'complaint_statuses', 'date_from', 'date_to']))
                                        <p>No complaints found matching your filters.</p>
                                    @else
                                        <p>No complaints found.</p>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                {{ $complaints->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>