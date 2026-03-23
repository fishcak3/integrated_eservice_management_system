<x-layouts::app title="Former Official Details">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('officials.former') }}">Former Officials</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $term->official?->resident?->full_name ?? 'Unknown Resident' }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Determine Status & Variables for Headers --}}
    @php
        $fullName = $term->official?->resident?->full_name ?? 'Unknown Resident';
        $email = $term->official?->resident?->user?->email ?? 'No email linked';
        $positionTitle = $term->position?->title ?? 'Unknown';
        
        $statusColor = match($term->status) {
            'completed' => 'green',
            'resigned' => 'yellow',
            'removed' => 'red',
            default => 'zinc'
        };
    @endphp

    {{-- Header & Actions --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-3">
                <flux:heading size="xl" level="1">
                    {{ $fullName }}
                </flux:heading>
                
                {{-- Status Badge --}}
                <flux:badge rounded :color="$statusColor" size="sm">
                    {{ ucfirst($term->status ?? 'Unknown') }}
                </flux:badge>

                {{-- Position Badge --}}
                <flux:badge rounded color="zinc" size="sm">
                    Former {{ $positionTitle }}
                </flux:badge>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:button size="xs" variant="subtle" icon="user-circle" href="{{ route('users.show', $term->official->resident->user->id) }}" class="text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-900/20">
                    {{ $email }}
                </flux:button>
            </div>

        </div>
      
    </div>

    {{-- Main Layout Container --}}
    <div class="space-y-10">

        {{-- Section 1: Service Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Service Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Profile picture, role, and status during this specific historical term.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 pb-6 border-b border-zinc-200 dark:border-zinc-800">
                        {{-- Avatar --}}
                        <div class="shrink-0">
                            @if($term->official?->resident?->profile_photo)
                                <img src="{{ asset('storage/' . $term->official->resident->profile_photo) }}" alt="Profile" 
                                    class="w-20 h-20 object-cover rounded-full ring-4 ring-zinc-50 dark:ring-zinc-900 shadow-sm">
                            @else
                                <div class="w-20 h-20 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center ring-4 ring-zinc-50 dark:ring-zinc-900 shadow-sm">
                                    <span class="text-2xl text-zinc-400 font-bold">
                                        {{ substr($term->official?->resident?->fname ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            <flux:heading size="md">{{ $fullName }}</flux:heading>
                            <flux:text variant="subtle" class="mt-1">Term Record ID: {{ $term->id }}</flux:text>
                        </div>
                    </div>
                    
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Position Held</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $positionTitle }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Term Status</dt>
                            <dd class="mt-2 text-sm font-medium text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400">
                                {{ ucfirst($term->status ?? 'Unknown') }}
                            </dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>

        {{-- Section 2: Term Timeline --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Term Timeline</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Timeline and assignment duration for this historical record.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Date Started</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $term->term_start ? \Carbon\Carbon::parse($term->term_start)->format('F d, Y') : 'Record unavailable' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Date Ended</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $term->term_end ? \Carbon\Carbon::parse($term->term_end)->format('F d, Y') : 'Record unavailable' }}
                            </dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>

        {{-- Section 3: Complete Service History --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Complete Service History</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    All recorded terms for this individual across different positions.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-0 overflow-hidden">
                    <div class="overflow-x-auto">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Position</flux:table.column>
                                <flux:table.column>Duration</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @forelse($term->official->terms->sortByDesc('term_start') as $historyTerm)
                                    <flux:table.row :key="$historyTerm->id">
                                        
                                        <flux:table.cell class="font-medium">
                                            {{ $historyTerm->position?->title ?? 'Unknown' }}
                                            @if($historyTerm->id === $term->id)
                                                <span class="ml-2 text-xs text-blue-500 font-normal">(Currently viewing)</span>
                                            @endif
                                        </flux:table.cell>
                                        
                                        <flux:table.cell>
                                            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                {{ $historyTerm->term_start ? \Carbon\Carbon::parse($historyTerm->term_start)->format('M Y') : 'Unknown' }} 
                                                - 
                                                {{ $historyTerm->term_end ? \Carbon\Carbon::parse($historyTerm->term_end)->format('M Y') : 'Present' }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <flux:badge size="sm" color="{{ match($historyTerm->status) {
                                                'current' => 'blue',
                                                'completed' => 'green',
                                                'resigned' => 'yellow',
                                                'removed' => 'red',
                                                default => 'zinc'
                                            } }}">
                                                {{ ucfirst($historyTerm->status) }}
                                            </flux:badge>
                                        </flux:table.cell>

                                    </flux:table.row>
                                @empty
                                    <flux:table.row>
                                        <flux:table.cell colspan="3" class="text-center text-zinc-500 py-6">
                                            No other service records found.
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforelse
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </flux:card>
            </div>
        </div>

    </div>

</x-layouts::app>