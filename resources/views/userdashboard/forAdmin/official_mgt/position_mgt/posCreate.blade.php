<x-layouts::app :title="__('Create Position')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('positions.posIndex') }}">Positions</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Page Header --}}
    <div class="flex flex-col justify-between gap-4 mb-8 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">Create New Position</flux:heading>
            <flux:text variant="subtle">Define a new role for barangay officials.</flux:text>
        </div>
    </div>

    @if(session('error'))
        <flux:card class="mb-6 border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20">
            <flux:heading class="text-red-600 dark:text-red-400">{{ session('error') }}</flux:heading>
        </flux:card>
    @endif

    {{-- MAIN FORM --}}
    <form 
        action="{{ route('positions.store') }}" 
        method="POST" 
        class="space-y-10"
    >
        @csrf

        {{-- Section 1: Position Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Position Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Define the title, responsibilities, and member capacity for this barangay role.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Title Input --}}
                    <div>
                        <flux:input 
                            name="title" 
                            label="Position Title" 
                            placeholder="e.g. Barangay Captain" 
                            value="{{ old('title') }}"
                            required 
                        />
                    </div>

                    {{-- Max Members --}}
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <flux:input 
                            type="number" 
                            name="max_members" 
                            label="Maximum Members" 
                            value="{{ old('max_members', 1) }}"
                            min="1"
                            required 
                        />
                    </div>

                    {{-- Description --}}
                    <div>
                        <flux:textarea 
                            name="description" 
                            label="Description (Optional)" 
                            placeholder="Briefly describe the responsibilities..."
                            rows="3"
                        >{{ old('description') }}</flux:textarea>
                    </div>

                </flux:card>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ route('positions.posIndex') }}" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Create Position</flux:button>
        </div>
    </form>

</x-layouts::app>