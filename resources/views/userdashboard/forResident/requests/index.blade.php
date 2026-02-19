<x-layouts::app :title="__('My Requests')">

    <x-slot:header>
        <flux:navbar scrollable>
            {{-- Document Requests Tab --}}
            <flux:navbar.item 
                href="{{ route('resident.requests.index', ['type' => 'documents']) }}" 
                :current="request('type', 'documents') === 'documents'"
                :badge="$pendingDocs > 0 ? $pendingDocs : null"
                icon="document-text"
            >
                My Requests
            </flux:navbar.item>

            {{-- Complaints Tab --}}
            <flux:navbar.item 
                href="{{ route('resident.requests.index', ['type' => 'complaints']) }}" 
                :current="request('type') === 'complaints'"
                :badge="$pendingComplaints > 0 ? $pendingComplaints : null"
                icon="exclamation-triangle"
            >
                My Complaints
            </flux:navbar.item>
        </flux:navbar>
    </x-slot:header>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">

        {{-- Page Title & Dynamic Buttons --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">
                    @if(request('type') === 'complaints')
                        My Complaints
                    @else
                        My Document Requests
                    @endif
                </flux:heading>
                <flux:subheading>
                    @if(request('type') === 'complaints')
                        Track the status of complaints you have filed.
                    @else
                        View the status and history of your requested documents.
                    @endif
                </flux:subheading>
            </div>

            {{-- DYNAMIC ACTION BUTTONS --}}
            <div>
                @if(request('type') === 'complaints')
                    {{-- Complaint Button --}}
                    <flux:button href="{{ route('resident.complaints.create') }}" variant="primary" icon="plus">
                        File Complaint
                    </flux:button>
                @else
                    {{-- Document Button (With Verification Check) --}}
                    @if(Auth::user()->verification_status === 'verified')
                        <flux:button href="{{ route('resident.requests.create') }}" variant="primary" icon="plus">
                            New Request
                        </flux:button>
                    @else
                        <flux:tooltip content="You must verify your account in Settings first.">
                            <flux:button href="{{ route('profile.edit') }}" variant="filled" class="bg-amber-100 text-amber-800 hover:bg-amber-200 border-amber-200">
                                <flux:icon.lock-closed class="w-4 h-4 mr-2" />
                                Verify to Request
                            </flux:button>
                        </flux:tooltip>
                    @endif
                @endif
            </div>
        </div>

        {{-- Main Content Card --}}
        <flux:card class="flex-1 p-0 overflow-hidden">
             
            {{-- Toolbar / Search --}}
            <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center border-b border-zinc-200 dark:border-zinc-700">
                <div class="relative w-full sm:w-80">
                    <form method="GET">
                        {{-- Keep the current tab active when searching --}}
                        <input type="hidden" name="type" value="{{ request('type', 'documents') }}">
                        <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search..." /> 
                    </form>
                </div>
            </div>

            {{-- ========================================== --}}
            {{-- 1. DOCUMENTS TABLE (Default)               --}}
            {{-- ========================================== --}}
            @if(request('type', 'documents') === 'documents')
                <flux:table :paginate="$documentRequests">
                    <flux:table.columns>
                        <flux:table.column>Tracking Code</flux:table.column>
                        <flux:table.column>Document Type</flux:table.column>
                        <flux:table.column>Purpose</flux:table.column>
                        <flux:table.column>Date Requested</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse($documentRequests as $req)
                            <flux:table.row :key="$req->id">
                                <flux:table.cell class="font-mono text-sm">{{ $req->tracking_code }}</flux:table.cell>
                                <flux:table.cell class="font-medium">{{ $req->documentType->name ?? 'Unknown Document' }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500 truncate max-w-[200px]">{{ $req->purpose ?? 'N/A' }}</flux:table.cell>
                                <flux:table.cell class="text-zinc-500">{{ $req->created_at->format('M d, Y') }}</flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $color = match($req->status) {
                                            'pending' => 'amber',
                                            'processing' => 'blue',
                                            'ready_for_pickup' => 'green',
                                            'completed' => 'zinc',
                                            'rejected' => 'red',
                                            default => 'zinc',
                                        };
                                    @endphp
                                    <flux:badge color="{{ $color }}" size="sm" inset="top bottom">
                                        {{ $req->status === 'ready_for_pickup' ? 'Ready for Pickup' : ucfirst($req->status) }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <flux:dropdown>
                                        <flux:button icon="ellipsis-horizontal" size="xs" variant="ghost" />
                                        <flux:menu>
                                            <flux:menu.item href="{{ route('resident.requests.show', $req->id) }}" icon="eye">View Details</flux:menu.item>
                                            @if($req->status === 'pending')
                                                <flux:menu.separator />
                                                <form action="{{ route('resident.requests.destroy', $req->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                    @csrf @method('DELETE')
                                                    <flux:menu.item type="submit" icon="trash" class="text-red-600 hover:bg-red-50">Cancel Request</flux:menu.item>
                                                </form> 
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6" class="text-center text-zinc-500 py-12">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <flux:icon name="document-text" class="h-8 w-8 text-zinc-300" />
                                        <p>You haven't made any document requests yet.</p>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

            {{-- ========================================== --}}
            {{-- 2. COMPLAINTS TABLE                        --}}
            {{-- ========================================== --}}
            @else
                <flux:table :paginate="$pendingComplaints instanceof \Illuminate\Pagination\LengthAwarePaginator ? $pendingComplaints : null">
                    <flux:table.columns>
                        <flux:table.column>Case Number</flux:table.column>
                        <flux:table.column>Respondent</flux:table.column>
                        <flux:table.column>Incident Date</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        {{-- Note: Ensure your controller passes $complaints variable --}}
                        @forelse($complaints as $complaint)
                            <flux:table.row :key="$complaint->id">
                                <flux:table.cell class="font-mono text-sm">
                                    {{ $complaint->case_number }}
                                </flux:table.cell>
                                
                                <flux:table.cell class="font-medium">
                                    {{ $complaint->respondent_name ?? 'N/A' }}
                                </flux:table.cell>

                                <flux:table.cell class="text-zinc-500">
                                    {{ \Carbon\Carbon::parse($complaint->incident_date)->format('M d, Y') }}
                                </flux:table.cell>

                                <flux:table.cell>
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
                                    <flux:badge color="{{ $statusColor }}" size="sm" inset="top bottom">
                                        {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell align="end">
                                    <flux:button href="{{ route('resident.complaints.show', $complaint->id) }}" size="xs" variant="ghost" icon="eye">
                                        View
                                    </flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center text-zinc-500 py-12">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <flux:icon name="exclamation-triangle" class="h-8 w-8 text-zinc-300" />
                                        <p>You haven't filed any complaints yet.</p>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            @endif

        </flux:card>
    </div>

</x-layouts::app>