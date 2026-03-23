<x-layouts::app :title="__('Edit Official')">
    {{-- Fetch the current active term to populate the form fields --}}
    @php
        $currentTerm = $official->terms()->where('status', 'current')->first() 
                    ?? $official->terms()->latest('id')->first();
    @endphp

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('officials.index') }}">Current Officials</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('officials.show', $official->id) }}">{{ $official->resident->full_name }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Header --}}
    <div class="mb-8">
        <div>
            <flux:heading size="xl" level="1">Edit {{ $official->resident->full_name }}</flux:heading>
            <flux:subheading>Update term details, position, and status.</flux:subheading>
        </div>
    </div>

    {{-- Form Start --}}
    {{-- Form Start --}}
    <form action="{{ route('officials.update', $official->id) }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf
        @method('PUT')

        {{-- SECTION 1: Position Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Position Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Assign the official's current position and term status.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        
                        {{-- Position Dropdown --}}
                        <div class="sm:col-span-2">
                            <flux:select
                                name="position_id"
                                label="Position"
                                placeholder="Assign a position..."
                            >
                                @foreach($positions as $position)
                                    <flux:select.option value="{{ $position->id }}" :selected="$currentTerm && $position->id == $currentTerm->position_id">
                                        {{ $position->title }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        {{-- Term Status (Radio Cards) --}}
                        {{-- FIX: Added sm:col-span-2 here so it takes up the full width --}}
                        <div class="sm:col-span-2">
                            <flux:radio.group name="status" label="Term Status" variant="cards" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" required>
                                <flux:radio value="current" label="Current" description="Actively serving this term." :checked="old('status', 'current') === 'current'" />
                                <flux:radio value="completed" label="Completed" description="Successfully finished the term." :checked="old('status') === 'completed'" />
                                <flux:radio value="resigned" label="Resigned" description="Stepped down voluntarily." :checked="old('status') === 'resigned'" />
                                <flux:radio value="removed" label="Removed" description="Relieved of duty early." :checked="old('status') === 'removed'" />
                            </flux:radio.group>
                        </div>
                        {{-- E-Signature Upload Section --}}
                        <div class="pt-6 mt-6 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:heading size="md" class="mb-4">Digital Signature</flux:heading>
                            
                            {{-- Show current signature if it exists --}}
                            @if($official->e_signature_path)
                                <div class="p-4 mb-4 border rounded-lg bg-zinc-50 border-zinc-200 dark:bg-zinc-800/50 dark:border-zinc-700 w-max">
                                    <flux:text variant="subtle" class="mb-2 text-xs font-semibold uppercase">Current Signature:</flux:text>
                                    <img src="{{ Storage::url($official->e_signature_path) }}" alt="Current E-Signature" class="object-contain h-16 max-w-full mix-blend-multiply dark:mix-blend-normal">
                                </div>
                            @endif

                            <flux:input 
                                type="file" 
                                name="signature_image" 
                                label="{{ $official->e_signature_path ? 'Upload New Signature (Optional)' : 'Upload E-Signature (Optional)' }}" 
                                description="Uploading a new file will replace the current signature. A transparent PNG is highly recommended."
                                accept="image/png, image/jpeg" 
                            />
                            <flux:error name="signature_image" class="mt-2" />
                        </div>
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- SECTION 2: Term Timeline --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Term Timeline</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Specify the election year and the duration of the official's term.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <flux:input
                                type="number"
                                name="election_year"
                                label="Election Year"
                                placeholder="e.g. 2023"
                                value="{{ old('election_year', $currentTerm->election_year ?? '') }}"
                                required
                            />
                        </div>

                        <div>
                            <flux:input
                                type="date"
                                name="term_start"
                                label="Term Start"
                                value="{{ old('term_start', $currentTerm && $currentTerm->term_start ? \Carbon\Carbon::parse($currentTerm->term_start)->format('Y-m-d') : '') }}"
                                required
                            />
                        </div>

                        <div>
                            <flux:input
                                type="date"
                                name="term_end"
                                label="Term End (Optional)"
                                value="{{ old('term_end', $currentTerm && $currentTerm->term_end ? \Carbon\Carbon::parse($currentTerm->term_end)->format('Y-m-d') : '') }}"
                            />
                        </div>
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-end gap-4 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ route('officials.index') }}" variant="subtle">
                Cancel
            </flux:button>
            
            <flux:button type="submit" variant="primary">
                Update Official
            </flux:button>
        </div>
    </form>
</x-layouts::app>