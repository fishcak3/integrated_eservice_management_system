<x-layouts::app :title="__('My Complaints')">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item >Complaints</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                case: true,
                respondent: true,
                date: true,
                status: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Page Title & Dynamic Buttons --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">My Complaints</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">Track the status of complaints you have filed.</flux:subheading>
            </div>

            {{-- DYNAMIC ACTION BUTTONS --}}
            <div>
                @if(Auth::user()->verification_status === 'verified')
                    <flux:button href="{{ route('resident.complaints.create') }}" variant="primary" icon="plus">
                        File Complaint
                    </flux:button>
                @else
                    <flux:tooltip content="You must verify your account in Settings first.">
                        <flux:button href="{{ route('profile.edit') }}" variant="filled" class="bg-amber-100 text-amber-800 hover:bg-amber-200 border-amber-200">
                            <flux:icon.lock-closed class="w-4 h-4 mr-2" />
                            Verify to File Complaint
                        </flux:button>
                    </flux:tooltip>
                @endif
            </div>
        </div>

        <form method="GET" action="{{ route('resident.complaints.index') }}" x-on:change="if (!$event.target.hasAttribute('data-no-submit')) $el.submit()" class="flex flex-col gap-4">
            
            {{-- Flux UI Segmented Status Filter --}}
            <div class="overflow-x-auto pb-2 sm:pb-0 hide-scrollbar"
                 x-data="{ status: '{{ request('status', '') }}' }"
                 x-init="$watch('status', value => $el.closest('form').submit())">
                 
                {{-- Note: We swapped :value for x-model="status" --}}
                <flux:radio.group x-model="status" variant="segmented" name="status" class="w-max">
                    <flux:radio value="" label="All" />
                    <flux:radio value="resolved" label="Resolved" />
                    <flux:radio value="under_investigation" label="Under Investigation" />
                    <flux:radio value="hearing_scheduled" label="Hearing Scheduled" />
                    <flux:radio value="pending" label="Pending" />
                    <flux:radio value="dismissed" label="Dismissed" />
                </flux:radio.group>
            </div>

            {{-- Toolbar: Search & Actions --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Search Input --}}
                <div class="flex-1 max-w-sm relative">
                    <flux:input 
                        name="search" 
                        value="{{ request('search') }}" 
                        icon="magnifying-glass" 
                        placeholder="Search complaints..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                        data-no-submit
                    />
                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.case" label="Case Number" x-on:change.stop />
                            <flux:checkbox x-model="cols.respondent" label="Respondent" x-on:change.stop />
                            <flux:checkbox x-model="cols.date" label="Incident Date" x-on:change.stop />
                            <flux:checkbox x-model="cols.status" label="Status" x-on:change.stop />
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Clear Search/Filter Button --}}
                    @if(request()->hasAny(['search', 'status']))
                        <flux:button href="{{ route('resident.complaints.index') }}" size="sm" variant="subtle" icon="x-mark">
                            Clear
                        </flux:button>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table Container --}}
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <flux:table class="whitespace-nowrap bg-transparent">
                <flux:table.columns>
                    <flux:table.column x-show="cols.case">Case Number</flux:table.column>
                    <flux:table.column x-show="cols.respondent">Respondent</flux:table.column>
                    <flux:table.column x-show="cols.date">Incident Date</flux:table.column>
                    <flux:table.column x-show="cols.status">Status</flux:table.column>
                    <flux:table.column align="end"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($complaints as $complaint)
                        <flux:table.row 
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('resident.complaints.show', $complaint->id) }}'"
                        >
                            <flux:table.cell x-show="cols.case" class="font-mono text-sm">
                                {{ $complaint->case_number }}
                            </flux:table.cell>
                            
                            <flux:table.cell x-show="cols.respondent" class="font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $complaint->respondent_name ?? 'N/A' }}
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.date" class="text-zinc-500">
                                {{ \Carbon\Carbon::parse($complaint->incident_date)->format('M d, Y') }}
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.status">
                                @php
                                    $statusColor = match($complaint->status) {
                                        'pending' => 'amber',
                                        'under_investigation' => 'blue',
                                        'hearing_scheduled' => 'purple',
                                        'resolved' => 'green',
                                        'dismissed' => 'zinc',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge rounded color="{{ $statusColor }}" size="sm" inset="top bottom">
                                    {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end" onclick="event.stopPropagation()">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />
                                    
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('resident.complaints.show', $complaint->id) }}" icon="eye">View Details</flux:menu.item>
                                        
                                        @if($complaint->status === 'pending')
                                            <flux:menu.item href="{{ route('resident.complaints.edit', $complaint->id) }}" icon="pencil-square">Edit Complaint</flux:menu.item>
                                            <flux:menu.separator />
                                            <form action="{{ route('resident.complaints.destroy', $complaint->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel and delete this complaint?');">
                                                @csrf 
                                                @method('DELETE')
                                                <flux:menu.item as="button" type="submit" icon="trash" class="text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30">Cancel Complaint</flux:menu.item>
                                            </form> 
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500 py-12">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <flux:icon.exclamation-triangle class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                    @if(request()->hasAny(['search', 'status']))
                                        <p>No complaints found matching your filters.</p>
                                    @else
                                        <p>You haven't filed any complaints yet.</p>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            @if($complaints->hasPages())
                <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                    {{ $complaints->links() }}
                </div>
            @endif
        </div>
    </div>

</x-layouts::app>