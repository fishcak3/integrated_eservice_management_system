<x-layouts::app :title="__('Manage Complaint')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('admin.requests.index', ['type' => 'complaints']) }}">Complaint Requests</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>View</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $complaint->case_number }}</flux:breadcrumbs.item>
        </flux:breadcrumbs> 
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">

        {{-- Header with Back Button --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Complaint Details</flux:heading>
                <flux:subheading>Case #: {{ $complaint->case_number }}</flux:subheading>
            </div>
            <flux:button href="{{ route('admin.requests.index', ['type' => 'complaints']) }}" variant="ghost" icon="arrow-left">Back to List</flux:button>
        </div>

        <flux:card>
            <div class="space-y-6">
                {{-- Status Badge --}}
                <div class="flex justify-between border-b pb-4">
                    <span class="text-zinc-500">Current Status:</span>
                    <flux:badge color="{{ $complaint->status === 'pending' ? 'yellow' : 'green' }}">
                        {{ ucfirst($complaint->status) }}
                    </flux:badge>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label>Complainant</flux:label>
                        <div class="font-medium">
                            {{ $complaint->complainant ? $complaint->complainant->name : $complaint->walkin_name }}
                        </div>
                    </div>
                    <div>
                        <flux:label>Respondent</flux:label>
                        <div class="font-medium">{{ $complaint->respondent_name }}</div>
                    </div>
                    <div>
                        <flux:label>Incident Date</flux:label>
                        <div class="font-medium">{{ \Carbon\Carbon::parse($complaint->incident_date)->format('F d, Y') }}</div>
                    </div>
                    <div>
                        <flux:label>Location</flux:label>
                        <div class="font-medium">{{ $complaint->location }}</div>
                    </div>
                </div>

                <div>
                    <flux:label>Narrative</flux:label>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 p-3 rounded-md">
                        {{ $complaint->incident_details }}
                    </p>
                </div>

            </div>

            {{-- Update Status Actions --}}
            <div class="flex gap-2 justify-end pt-4 border-t border-zinc-200 dark:border-zinc-700 p-2 mt-2">
                <form action="{{ route('complaints.update-status', $complaint->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="resolved">
                    <flux:button type="submit" variant="primary">Mark as Resolved</flux:button>
                </form>
            </div>
        </flux:card>

    </div>
</x-layouts::app>