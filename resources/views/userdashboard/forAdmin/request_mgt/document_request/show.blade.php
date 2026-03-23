<x-layouts::app :title="__('Request Details')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.documents.index') }}">Document Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $documentRequest->tracking_code }}</flux:breadcrumbs.item>
        </flux:breadcrumbs> 
    </x-slot>

    @php
        $statusColor = match($documentRequest->status) {
            'pending' => 'amber',
            'processing' => 'blue',
            'pending_e_signature' => 'purple',
            'ready_for_pickup' => 'green',
            'rejected' => 'red',
            default => 'zinc',
        };
        $statusText = ucfirst(str_replace('_', ' ', $documentRequest->status));
    @endphp

    {{-- Header & Actions --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" level="1">
                    Request Details
                </flux:heading>
                
                {{-- Status Badge --}}
                <flux:badge rounded :color="$statusColor" size="sm">
                    {{ $statusText }}
                </flux:badge>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:text variant="subtle">
                    Tracking Code: <span class="font-mono font-medium text-zinc-900 dark:text-zinc-100">{{ $documentRequest->tracking_code }}</span>
                </flux:text>
            </div>
        </div>

        <div class="flex items-center gap-2">
            
            {{-- 1. PENDING STATE ACTIONS --}}
            @if($documentRequest->status === 'pending')
                <flux:button href="{{ route('admin.documents.process', $documentRequest->id) }}" variant="primary" icon="play">
                    Process
                </flux:button>

                <form action="{{ route('admin.documents.update-status', $documentRequest->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <flux:button type="submit" variant="danger" icon="x-circle">
                        Reject
                    </flux:button>
                </form>

            {{-- 2. PROCESSING STATE ACTIONS --}}
            @elseif($documentRequest->status === 'processing')
                
                <div class="flex items-center gap-2 bg-zinc-50 dark:bg-zinc-800/50 p-2 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:text variant="subtle" class="text-sm mr-2">Signature Routing:</flux:text>

                    {{-- Option A: Captain is AWAY --}}
                    {{-- Ideally changes status to 'pending_e_signature' so the Cap knows what to sign --}}
                    <form action="{{ route('admin.documents.request-e-sign', $documentRequest->id) }}" method="POST" class="inline">
                        @csrf
                        <flux:button type="submit" variant="primary" icon="paper-airplane">
                            Request E-Sign (Cap is Away)
                        </flux:button>
                    </form>

                    {{-- Option B: Captain is PRESENT --}}
                    {{-- Prints the document and moves status directly to ready_for_pickup --}}
                    <form action="{{ route('admin.documents.update-status', $documentRequest->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="ready_for_pickup">
                        <flux:button type="submit" variant="subtle" icon="printer">
                            Print for Manual Sign
                        </flux:button>
                    </form>
                </div>

                {{-- Reject Button --}}
                <form action="{{ route('admin.documents.update-status', $documentRequest->id) }}" method="POST" class="inline ml-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="rejected">
                    <flux:button type="submit" variant="danger" icon="x-circle">
                        Reject
                    </flux:button>
                </form>

           {{-- 3. PENDING E-SIGNATURE STATE ACTIONS (New!) --}}
            @elseif($documentRequest->status === 'pending_e_signature')
                
                {{-- If the logged-in user is the Captain --}}
                @if(auth()->user()->isCurrentOfficialPosition('Barangay Captain'))
                    <form action="{{ route('admin.documents.approve-sign', $documentRequest->id) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <flux:button type="submit" variant="primary" icon="check-badge">
                            Approve & Attach Signature
                        </flux:button>
                    </form>
                    
                {{-- If the logged-in user is a Secretary/Admin --}}
                @else
                    <div class="flex items-center gap-2 bg-amber-50 dark:bg-amber-900/30 p-2 rounded-lg border border-amber-200 dark:border-amber-700/50">
                        <flux:icon name="clock" class="w-5 h-5 text-amber-500" />
                        <flux:text variant="subtle" class="text-sm font-medium text-amber-700 dark:text-amber-400">
                            Waiting for Captain's Signature
                        </flux:text>
                    </div>
                @endif

            {{-- 4. READY FOR PICKUP STATE ACTIONS --}}
            @elseif($documentRequest->status === 'ready_for_pickup')
                <form action="{{ route('admin.documents.update-status', $documentRequest->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <flux:button type="submit" variant="primary" icon="flag">
                        Mark as Completed
                    </flux:button>
                </form>
            @endif

            {{-- Persistent Actions (Always visible, pushed to the right) --}}
            <flux:button href="{{ route('admin.documents.preview', $documentRequest->id) }}" target="_blank" variant="subtle" icon="eye">
                Preview
            </flux:button>
            <flux:button href="{{ route('admin.documents.edit', $documentRequest->id) }}" variant="subtle" icon="pencil">Edit</flux:button>
        </div>
    </div>

    {{-- Main Layout Container --}}
    <div class="space-y-10">

        {{-- Section 1: Document Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Document Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Type, processing fee, purpose, and timeline details.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Document Type</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $documentRequest->documentType->name ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Processing Fee</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                ₱{{ number_format($documentRequest->documentType->fee ?? 0, 2) }}
                            </dd>
                        </div>
                        
                        <div class="sm:col-span-2">
                            <flux:separator class="mb-6" />
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Purpose of Request</dt>
                            <dd class="mt-2 text-sm text-zinc-900 dark:text-white">
                                {{ $documentRequest->purpose }}
                            </dd>
                        </div>

                        <div class="sm:col-span-2">
                            <flux:separator class="mb-6" />
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Date Requested</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $documentRequest->created_at->format('F d, Y h:i A') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Last Updated</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $documentRequest->updated_at->format('F d, Y h:i A') }}
                            </dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>

        {{-- Section 1.5: Assignment Form --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Assignment</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Assign a barangay official to process and handle this document request.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <form action="{{ route('admin.documents.assign', $documentRequest->id) }}" method="POST" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        @csrf
                        @method('PATCH')
                        <div class="w-full flex-1">
                            <flux:label>Assigned Official</flux:label>
                            <flux:select name="assigned_official_id">
                                <option value="">Unassigned (Select an official...)</option>
                                @foreach($officials as $official)
                                    <option value="{{ $official->id }}" @selected($documentRequest->assigned_official_id == $official->id)>
                                        {{ $official->name }}
                                    </option>
                                @endforeach
                            </flux:select>
                        </div>
                        <flux:button type="submit" variant="outline">Update Assignment</flux:button>
                    </form>
                </flux:card>
            </div>
        </div>

        {{-- Section 2: Requestor Profile --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Requestor Profile</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Information about the individual requesting this document.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    @if($documentRequest->user)
                        {{-- REGISTERED USER --}}
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 pb-6 border-b border-zinc-200 dark:border-zinc-800">
                            @php
                                $user = $documentRequest->user;
                                $residentProfile = $user->resident; 
                                
                                if ($residentProfile && $residentProfile->fname) {
                                    $displayName = "{$residentProfile->fname} {$residentProfile->lname}";
                                } elseif ($user->name) {
                                    $displayName = $user->name;
                                } else {
                                    $displayName = $user->email;
                                }
                            @endphp
                            
                            <flux:avatar src="{{ $user->profile_photo_url ?? '' }}" initials="{{ substr($displayName, 0, 1) }}" size="xl" class="shrink-0" />
                            
                            <div>
                                <flux:heading size="md">{{ $displayName }}</flux:heading>
                                <div class="flex items-center gap-2 mt-1">
                                    <flux:badge rounded color="blue" size="sm">Registered Resident</flux:badge>
                                </div>
                                <flux:text variant="subtle" class="mt-1">{{ $user->email }}</flux:text>
                            </div>
                        </div>

                        @if($residentProfile)
                            <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Address</dt>
                                    <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                        House Num. {{ $residentProfile->household_id ?? 'N/A' }}, Sitio {{ $residentProfile->sitio ? $residentProfile->sitio . ', ' : '' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone Number</dt>
                                    <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $residentProfile->phone_number ?? 'N/A' }}
                                    </dd>
                                </div>
                            </dl>
                            <div class="mt-6">
                                <flux:button href="{{ route('admin.residents.show', $residentProfile->id) }}" icon="user" variant="subtle" size="sm">View Full Profile</flux:button>
                            </div>
                        @else
                            <div class="text-zinc-500 dark:text-zinc-400 italic text-sm">Profile incomplete (No Resident ID linked).</div>
                        @endif

                    @elseif($documentRequest->resident)
                        {{-- REGISTERED RESIDENT WITHOUT ACCOUNT --}}
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 pb-6 border-b border-zinc-200 dark:border-zinc-800">
                            @php
                                $resident = $documentRequest->resident;
                                $displayName = ($resident->fname && $resident->lname) ? "{$resident->fname} {$resident->lname}" : "Resident #{$resident->id}";
                            @endphp
                            
                            <flux:avatar initials="{{ substr($displayName, 0, 1) }}" size="xl" class="shrink-0" />
                            
                            <div>
                                <flux:heading size="md">{{ $displayName }}</flux:heading>
                                <div class="flex items-center gap-2 mt-1">
                                    <flux:badge rounded color="zinc" size="sm">Registered Resident (No Account)</flux:badge>
                                </div>
                            </div>
                        </div>

                        <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Address</dt>
                                <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                    House Num. {{ $resident->household_id ?? 'N/A' }}, Sitio {{ $resident->sitio ? $resident->sitio . ', ' : '' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone Number</dt>
                                <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $resident->phone_number ?? 'N/A' }}
                                </dd>
                            </div>
                        </dl>

                        <div class="mt-6">
                            <flux:button href="{{ route('admin.residents.show', $resident->id) }}" icon="user" variant="subtle" size="sm">View Full Profile</flux:button>
                        </div>

                    @else
                        {{-- WALK-IN GUEST --}}
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 pb-6 border-b border-zinc-200 dark:border-zinc-800">
                            <flux:avatar initials="{{ substr($documentRequest->guest_name ?? 'W', 0, 1) }}" size="xl" class="shrink-0" />
                            
                            <div>
                                <flux:heading size="md">{{ $documentRequest->guest_name ?? 'Walk-In Guest' }}</flux:heading>
                                <div class="flex items-center gap-2 mt-1">
                                    <flux:badge rounded color="zinc" size="sm">Unregistered Walk-In</flux:badge>
                                </div>
                            </div>
                        </div>

                        <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Guest Email / Phone</dt>
                                <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $documentRequest->guest_contact ?? 'No contact info provided' }}
                                </dd>
                            </div>
                        </dl>
                    @endif
                </flux:card>
            </div>
        </div>

        {{-- Section 3: Attachments --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Attachments</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Files and supporting documents provided by the requestor.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    @if($documentRequest->attachments && $documentRequest->attachments->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($documentRequest->attachments as $attachment)
                                <div class="flex items-center justify-between p-3 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="bg-zinc-200 dark:bg-zinc-700 p-2 rounded-full shrink-0">
                                            <flux:icon name="paper-clip" class="h-4 w-4 text-zinc-600 dark:text-zinc-300" />
                                        </div>
                                        <div class="truncate">
                                            <div class="font-medium text-sm text-zinc-900 dark:text-zinc-100 truncate" title="{{ $attachment->file_name }}">
                                                {{ $attachment->file_name ?? 'Attached File' }}
                                            </div>
                                            <div class="text-xs text-zinc-500 uppercase">
                                                {{ explode('/', $attachment->file_type)[1] ?? 'FILE' }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <flux:button href="{{ Storage::url($attachment->file_path) }}" target="_blank" icon="arrow-down-tray" variant="ghost" size="sm" class="shrink-0" aria-label="Download" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="bg-zinc-100 dark:bg-zinc-800/50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <flux:icon.document-minus class="w-8 h-8 text-zinc-400" />
                            </div>
                            <flux:heading size="md">No Attachments</flux:heading>
                            <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-2 max-w-sm mx-auto">There are no files attached to this document request.</p>
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>

        {{-- Section 4: Audit Trail --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Audit Trail</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    A chronological record of all actions, status updates, and changes made to this document request.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-0 overflow-hidden">
                    @if($auditLogs->count() > 0)
                        <ul class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($auditLogs as $log)
                                <li class="p-6 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 mb-2">
                                        <div>
                                            <flux:heading size="sm" class="font-semibold text-zinc-900 dark:text-white">
                                                {{ $log->description }}
                                            </flux:heading>
                                            <flux:text variant="subtle" class="text-xs mt-1">
                                                Performed by: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $log->causer->name ?? 'System/Automated' }}</span>
                                            </flux:text>
                                        </div>
                                        <flux:text variant="subtle" class="text-xs whitespace-nowrap">
                                            {{ $log->created_at->format('M d, Y - h:i A') }}
                                        </flux:text>
                                    </div>

                                    {{-- Display specifically what changed (Old vs New) --}}
                                    @if(isset($log->properties['old']) && isset($log->properties['attributes']))
                                        <div class="mt-3 bg-zinc-100 dark:bg-zinc-800 rounded-md p-3 text-sm">
                                            <span class="font-medium text-zinc-700 dark:text-zinc-300 text-xs uppercase tracking-wider mb-2 block">Data Changes:</span>
                                            <ul class="space-y-1">
                                                @foreach($log->properties['attributes'] as $key => $newValue)
                                                    @php $oldValue = $log->properties['old'][$key] ?? 'None'; @endphp
                                                    <li class="flex items-center gap-2 text-zinc-600 dark:text-zinc-400">
                                                        <span class="font-mono text-xs">{{ $key }}:</span>
                                                        <span class="line-through text-red-500/70">{{ $oldValue }}</span>
                                                        <flux:icon name="arrow-right" class="w-3 h-3" />
                                                        <span class="text-green-600 dark:text-green-400 font-medium">{{ $newValue }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-6 text-center text-zinc-500 dark:text-zinc-400 text-sm">
                            No activity recorded yet.
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>

    </div>
</x-layouts::app>