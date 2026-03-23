<x-layouts::app :title="__('Edit Announcement')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('admin.announcements.index') }}">Announcements</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Edit</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $announcement->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs> 
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        <div>
            <flux:heading size="lg">Edit Announcement</flux:heading>
            <flux:subheading>Update details for "{{ $announcement->title }}".</flux:subheading>
        </div>

        {{-- Call the Component and pass the existing announcement --}}
        <livewire:announcement-form :announcement="$announcement" />
        
    </div>
</x-layouts::app>