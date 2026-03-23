<x-layouts::app :title="__('User Profile')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('users.index') }}">Users</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $user->email }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Determine Overall Verification Status for Headers --}}
    @php
        $isFullyVerified = $user->email_verified_at && $user->verification_status === 'verified';
        
        $statusColor = 'amber';
        $statusText = 'Pending';
        
        if ($isFullyVerified) {
            $statusColor = 'green';
            $statusText = 'Verified';
        } elseif ($user->verification_status === 'rejected') {
            $statusColor = 'red';
            $statusText = 'Rejected';
        } elseif (!$user->email_verified_at && $user->verification_status === 'verified') {
            $statusColor = 'amber';
            $statusText = 'Pending Email';
        } else {
            $statusColor = 'amber';
            $statusText = ucfirst($user->verification_status);
        }

        $roleColors = [
            'admin' => 'violet',
            'official' => 'blue',
            'resident' => 'green',
        ];
    @endphp

    {{-- Header & Actions --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" level="1">
                    {{ $user->email }}
                </flux:heading>
                
                {{-- Status Badge --}}
                <flux:badge rounded :color="$statusColor" size="sm">
                    {{ $statusText }}
                </flux:badge>

                {{-- Role Badge --}}
                <flux:badge rounded color="{{ $roleColors[$user->role] ?? 'zinc' }}" size="sm">
                    {{ ucfirst($user->role) }}
                </flux:badge>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if($user->resident && $user->resident->official)
                    <flux:button 
                        size="xs" 
                        variant="subtle" 
                        icon="shield-check"
                        href="{{ route('officials.show', $user->resident->official->id) }}"
                        class="text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20"
                    >
                        {{ $user->resident->official->currentTerm->position->title ?? 'Barangay Official' }}
                    </flux:button>
                @endif
            </div>
        </div>

        <flux:button href="{{ route('users.edit', $user->id) }}" variant="primary" icon="pencil">
            Edit
        </flux:button>            
    </div>

    {{-- Main Layout Container --}}
    <div class="space-y-10">

        {{-- Section 1: Account Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Account Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    System credentials, profile picture, and account status.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 pb-6">
                        {{-- Avatar --}}
                        <div class="shrink-0">
                            @if($user->profile_photo)
                                <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Profile" 
                                    class="w-20 h-20 object-cover rounded-full ring-4 ring-zinc-50 dark:ring-zinc-900 shadow-sm">
                            @else
                                <div class="w-20 h-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center ring-4 ring-zinc-50 dark:ring-zinc-900 shadow-sm">
                                    <span class="text-2xl text-zinc-400 font-bold">
                                        {{ $user->resident ? substr($user->resident->fname ?? 'U', 0, 1) : strtoupper(substr($user->email, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            <flux:heading size="md">{{ $user->email }}</flux:heading>
                            <flux:text variant="subtle" class="mt-1">User ID: {{ $user->id }}</flux:text>
                        </div>
                    </div>
                    <flux:separator />
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Account Role</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white capitalize">
                                {{ $user->role }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Account Status</dt>
                            <dd class="mt-2 text-sm font-medium text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400">
                                {{ $statusText }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Email Verified</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                @if($user->email_verified_at)
                                    <span class="text-green-600 dark:text-green-400 flex items-center gap-1">
                                        <flux:icon.check-circle class="w-4 h-4" />
                                        {{ $user->email_verified_at->format('M d, Y h:i A') }}
                                    </span>
                                @else
                                    <span class="text-amber-600 dark:text-amber-500">Unverified</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Account Approved</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                @if($user->account_verified_at)
                                    <span class="text-green-600 dark:text-green-400 flex items-center gap-1">
                                        <flux:icon.check-circle class="w-4 h-4" />
                                        {{ $user->account_verified_at->format('M d, Y h:i A') }}
                                    </span>
                                @else
                                    <span class="text-zinc-400">Not approved yet</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>
        
        {{-- Section 2: Linked Resident Data --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Linked Resident</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Personal information mapped from the barangay resident database.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    @if($user->resident)
                        <div class="flex justify-end mb-4">
                            <flux:button size="sm" variant="subtle" icon="arrow-top-right-on-square" href="{{ route('admin.residents.show', $user->resident->id) }}">
                                View Resident File
                            </flux:button>
                        </div>
                        <flux:separator />
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Full Name</dt>
                                <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $user->resident->full_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Date of Birth</dt>
                                <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $user->resident->birthdate ? \Carbon\Carbon::parse($user->resident->birthdate)->format('F d, Y') : 'N/A' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Resident Email</dt>
                                <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $user->email ?? 'N/A' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Phone Number</dt>
                                <dd class="mt-2 text-sm font-mono font-medium text-zinc-900 dark:text-white">
                                    {{ $user->resident->phone_number ?? 'N/A' }}
                                </dd>
                            </div>
                        </dl>
                    @else
                        <div class="text-center py-8">
                            <div class="bg-zinc-100 dark:bg-zinc-800/50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                                <flux:icon.user-minus class="w-8 h-8 text-zinc-400" />
                            </div>
                            <flux:heading size="md">No Resident Profile</flux:heading>
                            <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-2 max-w-sm mx-auto">This user account is not linked to any specific resident data record in the system.</p>
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>

        {{-- Section 3: Verification & Documents (Always visible) --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Account Verification</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Uploaded identification and verification status.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 border-amber-200 dark:border-amber-900/50 ring-1 ring-amber-500/20">
                    <div class="mb-6 rounded-xl border border-zinc-200 p-2 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/30 inline-block w-full sm:w-auto">
                        <h4 class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3 px-2 pt-2">Supporting Document</h4>
                        
                        @if($user->supporting_document)
                            {{-- Updated to use our new Secure Route! --}}
                            <a href="{{ route('users.document', $user->id) }}" target="_blank" class="block group relative w-full sm:w-72">
                                <img src="{{ route('users.document', $user->id) }}" alt="Supporting Document" class="w-full h-48 object-cover rounded-lg border border-zinc-200 dark:border-zinc-700 transition-transform group-hover:scale-[1.01]">
                                <div class="absolute inset-0 bg-zinc-900/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-lg backdrop-blur-sm">
                                    <div class="flex items-center gap-2 text-white">
                                        <flux:icon.arrows-pointing-out class="w-5 h-5" />
                                        <span class="text-sm font-medium">View Full</span>
                                    </div>
                                </div>
                            </a>
                        @else
                            <div class="py-10 px-6 sm:w-72 text-center bg-white dark:bg-zinc-900 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700">
                                <flux:icon.document-minus class="w-8 h-8 mx-auto text-zinc-400 mb-2" />
                                <span class="text-sm text-zinc-500">No document found or securely deleted.</span>
                            </div>
                        @endif
                    </div>

                    {{-- ONLY show the Approve/Reject buttons if the account is NOT verified --}}
                    @if($user->verification_status !== 'verified')
                        <div class="flex flex-wrap items-center gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                            <form action="{{ route('users.verify', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <flux:button type="submit" variant="primary" icon="check-circle">
                                    Approve Account
                                </flux:button>
                            </form>

                            <form action="{{ route('users.reject', $user->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <flux:button type="submit" variant="danger" icon="x-circle">
                                    Reject Account
                                </flux:button>
                            </form>
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>

        {{-- Section 4: Security & Sessions --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Security & Sessions</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Active devices and IP addresses currently logged into this account.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-0 overflow-hidden">
                    @php $sessions = $user->activeSessions(); @endphp
                    
                    @if($sessions->count() > 0)
                        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
                            @foreach($sessions as $session)
                                <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4 overflow-hidden">
                                        <div class="bg-zinc-100 dark:bg-zinc-800 p-2 rounded-full text-zinc-500 shrink-0">
                                            @if(str_contains(strtolower($session->user_agent), 'mobile') || str_contains(strtolower($session->user_agent), 'android') || str_contains(strtolower($session->user_agent), 'iphone'))
                                                <flux:icon.device-phone-mobile class="w-5 h-5" />
                                            @else
                                                <flux:icon.computer-desktop class="w-5 h-5" />
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $session->ip_address ?? 'Unknown IP' }}
                                                @if($session->id === request()->session()->getId())
                                                    <span class="ml-2 inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">This Device</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-zinc-500 truncate mt-0.5" title="{{ $session->user_agent }}">
                                                {{ $session->user_agent ?? 'Unknown Browser' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class=" sm:text-right shrink-0">
                                        <p class="text-xs text-zinc-500">Last Active</p>
                                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                            {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center">
                            <flux:icon.shield-exclamation class="w-8 h-8 mx-auto text-zinc-400 mb-2" />
                            <span class="text-sm text-zinc-500">No active sessions found.</span>
                        </div>
                    @endif
                </flux:card>
            </div>
        </div>

        {{-- Section 5: Danger Zone --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg" class="text-red-600 dark:text-red-500">Danger Zone</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Permanently delete this user account from the system. The resident profile will remain intact.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <x-warning-card 
                    title="Delete Account" 
                    description="Once you delete a user account, there is no going back. They will immediately lose access to the system."
                >
                    <flux:modal.trigger name="delete-user-{{ $user->id }}">
                        <flux:button variant="danger">Delete User</flux:button>
                    </flux:modal.trigger>
                </x-warning-card>
            </div>
        </div>

    </div>

    {{-- Modals --}}
    <x-delete-modal 
        name="delete-user-{{ $user->id }}" 
        action="{{ route('users.destroy', $user->id) }}"
    >
        This will permanently delete the account for <strong>{{ $user->email }}</strong>. 
        Any associated resident profile data will remain in the system, but this login credential will be destroyed. 
        This action cannot be undone.
    </x-delete-modal>

</x-layouts::app>