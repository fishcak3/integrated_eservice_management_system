<x-layouts::app title="Former Officials">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('officials.former') }}">Former Officials</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                name: true,
                position: true,
                term: true,
                election_year: true,
                status: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Header Section --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Former Officials</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">History of past barangay officials and their terms.</flux:subheading>
            </div>
        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ url()->current() }}" x-on:change="$el.submit()" class="flex flex-col gap-4">
            
            {{-- Official Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Position Filter Dropdown --}}
                <flux:dropdown>
                    @php 
                        $currentPositions = (array) request('positions', []); 
                        
                        // Dynamically fetch position titles from the database
                        $positionsList = \Illuminate\Support\Facades\DB::table('positions')
                            ->select('title')
                            ->distinct()
                            ->orderBy('title', 'asc')
                            ->pluck('title');
                    @endphp
                    
                    <flux:badge as="button" rounded color="{{ !empty($currentPositions) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentPositions) ? count($currentPositions) . ' Positions' : 'Position' }}
                    </flux:badge>
                    
                    <flux:menu class="w-56 p-3 space-y-2 max-h-72 overflow-y-auto">
                        <flux:heading size="sm" class="mb-2">Filter Position</flux:heading>
                        
                        @forelse($positionsList as $pos)
                            <flux:checkbox 
                                name="positions[]" 
                                value="{{ $pos }}" 
                                label="{{ $pos }}" 
                                :checked="in_array($pos, $currentPositions)" 
                            />
                        @empty
                            <div class="text-sm text-zinc-500 py-1">No positions found.</div>
                        @endforelse
                    </flux:menu>
                </flux:dropdown>

                {{-- Election Year Filter Dropdown --}}
                <flux:dropdown>
                    @php 
                        $currentYears = (array) request('election_years', []); 
                        
                        // Dynamically fetch unique, non-null election years from the database
                        $electionYears = \Illuminate\Support\Facades\DB::table('official_terms')
                            ->select('election_year')
                            ->whereNotNull('election_year')
                            ->where('election_year', '!=', '')
                            ->distinct()
                            ->orderBy('election_year', 'desc')
                            ->pluck('election_year');
                    @endphp

                    <flux:badge as="button" rounded color="{{ !empty($currentYears) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentYears) ? count($currentYears) . ' Selected' : 'Election Year' }}
                    </flux:badge>

                    <flux:menu class="w-48 p-3 space-y-2 max-h-72 overflow-y-auto">
                        <flux:heading size="sm" class="mb-2">Filter Election Year</flux:heading>
                        
                        @forelse($electionYears as $year)
                            <flux:checkbox 
                                name="election_years[]" 
                                value="{{ $year }}" 
                                label="{{ $year }}" 
                                :checked="in_array($year, $currentYears)" 
                            />
                        @empty
                            <div class="text-sm text-zinc-500 py-1">No election years found.</div>
                        @endforelse
                    </flux:menu>
                </flux:dropdown>

                {{-- Term Status Filter Dropdown --}}
                <flux:dropdown>
                    @php 
                        $currentStatuses = (array) request('statuses', []); 
                        $statusOptions = ['completed', 'resigned', 'removed'];
                    @endphp

                    <flux:badge as="button" rounded color="{{ !empty($currentStatuses) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentStatuses) ? count($currentStatuses) . ' Statuses' : 'Term Status' }}
                    </flux:badge>

                    <flux:menu class="p-3 space-y-2 w-48">
                        <flux:heading size="sm" class="mb-2">Filter Status</flux:heading>
                        @foreach($statusOptions as $status)
                            <flux:checkbox 
                                name="statuses[]" 
                                value="{{ $status }}" 
                                label="{{ ucfirst($status) }}" 
                                :checked="in_array($status, $currentStatuses)" 
                            />
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
                
                {{-- Clear Filters Button --}}
                @if(request()->hasAny(['positions', 'election_years', 'statuses', 'search']))
                    <flux:button href="{{ route('officials.former') }}" size="sm" variant="subtle" icon="x-mark">
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
                        placeholder="Search former officials..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                    />
                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.name" label="Official Name" x-on:change.stop />
                            <flux:checkbox x-model="cols.position" label="Position" x-on:change.stop />
                            <flux:checkbox x-model="cols.term" label="Term Duration" x-on:change.stop />
                            <flux:checkbox x-model="cols.election_year" label="Election Year" x-on:change.stop />
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
                    <flux:table.column x-show="cols.name">Official Name</flux:table.column>
                    <flux:table.column x-show="cols.position">Former Position</flux:table.column>
                    <flux:table.column x-show="cols.term">Term Duration</flux:table.column>
                    <flux:table.column x-show="cols.election_year">Election Year</flux:table.column>
                    <flux:table.column x-show="cols.status">Term Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @isset($officials)
                        @forelse($officials as $official)
                            <flux:table.row 
                                class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors cursor-pointer"
                                onclick="window.location.href='{{ route('officials.showFormer', $official->id) }}'"
                            >
                                <flux:table.cell x-show="cols.name">
                                    <div class="flex items-center gap-3">
                                        <flux:avatar circle src="{{ $official->official?->resident?->user?->profile_photo_url ?? '' }}" initials="{{ substr($official->official?->resident?->fname ?? '?', 0, 1) }}" />
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $official->official?->resident?->full_name ?? 'Unknown Resident' }}</div>
                                            <div class="text-xs text-zinc-500">{{ $official->official?->resident?->user?->email ?? 'No email linked' }}</div>
                                        </div>
                                    </div>
                                </flux:table.cell>
                                
                                <flux:table.cell x-show="cols.position">
                                    <flux:badge size="sm" color="zinc">
                                        {{ $official->position?->title ?? 'Unknown Position' }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell x-show="cols.term" class="text-zinc-600 dark:text-zinc-300">
                                    <div class="text-sm">
                                        @if($official->term_start)
                                            {{ \Carbon\Carbon::parse($official->term_start)->format('M d, Y') }} - 
                                            {{ $official->term_end ? \Carbon\Carbon::parse($official->term_end)->format('M d, Y') : 'Unknown' }}
                                        @else
                                            <span class="text-zinc-500 italic">Date unavailable</span>
                                        @endif
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell x-show="cols.election_year" class="text-zinc-600 dark:text-zinc-300">
                                    <div class="text-sm">
                                        {{ $official->election_year ?? 'N/A' }}
                                    </div>
                                </flux:table.cell>
                                
                                <flux:table.cell x-show="cols.status">
                                    @if($official->status === 'completed')
                                        <flux:badge rounded color="green" size="sm" inset="top bottom">Former</flux:badge>
                                    @elseif($official->status === 'removed')
                                        <flux:badge rounded color="zinc" size="sm" inset="top bottom">Removed</flux:badge>
                                    @elseif($official->status === 'resigned')
                                        <flux:badge rounded color="red" size="sm" inset="top bottom">Resigned</flux:badge>
                                    @else
                                        <flux:badge rounded color="zinc" size="sm" inset="top bottom">N/A</flux:badge>
                                    @endif
                                </flux:table.cell>
                                
                                {{-- Actions --}}
                                <flux:table.cell align="end" onclick="event.stopPropagation()">
                                    <flux:dropdown>
                                        <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />
                                        
                                        <flux:menu>
                                            <flux:menu.item href="{{ route('officials.showFormer', $official->id) }}" icon="eye">
                                                View History
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6" class="text-center text-zinc-500 py-8">
                                    <div class="flex flex-col items-center justify-center">
                                        <flux:icon.building-library class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                        @if(request()->hasAny(['search', 'positions', 'election_years', 'statuses']))
                                            <p>No former officials found matching your filters.</p>
                                        @else
                                            <p>No former officials found.</p>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    @endisset
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            @isset($officials)
                @if($officials->hasPages())
                    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                        {{ $officials->links() }}
                    </div>
                @endif
            @endisset
        </div>
    </div>
</x-layouts::app>