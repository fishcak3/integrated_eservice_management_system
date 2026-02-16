<x-layouts::app :title="__('Appoint Official')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('officials.index') }}">Officials</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Appoint New Official</flux:heading>
                <flux:subheading>Assign a position and term to a resident.</flux:subheading>
            </div>
            <flux:button href="{{ route('officials.index') }}" variant="ghost" icon="arrow-left">
                Back to List
            </flux:button>
        </div>

        {{-- Form Card --}}
        <flux:card class="max-w-2xl w-full mx-auto">
            
            {{-- ✅ STEP 1: Add an ID to the form --}}
            <form id="appoint-form" action="{{ route('officials.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Resident Selection --}}
                <flux:select 
                    name="resident_id" 
                    label="Select Resident" 
                    placeholder="Choose a resident..." 
                    searchable
                    required
                >
                    @foreach($residents as $resident)
                        <flux:select.option value="{{ $resident->id }}">
                            {{ $resident->full_name }} 
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Position Selection --}}
                <flux:select 
                    name="position_id" 
                    label="Position" 
                    placeholder="Assign a position..." 
                    required
                >
                    @foreach($positions as $position)
                        <flux:select.option value="{{ $position->id }}">
                            {{ $position->title }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Optional: User Account Link --}}
                @if(isset($users) && $users->count() > 0)
                    <flux:select 
                        name="user_id" 
                        label="Link User Account (Optional)" 
                        placeholder="Select a user account..." 
                        searchable
                    >
                        <flux:select.option value="">None</flux:select.option>
                        @foreach($users as $user)
                            <flux:select.option value="{{ $user->id }}">
                                {{ $user->name }} ({{ $user->email }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Term Start Date --}}
                    <flux:input 
                        type="date" 
                        name="date_start" 
                        label="Term Start" 
                        value="{{ date('Y-m-d') }}"
                        required 
                    />

                    {{-- Term End Date --}}
                    <flux:input 
                        type="date" 
                        name="date_end" 
                        label="Term End (Optional)" 
                        description="Leave blank if indefinite"
                    />
                </div>

                {{-- Status --}}
                <flux:radio.group label="Status" name="is_active" :value="old('is_active', '1')">
                    <flux:radio value="1" label="Active" />
                    <flux:radio value="0" label="Inactive" />
                </flux:radio.group>

            </form>
        </flux:card>
        {{-- Form Actions --}}
        <div class="flex justify-end gap-2 pt-4 border-zinc-200 dark:border-zinc-700 max-w-2xl w-full mx-auto">
            <flux:button href="{{ route('officials.index') }}" variant="ghost">Cancel</flux:button>
 
            <flux:button type="submit" form="appoint-form" variant="primary">
                Appoint Official
            </flux:button>
        </div>

    </div>
</x-layouts::app>