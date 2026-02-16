<x-layouts::app :title="$official->resident->full_name . ' - Official Profile'">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('officials.index') }}">Officials</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>View</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $official->resident->full_name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">

        {{-- Header & Navigation --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Official Profile</flux:heading>
                <flux:subheading>View detailed records and term information.</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:button href="{{ route('officials.index') }}" variant="ghost" icon="arrow-left">
                    Back to List
                </flux:button>
                <flux:button href="{{ route('officials.edit', $official->id) }}" variant="primary" icon="pencil-square">
                    Edit Profile
                </flux:button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT COLUMN: Main Profile Card --}}
            <flux:card class="lg:col-span-1 flex flex-col items-center text-center p-6 space-y-4">
                
                {{-- Avatar / Initials --}}
                <div class="relative">
                    <div class="w-32 h-32 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-3xl font-bold text-zinc-500 border-4 border-white dark:border-zinc-900 shadow-lg">
                        {{ substr($official->resident->fname, 0, 1) }}{{ substr($official->resident->lname, 0, 1) }}
                    </div>
                    <div class="absolute bottom-0 right-0">
                        @if($official->is_active)
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-green-500 ring-4 ring-white dark:ring-zinc-900">
                                <flux:icon.check class="w-4 h-4 text-white" />
                            </span>
                        @else
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-400 ring-4 ring-white dark:ring-zinc-900">
                                <flux:icon.x-mark class="w-4 h-4 text-white" />
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Name & Title --}}
                <div>
                    <h2 class="text-xl font-bold text-zinc-900 dark:text-white">
                        {{ $official->resident->full_name }}
                    </h2>
                    <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ $official->position->title }}
                    </p>
                </div>

                {{-- Status Badge --}}
                <div>
                    @if($official->is_active)
                        <flux:badge color="green" size="sm">Active Duty</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm">Term Ended</flux:badge>
                    @endif
                </div>

                <div class="w-full border-t border-zinc-200 dark:border-zinc-700 my-4"></div>

                {{-- Quick Contact Actions --}}
                <div class="w-full space-y-2">
                    
                    {{-- Check if linked user has email --}}
                    @if($official->user)
                        <flux:button class="w-full" href="mailto:{{ $official->user->email }}" variant="ghost" icon="envelope">
                            Send Email
                        </flux:button>
                    @endif
                </div>
            </flux:card>

            {{-- RIGHT COLUMN: Detailed Information --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- 1. Term Information --}}
                <flux:card>
                    <flux:heading size="md" class="mb-4">Term Details</flux:heading>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <flux:label>Position Title</flux:label>
                            <div class="font-medium mt-1">{{ $official->position->title }}</div>
                        </div>

                        <div>
                            <flux:label>System Account</flux:label>
                            @if($official->user)
                                <div class="font-medium mt-1 flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                    Linked: {{ $official->user->email }}
                                </div>
                            @else
                                <div class="text-zinc-500 mt-1 italic">No system account linked</div>
                            @endif
                        </div>

                        <div>
                            <flux:label>Date Started</flux:label>
                            <div class="font-medium mt-1">
                                {{ \Carbon\Carbon::parse($official->date_start)->format('F d, Y') }}
                            </div>
                        </div>

                        <div>
                            <flux:label>Date Ended</flux:label>
                            <div class="font-medium mt-1">
                                @if($official->date_end)
                                    {{ \Carbon\Carbon::parse($official->date_end)->format('F d, Y') }}
                                @else
                                    <span class="text-zinc-500">Present (Indefinite)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </flux:card>

                {{-- 2. Personal Resident Information --}}
                <flux:card>
                    <flux:heading size="md" class="mb-4">Personal Information</flux:heading>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <flux:label>Full Name</flux:label>
                            <div class="font-medium mt-1">{{ $official->resident->full_name }}</div>
                        </div>
                        
                        <div>
                            <flux:label>Civil Status</flux:label>
                            <div class="font-medium mt-1 capitalize">{{ $official->resident->civil_status ?? 'N/A' }}</div>
                        </div>

                        <div>
                            <flux:label>Sex</flux:label>
                            <div class="font-medium mt-1 capitalize">{{ $official->resident->sex ?? 'N/A' }}</div>
                        </div>

                        <div>
                            <flux:label>Phone Number</flux:label>
                            <div class="font-medium mt-1">{{ $official->resident->phone_number ?? 'N/A' }}</div>
                        </div>

                        <div class="sm:col-span-2">
                            <flux:label>Address</flux:label>
                            <div class="font-medium mt-1">
                                {{ $official->resident->street ?? '' }} 
                                {{ $official->resident->purok ? 'Purok ' . $official->resident->purok . ',' : '' }}
                                {{ $official->resident->barangay }}, {{ $official->resident->municipality }}
                            </div>
                        </div>
                    </div>
                </flux:card>

                {{-- Danger Zone (Delete) --}}
                <div class="flex justify-end pt-4">
                     <form action="{{ route('officials.destroy', $official->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this official record? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="danger" icon="trash">
                            Delete Official Record
                        </flux:button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-layouts::app>