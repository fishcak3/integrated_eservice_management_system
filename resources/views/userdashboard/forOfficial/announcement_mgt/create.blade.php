<x-layouts::app :title="__('Create Draft Announcement')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('official.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.announcements.index') }}">Announcements</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>      
    </x-slot>

    {{-- Page Header --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">New Announcement Draft</flux:heading>
            <flux:text variant="subtle">Create a new notice. It will be saved as a draft until published.</flux:text>
        </div>
    </div>

    @if(session('error'))
        <flux:card class="mb-6 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-900">
            <flux:heading class="text-red-600 dark:text-red-400">{{ session('error') }}</flux:heading>
        </flux:card>
    @endif

    {{-- MAIN FORM --}}
    <form action="{{ route('official.announcements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf

        {{-- Section 1: Basic Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Basic Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Provide the title and an optional cover image for the announcement.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Title --}}
                    <flux:input 
                        name="title" 
                        label="Announcement Title" 
                        placeholder="e.g., Scheduled Power Interruption" 
                        value="{{ old('title') }}" 
                        required 
                    />

                    {{-- Cover Image --}}
                    <flux:input 
                        type="file" 
                        name="cover_image" 
                        label="Cover Image (Optional)" 
                        accept="image/*"
                    />

                </flux:card>
            </div>
        </div>

        {{-- Section 2: Announcement Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Announcement Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Write the full content and specifics of the announcement here.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Content --}}
                    <flux:textarea 
                        name="content" 
                        label="Content" 
                        placeholder="Write the full details of the announcement here..." 
                        rows="8" 
                        required 
                    >{{ old('content') }}</flux:textarea>

                </flux:card>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6">
            <flux:button href="{{ route('official.announcements.index') }}" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary" icon="document-text">Save as Draft</flux:button>
        </div>  
    </form>

</x-layouts::app>