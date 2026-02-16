<x-layouts::app :title="__('Create Position')">
        <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('positions.posIndex') }}">Positions</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Header & Back Button --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Create New Position</flux:heading>
                <flux:subheading>Define a new role for barangay officials.</flux:subheading>
            </div>
            {{-- Updated Route Name --}}
            <flux:button href="{{ route('positions.posIndex') }}" variant="ghost" icon="arrow-left">
                Back to Positions
            </flux:button>
        </div>

        {{-- Form Card --}}
        <flux:card class="max-w-2xl w-full mx-auto">
            
            {{-- ✅ STEP 1: Add ID here --}}
            <form id="position-form" action="{{ route('positions.store') }}" method="POST" class="space-y-6">
                @csrf

                <flux:input 
                    name="title" 
                    label="Position Title" 
                    placeholder="e.g. Barangay Captain" 
                    value="{{ old('title') }}"
                    required 
                />

                <flux:textarea 
                    name="description" 
                    label="Description (Optional)" 
                    placeholder="Briefly describe the responsibilities..."
                    rows="3"
                >{{ old('description') }}</flux:textarea>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input 
                        type="number" 
                        name="max_members" 
                        label="Maximum Members" 
                        value="{{ old('max_members', 1) }}"
                        min="1"
                        required 
                    />
                </div>

            </form>
        </flux:card>

        {{-- Form Actions --}}
        <div class="flex justify-end gap-2 pt-4 border-zinc-200 dark:border-zinc-700">
            <flux:button href="{{ route('positions.posIndex') }}" variant="ghost">Cancel</flux:button>
            
            {{-- ✅ STEP 2: Link button to form ID --}}
            <flux:button type="submit" form="position-form" variant="primary">
                Create Position
            </flux:button>
        </div>
    </div>
</x-layouts::app>