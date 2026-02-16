<x-layouts::app :title="__('Edit Position')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">
        
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Edit Position</flux:heading>
                <flux:subheading>Update position details and status.</flux:subheading>
            </div>
            <flux:button href="{{ route('positions.posIndex') }}" variant="ghost" icon="arrow-left">
                Back to Positions
            </flux:button>
        </div>

        {{-- Form Card --}}
        <flux:card class="max-w-2xl w-full mx-auto">
            <form action="{{ route('positions.update', $position->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Position Title --}}
                <flux:input 
                    name="title" 
                    label="Position Title" 
                    value="{{ old('title', $position->title) }}"
                    required 
                />

                {{-- Description --}}
                <flux:textarea 
                    name="description" 
                    label="Description" 
                    placeholder="Briefly describe the responsibilities..."
                    rows="3"
                >{{ old('description', $position->description) }}</flux:textarea>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Max Members --}}
                    <flux:input 
                        type="number" 
                        name="max_members" 
                        label="Maximum Members" 
                        value="{{ old('max_members', $position->max_members) }}"
                        min="1"
                        required 
                    />

                    {{-- Status (Essential for Editing) --}}
                    <flux:radio.group label="Status" name="is_active" :value="old('is_active', $position->is_active ? '1' : '0')">
                        <flux:radio value="1" label="Active" />
                        <flux:radio value="0" label="Inactive" />
                    </flux:radio.group>
                </div>

                {{-- Form Actions --}}
                <div class="flex justify-end gap-2 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:button href="{{ route('positions.posIndex') }}" variant="ghost">Cancel</flux:button>
                    <flux:button type="submit" variant="primary">Save Changes</flux:button>
                </div>

            </form>
        </flux:card>

    </div>
</x-layouts::app>