<x-layouts::app :title="__('Request Details')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('admin.requests.index') }}">Document Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>View</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $documentRequest->tracking_code }}</flux:breadcrumbs.item>
        </flux:breadcrumbs> 
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">

        {{-- Header with Back Button --}}
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="lg">Request Details</flux:heading>
                    @php
                        $color = match($documentRequest->status) {
                            'pending' => 'amber',
                            'processing' => 'blue',
                            'ready_for_pickup' => 'green',
                            'rejected' => 'red',
                            default => 'zinc',
                        };
                    @endphp
                    <flux:badge color="{{ $color }}">{{ ucfirst(str_replace('_', ' ', $documentRequest->status)) }}</flux:badge>
                </div>
                <flux:subheading>Tracking Code: <span class="font-mono text-zinc-900 dark:text-zinc-100">{{ $documentRequest->tracking_code }}</span></flux:subheading>
            </div>
            
            <flux:button href="{{ route('admin.requests.index') }}" icon="arrow-left" variant="ghost">Back to List</flux:button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- COLUMN 1: Request Information --}}
            <div class="lg:col-span-2 space-y-6">
                <flux:card>
                    <flux:heading size="md" class="mb-4">Document Information</flux:heading>
                    
                    <div class="space-y-4">
                        {{-- Type & Fee --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Document Type</flux:label>
                                <div class="font-medium text-lg">{{ $documentRequest->documentType->name ?? 'N/A' }}</div>
                            </flux:field>

                            <flux:field>
                                <flux:label>Processing Fee</flux:label>
                                <div class="font-medium text-lg">₱{{ number_format($documentRequest->documentType->fee ?? 0, 2) }}</div>
                            </flux:field>
                        </div>

                        <flux:separator />

                        {{-- Purpose --}}
                        <flux:field>
                            <flux:label>Purpose of Request</flux:label>
                            <div class="text-zinc-700 dark:text-zinc-300">
                                {{ $documentRequest->purpose }}
                            </div>
                        </flux:field>

                        <flux:separator />

                        {{-- Dates --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-zinc-500">Date Requested:</span>
                                <div class="font-medium">{{ $documentRequest->created_at->format('F d, Y h:i A') }}</div>
                            </div>
                            <div>
                                <span class="text-zinc-500">Last Updated:</span>
                                <div class="font-medium">{{ $documentRequest->updated_at->format('F d, Y h:i A') }}</div>
                            </div>
                        </div>
                    </div>
                </flux:card>

                {{-- Action Area --}}
                <flux:card>
                    <flux:heading size="md" class="mb-4">Actions</flux:heading>
                    <div class="flex flex-wrap gap-3">
                        {{-- Update Status Buttons --}}
                        @if($documentRequest->status === 'pending')
                            <form action="{{ route('requests.update-status', $documentRequest->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="processing">
                                
                                <flux:button type="submit" icon="arrow-path" class="w-full sm:w-auto">
                                    Mark as Processing
                                </flux:button>
                            </form>
                        @endif

                        @if($documentRequest->status === 'processing')
                            <form action="{{ route('requests.update-status', $documentRequest->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="ready_for_pickup">

                                <flux:button type="submit" icon="check-circle" variant="primary" class="w-full sm:w-auto">
                                    Ready for Pickup
                                </flux:button>
                            </form>
                        @endif

                        <div class="flex-1"></div>
                        
                        @if($documentRequest->status !== 'rejected')
                            <form action="{{ route('requests.update-status', $documentRequest->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">

                                <flux:button type="submit" icon="x-circle" variant="danger" class="w-full sm:w-auto">
                                    Reject Request
                                </flux:button>
                            </form>
                        @endif
                    </div>
                </flux:card>
            </div>

            {{-- COLUMN 2: Requestor Profile (Updated for Walk-ins) --}}
            <div class="lg:col-span-1">
                <flux:card>
                    <flux:heading size="md" class="mb-4">Requestor Profile</flux:heading>

                    @if($documentRequest->user)
                        {{-- ============================== --}}
                        {{-- SCENARIO A: REGISTERED USER    --}}
                        {{-- ============================== --}}
                        <div class="flex flex-col items-center text-center mb-6">
                            @php
                                $user = $documentRequest->user;
                                $residentProfile = $user->resident; // Assumes User -> belongsTo Resident
                                
                                // Fallback logic for name
                                if ($residentProfile && $residentProfile->fname) {
                                    $displayName = "{$residentProfile->fname} {$residentProfile->lname}";
                                } elseif ($user->name) {
                                    $displayName = $user->name;
                                } else {
                                    $displayName = $user->email;
                                }
                            @endphp
                            
                            <flux:avatar src="{{ $user->profile_photo_url ?? '' }}" initials="{{ substr($displayName, 0, 1) }}" size="xl" class="mb-3" />
                            
                            <div class="font-bold text-lg">{{ $displayName }}</div>
                            <div class="text-sm text-zinc-500">Registered Resident</div>
                            <div class="text-xs text-zinc-400 mt-1">{{ $user->email }}</div>
                        </div>

                        <flux:separator class="my-4" />

                        @if($residentProfile)
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">Address:</span>
                                    <span class="font-medium text-right">{{ $residentProfile->street }}, {{ $residentProfile->barangay }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">Phone:</span>
                                    <span class="font-medium">{{ $residentProfile->phone_number ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-zinc-500">Civil Status:</span>
                                    <span class="font-medium capitalize">{{ $residentProfile->civil_status ?? 'N/A' }}</span>
                                </div>
                                
                                {{-- Resident Tags --}}
                                <div class="pt-2 flex flex-wrap gap-2 justify-center">
                                    @if($residentProfile->senior_citizen) <flux:badge size="sm" color="indigo">Senior</flux:badge> @endif
                                    @if($residentProfile->is_4ps) <flux:badge size="sm" color="orange">4Ps</flux:badge> @endif
                                    @if($residentProfile->is_pwd) <flux:badge size="sm" color="blue">PWD</flux:badge> @endif
                                    @if($residentProfile->solo_parent) <flux:badge size="sm" color="pink">Solo Parent</flux:badge> @endif
                                </div>
                            </div>
                            
                            <div class="mt-6">
                                <flux:button href="#" variant="ghost" class="w-full text-xs">View Full Profile</flux:button>
                            </div>
                        @else
                            <div class="text-center text-zinc-500 italic text-sm">Profile incomplete (No Resident ID).</div>
                        @endif

                    @else
                        {{-- ============================== --}}
                        {{-- SCENARIO B: WALK-IN GUEST      --}}
                        {{-- ============================== --}}
                        <div class="flex flex-col items-center text-center mb-6">
                            {{-- Generic Icon for Walk-in --}}
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700 mb-3">
                                <flux:icon name="user" class="h-10 w-10 text-zinc-400" />
                            </div>
                            
                            <div class="font-bold text-lg">{{ $documentRequest->requestor_name ?: 'Unknown Guest' }}</div>
                            <div class="text-sm text-zinc-500">Walk-in Guest</div>
                        </div>

                        <flux:separator class="my-4" />

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-zinc-500">Phone:</span>
                                <span class="font-medium">{{ $documentRequest->requestor_phone ?? 'N/A' }}</span>
                            </div>
                            
                            <div class="flex flex-col gap-1">
                                <span class="text-zinc-500">Address:</span>
                                <span class="font-medium text-right">{{ $documentRequest->requestor_address ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endif

                </flux:card>
            </div>

        </div>
    </div>
</x-layouts::app>