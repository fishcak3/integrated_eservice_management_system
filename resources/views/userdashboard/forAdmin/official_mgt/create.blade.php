<x-layouts::app :title="__('Create Official')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('officials.index') }}">Current Officials</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Page Header --}}
    <div class="flex flex-col justify-between gap-4 mb-8 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">Appoint New Official</flux:heading>
            <flux:text variant="subtle">Add a new official with term details and link them to a resident profile.</flux:text>
        </div>
    </div>

    @if(session('error'))
        <flux:card class="mb-6 border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20">
            <flux:heading class="text-red-600 dark:text-red-400">{{ session('error') }}</flux:heading>
        </flux:card>
    @endif

    {{-- MAIN FORM --}}
    <form 
        action="{{ route('officials.store') }}" 
        method="POST" 
        enctype="multipart/form-data" 
        class="space-y-10"
        x-data="{ needsAccount: false }"
        @resident-selected.window="needsAccount = !$event.detail.hasAccount"
    >
        @csrf

        {{-- Section 1: Link Resident --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Resident Profile</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Search and select the resident from the barangay database to appoint as an official.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 border-zinc-200 dark:border-zinc-800">
                    <div>
                        <x-resident-search required="true" />
                        <flux:error name="resident_id" class="mt-2" />
                    </div>
                </flux:card>
            </div>
        </div>

               {{-- Section 2: Account Credentials (Conditionally visible) --}}
        <div x-show="needsAccount" x-collapse class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">System Access</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    This resident does not have a user account yet. Provide an email and password to generate their official access credentials.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    <div>
                        <flux:input label="Email Address" name="email" type="email" :value="old('email')" x-bind:required="needsAccount" />
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <flux:input name="password" :label="__('Password')" type="password" autocomplete="new-password" placeholder="Min. 8 characters" viewable x-bind:required="needsAccount" />
                        <flux:input name="password_confirmation" :label="__('Confirm password')" type="password" autocomplete="new-password" placeholder="Confirm" viewable x-bind:required="needsAccount" />
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Section 3: Appointment Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Appointment Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Assign a position and specify the election and term limits for this official.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Position Selection (Full Width) --}}
                    <div>
                        <flux:select name="position_id" label="Position" placeholder="Assign a position..." required>
                            @foreach($positions as $position)
                                <flux:select.option value="{{ $position->id }}" :selected="old('position_id') == $position->id">
                                    {{ $position->title }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    {{-- Term Dates (3-Column Grid) --}}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <flux:input
                            type="number"
                            name="election_year"
                            label="Election Year"
                            placeholder="e.g. 2023"
                            value="{{ old('election_year') }}"
                            required
                        />

                        <flux:input 
                            type="date" 
                            name="term_start" 
                            label="Term Start" 
                            value="{{ old('term_start') }}" 
                            required 
                        />
                        
                        <flux:input 
                            type="date" 
                            name="term_end" 
                            label="Term End (Optional)" 
                            value="{{ old('term_end') }}" 
                        />
                    </div>

                    {{-- Term Status (Radio Cards) --}}
                    <div>
                        <flux:radio.group name="status" label="Term Status" variant="cards" class="grid grid-cols-1 gap-4 sm:grid-cols-2" required>
                            <flux:radio value="current" label="Current" description="Actively serving this term." :checked="old('status', 'current') === 'current'" />
                            <flux:radio value="completed" label="Completed" description="Successfully finished the term." :checked="old('status') === 'completed'" />
                            <flux:radio value="resigned" label="Resigned" description="Stepped down voluntarily." :checked="old('status') === 'resigned'" />
                            <flux:radio value="removed" label="Removed" description="Relieved of duty early." :checked="old('status') === 'removed'" />
                        </flux:radio.group>
                    </div>

                    {{-- E-Signature Upload --}}
                    <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:input 
                            type="file" 
                            name="signature_image" 
                            label="E-Signature (Optional)" 
                            description="Upload a clear image of the official's signature. A transparent PNG is highly recommended for documents."
                            accept="image/png, image/jpeg" 
                        />
                        <flux:error name="signature_image" class="mt-2" />
                    </div>
                </flux:card>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6">
            <flux:button href="{{ route('officials.index') }}" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Appoint Official</flux:button>
        </div>
    </form>

</x-layouts::app>