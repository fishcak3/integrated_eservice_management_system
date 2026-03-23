<x-layouts::app :title="__('Edit Announcement')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('official.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.announcements.index') }}">All Announcements</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.announcements.show', $announcement) }}">{{ $announcement->title }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Page Header --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">Edit Announcement</flux:heading>
            <flux:text variant="subtle">Update the details, cover image, or schedule of your notice.</flux:text>
        </div>
    </div>

    @if(session('error'))
        <flux:card class="mb-6 bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-900">
            <flux:heading class="text-red-600 dark:text-red-400">{{ session('error') }}</flux:heading>
        </flux:card>
    @endif

    {{-- MAIN FORM --}}
    <form action="{{ route('official.announcements.update', $announcement) }}" method="POST" enctype="multipart/form-data" class="space-y-10">
        @csrf
        @method('PUT')

        {{-- Section 1: Basic Information --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Basic Information</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Update the title and cover image. You can upload a new image to replace the existing one.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Title --}}
                    <flux:input 
                        name="title" 
                        label="Announcement Title" 
                        value="{{ old('title', $announcement->title) }}" 
                        required 
                    />

                    {{-- Cover Image with Preview --}}
                    <div class="space-y-3">
                        <flux:label>Cover Image</flux:label>
                        
                        @if($announcement->cover_image)
                            <div class="relative w-48 h-28 rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
                                <img src="{{ Storage::url($announcement->cover_image) }}" 
                                     alt="Current Cover" 
                                     class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/10"></div>
                            </div>
                            <p class="text-xs text-zinc-500">Current file: {{ basename($announcement->cover_image) }}</p>
                        @endif

                        <flux:input 
                            type="file" 
                            name="cover_image" 
                            description="Upload a new image to replace the current one. Leave blank to keep current image." 
                            accept="image/*" 
                        />
                    </div>

                </flux:card>
            </div>
        </div>

        {{-- Section 2: Announcement Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Announcement Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Modify the full content and specifics of the announcement.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6 space-y-6 border-zinc-200 dark:border-zinc-800">
                    
                    {{-- Content --}}
                    <flux:textarea 
                        name="content" 
                        label="Content" 
                        rows="8" 
                        required 
                    >{{ old('content', $announcement->content) }}</flux:textarea>

                </flux:card>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-6">
            <flux:button href="{{ route('official.announcements.index') }}" variant="subtle">Cancel</flux:button>
            <flux:button type="submit" variant="primary" icon="check">Update Announcement</flux:button>
        </div>  
    </form>

</x-layouts::app>