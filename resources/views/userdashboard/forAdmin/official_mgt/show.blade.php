<x-layouts::app :title="$official->resident->full_name . ' - Official Profile'">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('admin.dashboard') }}">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('officials.index') }}">Current Officials</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $official->resident->full_name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Determine Status & Variables for Headers --}}
    @php
        $isActive = $official->currentTerm !== null;
        $statusColor = $isActive ? 'green' : 'zinc';
        $statusText = $isActive ? 'Active Duty' : 'Inactive';
        $positionTitle = $official->currentTerm?->position->title ?? 'No Active Position';
    @endphp

    {{-- Header & Actions --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" level="1">
                    {{ $official->resident->full_name }}
                </flux:heading>
                
                {{-- Status Badge --}}
                <flux:badge rounded :color="$statusColor" size="sm">
                    {{ $statusText }}
                </flux:badge>

                {{-- Position Badge --}}
                <flux:badge rounded color="blue" size="sm">
                    {{ $positionTitle }}
                </flux:badge>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <flux:button size="xs" variant="subtle" icon="user-circle" href="{{ route('users.show', $official->resident->user->id) }}" class="text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20">
                            {{ $official->resident->user->email }}
                </flux:button>
            </div>
        </div>

        <flux:button href="{{ route('officials.edit', $official->id) }}" variant="primary" icon="pencil">
            Edit
        </flux:button>            
    </div>

    {{-- Main Layout Container --}}
    <div class="space-y-10">

        {{-- Section 1: Official Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Official Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Profile picture, current role, and system account linkage.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 pb-6 border-b border-zinc-200 dark:border-zinc-800">
                        {{-- Avatar --}}
                        <div class="shrink-0">
                            @if($official->resident->profile_photo)
                                <img src="{{ asset('storage/' . $official->resident?->profile_photo) }}" alt="Profile" 
                                    class="w-20 h-20 object-cover rounded-full ring-4 ring-zinc-50 dark:ring-zinc-900 shadow-sm">
                            @else
                                <div class="w-20 h-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center ring-4 ring-zinc-50 dark:ring-zinc-900 shadow-sm">
                                    <span class="text-2xl text-zinc-400 font-bold">
                                        {{ $official->resident ? substr($official->resident->fname ?? 'U', 0, 1) : strtoupper(substr($official->email, 0, 1)) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            <flux:heading size="md">{{ $official->resident->full_name }}</flux:heading>
                            <flux:text variant="subtle" class="mt-1">Official ID: {{ $official->id }}</flux:text>
                        </div>
                    </div>
                    
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Current Position</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $positionTitle }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Account Status</dt>
                            <dd class="mt-2 text-sm font-medium text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400">
                                {{ $statusText }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">System Account</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                @if($official->user ?? $official->resident->user)
                                    <span class="flex items-center gap-1">
                                        <flux:icon.link class="w-4 h-4 text-zinc-400" />
                                        {{ $official->user->email ?? $official->resident->user->email }}
                                    </span>
                                @else
                                    <span class="text-zinc-400 italic">No system account linked</span>
                                @endif
                            </dd>
                        </div>
                        {{-- NEW: Digital Signature Display --}}
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Digital Signature</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                @if($official->e_signature_path)
                                    <div class="p-2 border rounded-lg bg-zinc-50 border-zinc-200 dark:bg-zinc-800/50 dark:border-zinc-700 w-max">
                                        <img src="{{ Storage::url($official->e_signature_path) }}" alt="E-Signature" class="object-contain h-12 max-w-full mix-blend-multiply dark:mix-blend-normal">
                                    </div>
                                @else
                                    <span class="text-zinc-400 italic">No signature uploaded</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>

        {{-- Section 2: Term Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Term Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Timeline and assignment duration for the current term.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Date Started</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                @if($official->currentTerm?->term_start)
                                    {{ \Carbon\Carbon::parse($official->currentTerm->term_start)->format('F d, Y') }}
                                @else
                                    <span class="text-zinc-500">N/A</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Date Ended</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                @if($official->currentTerm?->term_end)
                                    {{ \Carbon\Carbon::parse($official->currentTerm->term_end)->format('F d, Y') }}
                                @elseif($official->currentTerm)
                                    <span class="text-zinc-500">Present (Indefinite)</span>
                                @else
                                    <span class="text-zinc-500">N/A</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>

        {{-- Section 3: Linked Resident Data --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Personal Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Personal details mapped from the linked barangay resident database.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="flex justify-end mb-4">
                        <flux:button size="sm" variant="subtle" icon="arrow-top-right-on-square" href="{{ route('admin.residents.show', $official->resident->id) }}">
                            View Resident File
                        </flux:button>
                    </div>
                    <flux:separator />
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2 mt-6">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Full Name</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $official->resident->full_name }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Date of Birth</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $official->resident->birthdate ? \Carbon\Carbon::parse($official->resident->birthdate)->format('F d, Y') : 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Resident Email</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $official->resident->user?->email ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Contact Number</dt>
                            <dd class="mt-2 text-sm font-mono font-medium text-zinc-900 dark:text-white">
                                {{ $official->resident->phone_number ?? 'Not provided' }}
                            </dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>

        {{-- Section 4: Danger Zone --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg" class="text-red-600 dark:text-red-500">Danger Zone</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Permanently delete this official record from the system. The resident profile will remain intact.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <x-warning-card 
                    title="Delete Official Record" 
                    description="Once you delete this official record, there is no going back. Their administrative access and history may be affected."
                >
                    <flux:modal.trigger name="delete-official-{{ $official->id }}">
                        <flux:button variant="danger">Delete Record</flux:button>
                    </flux:modal.trigger>
                </x-warning-card>
            </div>
        </div>

    </div>

    {{-- Modals --}}
    <x-delete-modal 
        name="delete-official-{{ $official->id }}" 
        action="{{ route('officials.destroy', $official->id) }}"
    >
        This will permanently delete the official record for <strong>{{ $official->resident->full_name }}</strong>. 
        Any associated resident profile data will remain in the system, but their status as an official will be destroyed. 
        This action cannot be undone.
    </x-delete-modal>

</x-layouts::app>