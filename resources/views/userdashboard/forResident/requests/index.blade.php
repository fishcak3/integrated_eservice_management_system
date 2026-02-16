<x-layouts::app :title="__('My Requests')">

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">

        {{-- Header Section --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">My Document Requests</flux:heading>
                <flux:subheading>View the status and history of your requested documents.</flux:subheading>
            </div>

            {{-- Create Button (Prominent for Residents) --}}
            <flux:button href="{{ route('resident.requests.create') }}" variant="primary" icon="plus">
                New Request
            </flux:button>
        </div>

        {{-- Main Content Card --}}
        <flux:card class="flex-1 p-0 overflow-hidden">
            
            {{-- Toolbar --}}
            <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center border-b border-zinc-200 dark:border-zinc-700">
                <div class="relative w-full sm:w-80">
                    <form method="GET">
                        <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search by tracking code..." /> 
                    </form>
                </div>
            </div>

            {{-- DOCUMENTS TABLE --}}
            <flux:table :paginate="$documentRequests">
                <flux:table.columns>
                    <flux:table.column>Tracking Code</flux:table.column>
                    <flux:table.column>Document Type</flux:table.column>
                    <flux:table.column>Purpose</flux:table.column> {{-- Added Purpose for context --}}
                    <flux:table.column>Date Requested</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($documentRequests as $req)
                        <flux:table.row :key="$req->id">
                            {{-- Tracking Code --}}
                            <flux:table.cell class="font-mono text-sm">
                                {{ $req->tracking_code }}
                            </flux:table.cell>
                            
                            {{-- Document Type --}}
                            <flux:table.cell class="font-medium">
                                {{ $req->type->name ?? 'Unknown Document' }}
                            </flux:table.cell>

                            {{-- Purpose --}}
                            <flux:table.cell class="text-zinc-500 truncate max-w-[200px]">
                                {{ $req->purpose ?? 'N/A' }}
                            </flux:table.cell>

                            {{-- Date --}}
                            <flux:table.cell class="text-zinc-500">
                                {{ $req->created_at->format('M d, Y') }}
                            </flux:table.cell>

                            {{-- Status Badge --}}
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
                                    
                                    $statusLabel = match($req->status) {
                                        'ready_for_pickup' => 'Ready for Pickup',
                                        default => ucfirst($req->status),
                                    };
                                @endphp
                                <flux:badge color="{{ $color }}" size="sm" inset="top bottom">
                                    {{ $statusLabel }}
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="xs" variant="ghost" />
                                    <flux:menu>
                                        {{-- View Details --}}
                                        <flux:menu.item href="{{ route('resident.requests.show', $req->id) }}" icon="eye">
                                            View Details
                                        </flux:menu.item>

                                        @if($req->status === 'pending')
                                            <flux:menu.separator />

                                            <form action="{{ route('resident.requests.destroy', $req->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                                @csrf @method('DELETE')
                                                <flux:menu.item type="submit" icon="trash" class="text-red-600 hover:bg-red-50">
                                                    Cancel Request
                                                </flux:menu.item>
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
                                    <p>You haven't made any requests yet.</p>
                                    <flux:button href="{{ route('resident.requests.create') }}" size="sm" variant="outline">
                                        Create your first request
                                    </flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

</x-layouts::app>