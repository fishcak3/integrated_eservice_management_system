<x-layouts::app :title="__('Resident Profile')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.residents.index') }}">Residents</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $resident->fname }} {{ $resident->lname }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Header & Actions --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1">
                    {{ $resident->fname }} {{ $resident->mname }} {{ $resident->lname }} {{ $resident->suffix }}
                </flux:heading>
                
                {{-- Status Badge --}}
                @php
                    $statusColors = [
                        'active' => 'green',
                        'inactive' => 'zinc',
                        'pending' => 'amber',
                        'deceased' => 'red',
                        'transferred' => 'blue',
                    ];
                @endphp
                <flux:badge rounded :color="$statusColors[$resident->status] ?? 'zinc'" class="capitalize" size="sm">
                    {{ $resident->status }}
                </flux:badge>
            </div>

            {{-- Connected Accounts / Official Status --}}
            <div class="flex flex-wrap items-center gap-2">
                @if($resident->official && $resident->official->currentTerm && $resident->official->currentTerm->position)
                    <flux:button 
                        size="xs" 
                        variant="subtle" 
                        icon="shield-check"
                        href="{{ route('officials.show', $resident->official->id) }}"
                        class="text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20"
                    >
                        {{ $resident->official->currentTerm->position->title }}
                    </flux:button>
                @endif

                @if($resident->user)
                    <flux:button size="xs" variant="subtle" icon="user-circle" href="{{ route('users.show', $resident->user->id) }}" class="text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20">
                        {{ $resident->user->email }}
                    </flux:button>
                @endif
            </div>

        </div>

        <flux:button href="{{ route('admin.residents.edit', $resident->id) }}" variant="primary" icon="pencil">
            Edit
        </flux:button>            
    </div>

    {{-- Main Layout Container --}}
    <div class="space-y-10">

        {{-- Section 1: Personal Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Personal Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Basic identifying information and contact details of the resident.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Birthdate</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $resident->birthdate ? \Carbon\Carbon::parse($resident->birthdate)->format('F d, Y') : 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Age</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $resident->age ?? 'N/A' }}
                            </dd>
                        </div>
                        
                        {{-- Added Birth Place and Citizenship --}}
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Place of Birth</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $resident->birth_place ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Citizenship</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white capitalize">
                                {{ $resident->citizenship ?? 'N/A' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Sex</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white capitalize">{{ $resident->sex ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Civil Status</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white capitalize">{{ $resident->civil_status ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Mother's Maiden Name</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">{{ $resident->mother_maiden_name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Contact Number</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">{{ $resident->phone_number ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>
        
        {{-- Section 2: Address & Household Data --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Address & Household</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Information about where the resident currently resides and their household status within the barangay.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">

                        {{-- Household Details --}}
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Household Number</dt>
                            <dd class="mt-2 text-sm font-mono font-semibold text-blue-600 dark:text-blue-400">
                                {{ $resident->household->household_number ?? 'No Household Assigned' }}
                            </dd>
                        </div>
                
                        {{-- Address Span --}}
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Full Address</dt>
                            <dd class="mt-2 text-sm font-medium leading-relaxed text-zinc-900 dark:text-white">
                                @if($resident->household)
                                    {{ $resident->household->address ? $resident->household->address . ', ' : '' }}
                                    Sitio {{ $resident->household->sitio ? $resident->household->sitio . ', ' : '' }}
                                @endif
                                Brgy. {{ $global_brgy_name ?? 'Unknown' }}, 
                                {{ $global_municipality ?? 'Unknown' }}, 
                                {{ $global_province ?? 'Unknown' }}, <br>
                                {{ $global_region ?? 'Unknown' }}, Philippines {{ $global_postal_code ?? '' }}
                            </dd>
                        </div>

                    </dl>
                </flux:card>
            </div>
        </div>
        
        {{-- Section 3: Socio-Economic Profile --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Socio-Economic</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Sectors, categories, and special classifications the resident belongs to.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="flex flex-wrap gap-2">
                        @php
                            $categories = [
                                'solo_parent' => ['label' => 'Solo Parent', 'color' => 'blue'],
                                'ofw' => ['label' => 'OFW', 'color' => 'indigo'],
                                'is_pwd' => ['label' => 'PWD', 'color' => 'violet'],
                                'is_4ps_grantee' => ['label' => '4Ps Beneficiary', 'color' => 'amber'],
                                'senior_citizen' => ['label' => 'Senior Citizen', 'color' => 'orange'],
                                'voter' => ['label' => 'Registered Voter', 'color' => 'green'],
                                'out_of_school_children' => ['label' => 'Out of School Youth', 'color' => 'red'],
                                'osa' => ['label' => 'OSA', 'color' => 'zinc'],
                                'unemployed' => ['label' => 'Unemployed', 'color' => 'pink'],
                                'laborforce' => ['label' => 'Labor Force', 'color' => 'cyan'],
                                'isy_isc' => ['label' => 'ISY / ISC', 'color' => 'teal'],
                            ];
                            $hasCategories = false;
                        @endphp

                        @foreach ($categories as $field => $details)
                            @if ($resident->$field)
                                @php $hasCategories = true; @endphp
                                <flux:badge rounded :color="$details['color']">
                                    {{ $details['label'] }}
                                </flux:badge>
                            @endif
                        @endforeach

                        @if (!$hasCategories)
                            <flux:text variant="subtle" italic>No specific categories tagged.</flux:text>
                        @endif
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Section 4: Danger Zone --}}
            <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
                <div class="px-4 sm:px-0">
                    <flux:heading size="lg" class="text-red-600 dark:text-red-500">Danger Zone</flux:heading>
                    <flux:text variant="subtle" class="mt-1">
                        Permanently delete this resident from the system. This action cannot be undone.
                    </flux:text>
                </div>

                <div class="md:col-span-2">
                    <x-warning-card 
                        title="Delete Resident" 
                        description="Once you delete a resident, there is no going back. Please be certain."
                    >
                        <flux:modal.trigger name="delete-resident-{{ $resident->id }}">
                            <flux:button variant="danger">Delete Resident</flux:button>
                        </flux:modal.trigger>
                    </x-warning-card>
                </div>
            </div>

    </div>
        {{-- Modals --}}
        <x-delete-modal 
            name="delete-resident-{{ $resident->id }}" 
            action="{{ route('admin.residents.destroy', $resident->id) }}"
        >
            This will permanently delete the resident profile for <strong>{{ $resident->fname }} {{ $resident->lname }}</strong>. This action cannot be undone.
        </x-delete-modal>
</x-layouts::app>