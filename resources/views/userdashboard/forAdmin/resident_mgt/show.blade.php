<x-layouts::app :title="__('Resident Profile')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('residents.index') }}">Residents</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>View</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $resident->full_name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

        {{-- Header & Actions --}}
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
            <div class="space-y-2">

                <flux:heading size="xl" level="1">
                    {{ $resident->fname }} {{ $resident->mname }} {{ $resident->lname }} {{ $resident->suffix }}
                </flux:heading>

                <div class="flex items-center gap-3">
                    @php
                        $statusColor = match($resident->status) {
                            'active' => 'green',
                            'pending' => 'yellow',
                            default => 'zinc',
                        };
                    @endphp

                    <flux:badge :color="$statusColor" size="sm" inset="top bottom">
                        {{ ucfirst($resident->status) }}
                    </flux:badge>

                    <flux:text variant="subtle" size="sm">ID: #{{ $resident->id }}</flux:text>
                </div>
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('residents.index') }}" variant="ghost" icon="arrow-left">
                    Back to List
                </flux:button>
                <flux:button href="{{ route('residents.edit', $resident->id) }}" variant="primary" icon="pencil">
                    Edit Profile
                </flux:button>            
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            
            {{-- Column 1: Main Info --}}
            <div class="space-y-6 lg:col-span-2">
                
                {{-- Personal Information Card --}}
                <flux:card class="p-0 overflow-hidden">
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <flux:heading size="lg">Personal Details</flux:heading>
                    </div>
                    
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Birthdate</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->format('F d, Y') : 'N/A' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Age</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->age . ' years old' : 'N/A' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Sex</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white capitalize">{{ $resident->sex ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Civil Status</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white capitalize">{{ $resident->civil_status ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Mother's Maiden Name</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">{{ $resident->mother_maiden_name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Contact Number</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">{{ $resident->phone_number ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                </flux:card>

                {{-- Address Card --}}
                <flux:card class="p-0 overflow-hidden">
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <flux:heading size="lg">Address & Household</flux:heading>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Full Address</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                    {{ $resident->street ? $resident->street . ', ' : '' }}
                                    {{ $resident->purok ? 'Purok ' . $resident->purok . ', ' : '' }}
                                    {{ $resident->sitio ? 'Sitio ' . $resident->sitio . ', ' : '' }}
                                    {{ $resident->barangay }}, {{ $resident->municipality }}, {{ $resident->province }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Zone</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">{{ $resident->zone ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Household ID</dt>
                                <dd class="mt-1 text-sm font-mono font-semibold text-blue-600 dark:text-blue-400">
                                    {{ $resident->household_id ?? 'No Household Assigned' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </flux:card>

            </div>

            {{-- Column 2: Side Panel (Socio-Economic & System Info) --}}
            <div class="space-y-6">
                
                {{-- Socio-Economic Profile --}}
                <flux:card class="p-0 overflow-hidden">
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                        <flux:heading size="lg">Socio-Economic</flux:heading>
                    </div>
                    <div class="px-6 py-4">
                        <div class="flex flex-wrap gap-2">
                            @php
                                $categories = [
                                    'solo_parent' => ['label' => 'Solo Parent', 'color' => 'blue'],
                                    'ofw' => ['label' => 'OFW', 'color' => 'indigo'],
                                    'is_pwd' => ['label' => 'PWD', 'color' => 'violet'],
                                    'is_4ps' => ['label' => '4Ps Beneficiary', 'color' => 'amber'],
                                    'senior_citizen' => ['label' => 'Senior Citizen', 'color' => 'orange'],
                                    'voter' => ['label' => 'Registered Voter', 'color' => 'green'],
                                    'out_of_school_children' => ['label' => 'Out of School Youth', 'color' => 'red'],
                                    'osa' => ['label' => 'OSA', 'color' => 'zinc'],
                                    'unemployed' => ['label' => 'Unemployed', 'color' => 'pink'],
                                    'laborforce' => ['label' => 'Labor Force', 'color' => 'cyan'],
                                ];
                                $hasCategories = false;
                            @endphp

                            @foreach ($categories as $field => $details)
                                @if ($resident->$field)
                                    @php $hasCategories = true; @endphp
                                    <flux:badge :color="$details['color']">
                                        {{ $details['label'] }}
                                    </flux:badge>
                                @endif
                            @endforeach

                            @if (!$hasCategories)
                                <flux:text variant="subtle" italic>No specific categories tagged.</flux:text>
                            @endif
                        </div>
                    </div>
                </flux:card>

                {{-- System Metadata --}}
                <flux:card class="bg-zinc-50 dark:bg-zinc-900">
                    <flux:heading size="sm" class="uppercase tracking-wider text-zinc-500 mb-3">System Record</flux:heading>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs">
                            <span class="text-zinc-500">Created:</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $resident->created_at->format('M d, Y H:i A') }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-zinc-500">Last Updated:</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $resident->updated_at->format('M d, Y H:i A') }}</span>
                        </div>
                    </div>
                </flux:card>

            </div>
        </div>
</x-layouts::app>