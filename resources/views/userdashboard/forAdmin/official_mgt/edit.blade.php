<x-layouts::app :title="__('Edit Official')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('officials.index') }}">Officials</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $official->resident->full_name }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT WRAPPER --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Edit Official Record</flux:heading>
                <flux:subheading>Update term details, position, or status.</flux:subheading>
            </div>
            <flux:button href="{{ route('officials.index') }}" variant="ghost" icon="arrow-left">
                Back to List
            </flux:button>
        </div>

        {{-- Form Card --}}
        <flux:card class="max-w-2xl w-full mx-auto">
            
            {{-- ✅ STEP 1: Add ID here --}}
            <form id="edit-official-form" action="{{ route('officials.update', $official->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Resident Selection --}}
                <flux:select 
                    name="resident_id" 
                    label="Resident" 
                    searchable
                    placeholder="Select resident..."
                >
                    @foreach($residents as $resident)
                        <flux:select.option value="{{ $resident->id }}" :selected="$resident->id == $official->resident_id">
                            {{ $resident->full_name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Position Selection --}}
                <flux:select 
                    name="position_id" 
                    label="Position" 
                    placeholder="Assign a position..."
                >
                    @foreach($positions as $position)
                        <flux:select.option value="{{ $position->id }}" :selected="$position->id == $official->position_id">
                            {{ $position->title }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- User Account Link --}}
                <flux:select 
                    name="user_id" 
                    label="Linked User Account (Optional)" 
                    searchable
                    placeholder="Select user account..."
                >
                    <flux:select.option value="">None</flux:select.option>
                    @foreach($users as $user)
                        <flux:select.option value="{{ $user->id }}" :selected="$user->id == $official->user_id">
                            {{ $user->name }} ({{ $user->email }})
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Term Start Date --}}
                    <flux:input 
                        type="date" 
                        name="date_start" 
                        label="Term Start" 
                        value="{{ old('date_start', $official->date_start->format('Y-m-d')) }}"
                        required 
                    />

                    {{-- Term End Date --}}
                    <flux:input 
                        type="date" 
                        name="date_end" 
                        label="Term End (Optional)" 
                        value="{{ old('date_end', $official->date_end ? $official->date_end->format('Y-m-d') : '') }}"
                        description="Leave blank if indefinite"
                    />
                </div>

                {{-- Status (Fixed Logic) --}}
                <flux:radio.group label="Status" name="is_active">
                    <flux:radio value="1" label="Active" :checked="old('is_active', $official->is_active) == 1" />
                    <flux:radio value="0" label="Inactive" :checked="old('is_active', $official->is_active) == 0" />
                </flux:radio.group>

            </form>
        </flux:card>

        {{-- ✅ STEP 2: Form Actions (Outside Card) --}}
        <div class="flex justify-end gap-2 pt-4 border-zinc-200 dark:border-zinc-700 max-w-2xl w-full mx-auto">
            <flux:button href="{{ route('officials.index') }}" variant="ghost">Cancel</flux:button>
            
            {{-- Link button to form ID --}}
            <flux:button type="submit" form="edit-official-form" variant="primary">
                Update Official
            </flux:button>
        </div>

    </div>
</x-layouts::app>