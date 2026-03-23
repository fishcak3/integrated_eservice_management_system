<x-layouts::app :title="__('Edit Position')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('positions.posIndex') }}">Positions</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Page Header --}}
    <div class="flex flex-col justify-between gap-4 mb-8 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">Edit Position: {{ $position->title }}</flux:heading>
            <flux:text variant="subtle">Update position details and status.</flux:text>
        </div>
    </div>

    @if(session('error'))
        <flux:card class="mb-6 border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20">
            <flux:heading class="text-red-600 dark:text-red-400">{{ session('error') }}</flux:heading>
        </flux:card>
    @endif

    {{-- MAIN FORM --}}
    <form 
        action="{{ route('positions.update', $position->id) }}" 
        method="POST" 
        class="space-y-10"
    >
        @csrf
        @method('PUT')

        {{-- Section 1: Position Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Position Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Modify the title, responsibilities, member capacity, and active status for this role.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Position Title --}}
                    <div>
                        <flux:input 
                            name="title" 
                            label="Position Title" 
                            value="{{ old('title', $position->title) }}"
                            required 
                        />
                    </div>

{{-- Max Members & Status Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:input 
                            type="number" 
                            name="max_members" 
                            label="Maximum Members" 
                            value="{{ old('max_members', $position->max_members) }}"
                            min="1"
                            required 
                        />

                        <flux:radio.group label="Status" name="is_active">
                            <flux:radio 
                                value="1" 
                                label="Active" 
                                :checked="old('is_active', $position->is_active ? '1' : '0') == '1'" 
                            />
                            <flux:radio 
                                value="0" 
                                label="Inactive" 
                                :checked="old('is_active', $position->is_active ? '1' : '0') == '0'" 
                            />
                        </flux:radio.group>
                    </div>

                    {{-- Description --}}
                    <div>
                        <flux:textarea 
                            name="description" 
                            label="Description" 
                            placeholder="Briefly describe the responsibilities..."
                            rows="3"
                        >{{ old('description', $position->description) }}</flux:textarea>
                    </div>

                </flux:card>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <flux:button href="{{ route('positions.posIndex') }}" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
    </form>

</x-layouts::app>