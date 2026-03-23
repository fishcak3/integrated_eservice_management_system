<x-layouts::app :title="__('Complaint Details')">

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto">
        
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('resident.requests.index', ['type' => 'complaints']) }}" variant="ghost" icon="arrow-left" size="sm" />
            <div>
                <flux:heading size="lg">Case {{ $complaint->case_number }}</flux:heading>
                <flux:subheading>Filed on {{ $complaint->created_at->format('M d, Y h:i A') }}</flux:subheading>
            </div>
            
            <div class="ml-auto">
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
                <flux:badge color="{{ $statusColor }}" size="lg">
                    {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                </flux:badge>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Incident Details Card --}}
            <flux:card>
                <flux:heading size="md" class="mb-4">Incident Details</flux:heading>
                
                <div class="space-y-4 text-sm">
                    <div>
                        <span class="text-zinc-500 block">Complaint Type</span>
                        <span class="font-medium">{{ $complaint->type->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500 block">Incident Date</span>
                        <span class="font-medium">{{ $complaint->incident_at->format('M d, Y') }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500 block">Location</span>
                        <span class="font-medium">{{ $complaint->location }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500 block">Respondent (Who you are complaining about)</span>
                        <span class="font-medium text-red-600">{{ $complaint->respondent_name ?? 'Unknown' }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500 block">Narrative / Details</span>
                        <p class="mt-1 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg whitespace-pre-wrap">{{ $complaint->incident_details }}</p>
                    </div>
                </div>
            </flux:card>

            {{-- Office Updates Card --}}
            <flux:card class="flex flex-col gap-4">
                <flux:heading size="md">Official Remarks & Updates</flux:heading>

                @if($complaint->hearing_date)
                    <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                        <span class="text-purple-800 dark:text-purple-300 font-semibold block mb-1 flex items-center gap-2">
                            <flux:icon name="calendar" class="w-4 h-4"/> Scheduled Hearing
                        </span>
                        <span class="text-purple-900 dark:text-purple-200">{{ $complaint->hearing_date->format('l, F j, Y \a\t h:i A') }}</span>
                    </div>
                @endif

                @if($complaint->admin_remarks || $complaint->resolution_notes)
                    <div class="space-y-4 text-sm mt-2">
                        @if($complaint->admin_remarks)
                            <div>
                                <span class="text-zinc-500 block mb-1">Admin Remarks:</span>
                                <p class="font-medium">{{ $complaint->admin_remarks }}</p>
                            </div>
                        @endif

                        @if($complaint->resolution_notes)
                            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                                <span class="text-zinc-500 block mb-1">Resolution:</span>
                                <p class="font-medium text-green-700 dark:text-green-400">{{ $complaint->resolution_notes }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-zinc-400 py-8">
                        <flux:icon name="clock" class="w-8 h-8 mb-2 opacity-50" />
                        <p class="text-center">No official remarks have been added yet.</p>
                    </div>
                @endif
            </flux:card>
        </div>

    </div>
</x-layouts::app>