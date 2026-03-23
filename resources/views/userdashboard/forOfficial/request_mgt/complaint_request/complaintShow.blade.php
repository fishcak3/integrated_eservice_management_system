<x-layouts::app :title="__('Manage Complaint')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('official.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.complaints.index', ['type' => 'complaints']) }}">Complaint Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $complaint->case_number }}</flux:breadcrumbs.item>
        </flux:breadcrumbs> 
    </x-slot>

    @php
        $statusColor = match($complaint->status) {
            'pending' => 'yellow',
            'investigation' => 'blue',
            'hearing' => 'orange',
            'settled' => 'green',
            'dismissed' => 'red',
            default => 'zinc'
        };
        $statusText = ucfirst($complaint->status);
    @endphp

    {{-- Header & Actions --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" level="1">
                    Complaint Details
                </flux:heading>
                
                {{-- Status Badge --}}
                <flux:badge rounded :color="$statusColor" size="sm">
                    {{ $statusText }}
                </flux:badge>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:text variant="subtle">
                    Case #: <span class="font-mono font-medium text-zinc-900 dark:text-zinc-100">{{ $complaint->case_number }}</span>
                </flux:text>
            </div>
        </div>

    </div>

    {{-- Main Layout Container --}}
    <div class="space-y-10">

        {{-- Section 1: Complaint Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Complaint Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Details regarding the involved parties, incident date, location, and narrative.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Complainant</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $complaint->complainant_name }}
                            </dd>
                            @if($complaint->complainant_phone)
                                <dd class="mt-1 text-xs text-zinc-500">
                                    {{ $complaint->complainant_phone }}
                                </dd>
                            @endif
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Respondent</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $complaint->respondent_name }}
                            </dd>
                        </div>
                        
                        <div class="sm:col-span-2">
                            <flux:separator class="mb-6" />
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Incident Date</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($complaint->incident_at)->format('F d, Y h:i A') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Location</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $complaint->location }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <flux:separator class="mb-6" />
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Narrative / Incident Details</dt>
                            <dd class="mt-2 rounded-md bg-zinc-50 p-4 text-sm text-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-300 border border-zinc-100 dark:border-zinc-800">
                                {{ $complaint->incident_details }}
                            </dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>

        {{-- Section 2: Update Case Status --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Update Case Status</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Progress the complaint through its lifecycle, scheduling hearings, or logging resolutions.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <form action="{{ route('official.complaints.update-status', $complaint->id) }}" method="POST" x-data="{ currentStatus: '{{ $complaint->status }}' }" class="flex flex-col gap-6">
                        @csrf
                        @method('PATCH')

                        {{-- Radio Group --}}
                        <div class="flex flex-col gap-2">
                            <flux:radio.group x-model="currentStatus" name="status" variant="cards" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <flux:radio value="pending" icon="clock" label="Pending" description="Awaiting review" />
                                <flux:radio value="investigation" icon="magnifying-glass" label="Investigation" description="Gathering facts" />
                                <flux:radio value="hearing" icon="users" label="Hearing" description="Mediation scheduled" />
                                <flux:radio value="settled" icon="check-circle" label="Settled" description="Mutually resolved" />
                                <flux:radio value="dismissed" icon="x-circle" label="Dismissed" description="Case dropped" />
                            </flux:radio.group>
                        </div>

                        {{-- Dynamic Fields Container --}}
                        <div class="space-y-4 rounded-lg bg-zinc-50 p-5 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700">
                            
                            {{-- Show when Investigation --}}
                            <div x-show="currentStatus === 'investigation'" style="display: none;" x-transition>
                                <flux:textarea name="investigation_notes" label="Investigation Notes" rows="3">{{ $complaint->investigation_notes }}</flux:textarea>
                            </div>

                            {{-- Show when Hearing --}}
                            <div x-show="currentStatus === 'hearing'" style="display: none;" x-transition>
                                <flux:input type="datetime-local" name="hearing_date" label="Hearing Date & Time" value="{{ $complaint->hearing_date ? \Carbon\Carbon::parse($complaint->hearing_date)->format('Y-m-d\TH:i') : '' }}" />
                            </div>

                            {{-- Show when Settled or Dismissed --}}
                            <div x-show="currentStatus === 'settled' || currentStatus === 'dismissed'" style="display: none;" x-transition class="space-y-4">
                                <flux:select name="resolution" label="Resolution Type">
                                    <option value="">Select specific resolution...</option>
                                    <option value="founded" @selected($complaint->resolution === 'founded')>Founded</option>
                                    <option value="unfounded" @selected($complaint->resolution === 'unfounded')>Unfounded</option>
                                    <option value="settled" @selected($complaint->resolution === 'settled')>Settled</option>
                                    <option value="dismissed" @selected($complaint->resolution === 'dismissed')>Dismissed</option>
                                </flux:select>
                                
                                <flux:textarea name="resolution_notes" label="Resolution Notes" rows="3">{{ $complaint->resolution_notes }}</flux:textarea>
                            </div>

                            {{-- Always Show Official Remarks --}}
                            <div>
                                <flux:textarea name="official_remarks" label="Official Remarks / Update Log" rows="2" placeholder="Brief note about this update (optional)..."></flux:textarea>
                            </div>
                        </div>

                        <div class="flex justify-end mt-2">
                            <flux:button type="submit" variant="primary">Save Changes</flux:button>
                        </div>
                    </form>
                </flux:card>
            </div>
        </div>

        {{-- Section 3: Case History --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Case History & Updates</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    A timeline log of all status changes, assignments, and notes for this complaint.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    @if($complaint->statusHistories && $complaint->statusHistories->count() > 0)
                        <div class="relative ml-3 space-y-8 border-l-2 border-zinc-200 dark:border-zinc-700">
                            @foreach($complaint->statusHistories as $history)
                                <div class="relative pl-6">
                                    {{-- Timeline Dot --}}
                                    <div class="absolute -left-[9px] top-1.5 h-4 w-4 rounded-full border-2 border-white bg-blue-500 dark:border-zinc-900"></div>
                                    
                                    {{-- Content --}}
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                                        <div>
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                Status updated to <span class="font-bold uppercase text-blue-600 dark:text-blue-400">{{ $history->new_status }}</span>
                                            </div>
                                            <div class="mt-0.5 text-sm text-zinc-500">
                                                By {{ $history->changer->name ?? 'System' }}
                                            </div>
                                        </div>
                                        <div class="text-xs font-medium text-zinc-400 bg-zinc-100 dark:bg-zinc-800 px-2 py-1 rounded">
                                            {{ $history->created_at->format('M d, Y h:i A') }}
                                        </div>
                                    </div>

                                    {{-- Remarks (if any) --}}
                                    @if($history->remarks)
                                        <div class="mt-3 rounded-md bg-zinc-50 p-3 text-sm text-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-400 border border-zinc-100 dark:border-zinc-700/50">
                                            {{ $history->remarks }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="bg-zinc-100 dark:bg-zinc-800/50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <flux:icon.clock class="w-8 h-8 text-zinc-400" />
                            </div>
                            <flux:heading size="md">No History</flux:heading>
                            <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-2 max-w-sm mx-auto">No timeline updates have been recorded for this case yet.</p>
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>

    </div>
</x-layouts::app>