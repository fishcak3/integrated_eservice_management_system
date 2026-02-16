<x-layouts::app :title="__('Requests Management')">

    <x-slot:header>
        <flux:navbar scrollable>
            <flux:navbar.item 
                href="{{ route('admin.requests.index', ['type' => 'documents']) }}" 
                :current="request('type', 'documents') === 'documents'"
                :badge="$pendingDocs > 0 ? $pendingDocs : null"
                icon="document-text"
                wire:navigate
            >
                Document Requests
            </flux:navbar.item>

            <flux:navbar.item 
                href="{{ route('admin.requests.index', ['type' => 'complaints']) }}" 
                :current="request('type') === 'complaints'"
                :badge="$pendingComplaints > 0 ? $pendingComplaints : null"
                icon="exclamation-triangle"
                wire:navigate
            >
                Complaints
            </flux:navbar.item>
        </flux:navbar>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">

        {{-- Page Title --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">Request Management</flux:heading>
                <flux:subheading>Manage resident document requests and blotter complaints.</flux:subheading>
            </div>

                {{-- Dynamic Action Button --}}
                @if(request('type') === 'complaints')
                    <flux:button href="{{ route('complaints.create') }}" variant="primary" icon="plus">
                        File Complaint
                    </flux:button>
                @else
                    <flux:button href="{{ route('admin.requests.create') }}" variant="primary" icon="plus">
                        New Request
                    </flux:button>
                @endif
        </div>

        {{-- Content Card --}}
        <flux:card class="flex-1 p-0 overflow-hidden">
            
            {{-- Toolbar --}}
            <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center border-b border-zinc-200 dark:border-zinc-700">
                <div class="relative w-full sm:w-80">
                    <form method="GET">
                        <input type="hidden" name="type" value="{{ request('type', 'documents') }}">
                        <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search requests..." class="max-w-sm"/> 
                    </form>
                </div>

            </div>

            {{-- 1. DOCUMENTS TABLE --}}
            @if(request('type', 'documents') === 'documents')
                <flux:table :paginate="$documentRequests">
                    <flux:table.columns>
                        <flux:table.column>Tracking Code</flux:table.column>
                        <flux:table.column>Requestor</flux:table.column>
                        <flux:table.column>Type</flux:table.column>
                        <flux:table.column>Date</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse($documentRequests as $req)
                            <flux:table.row :key="$req->id">
                                <flux:table.cell class="font-mono text-sm">{{ $req->tracking_code }}</flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-3">
                                        <flux:avatar src="{{ $req->user->profile_photo_url ?? '' }}" initials="{{ substr($req->user->email ?? '?', 0, 1) }}" size="xs" />
                                        <div>
                                            <div class="font-medium">
                                                {{ $req->user->resident->formatted_name ?? ($req->requestor_name ?: 'Unknown') }}
                                            </div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $req->user ? 'Registered Resident' : 'Walk-in Guest' }}
                                            </div>
                                        </div>
                                    </div>
                                </flux:table.cell>
                                <flux:table.cell>{{ $req->documentType->name ?? 'Unknown' }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $req->created_at->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $color = match($req->status) {
                                            'pending' => 'amber',
                                            'processing' => 'blue',
                                            'ready_for_pickup' => 'green',
                                            'rejected' => 'red',
                                            default => 'zinc',
                                        };
                                    @endphp
                                    <flux:badge :color="$color" size="sm" inset="top bottom">{{ ucfirst(str_replace('_', ' ', $req->status)) }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:dropdown>
                                        <flux:button icon="ellipsis-horizontal" size="xs" variant="ghost" />
                                        <flux:menu>
                                            <flux:menu.item href="{{ route('admin.requests.show', $req->id) }}" icon="eye">View Details</flux:menu.item>
                                            @if(in_array($req->status, ['pending', 'processing']))
                                                <flux:menu.separator />
                                                <form action="{{ route('requests.update-status', $req->id) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="ready_for_pickup">
                                                    <flux:menu.item type="submit" icon="check-circle" class="text-green-600">Mark Ready</flux:menu.item>
                                                </form>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6" class="text-center text-zinc-500 py-6">No document requests found.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

            {{-- 2. COMPLAINTS TABLE --}}
            @else
                <flux:table :paginate="$complaints">
                    <flux:table.columns>
                        <flux:table.column>Case #</flux:table.column>
                        <flux:table.column>Complainant</flux:table.column>
                        <flux:table.column>Respondent</flux:table.column>
                        <flux:table.column>Severity</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse($complaints as $case)
                            <flux:table.row :key="$case->id">
                                <flux:table.cell class="font-mono text-sm">{{ $case->case_number }}</flux:table.cell>
                                <flux:table.cell class="font-medium">{{ $case->complainant?->name ?? 'Unknown' }}</flux:table.cell>
                                <flux:table.cell>{{ $case->respondent_name ?? 'N/A' }}</flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $sevColor = match($case->type?->severity_level ?? 'low') {
                                            'critical' => 'red',
                                            'high' => 'orange',
                                            'medium' => 'yellow',
                                            default => 'green',
                                        };
                                    @endphp
                                    <flux:badge :color="$sevColor" size="sm">{{ ucfirst($case->type->severity_level ?? 'low') }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="zinc" size="sm" inset="top bottom">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button href="{{ route('complaints.show', $case->id) }}" size="xs">Manage</flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6" class="text-center text-zinc-500 py-6">No complaints found.</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

    </div>
</x-layouts::app>