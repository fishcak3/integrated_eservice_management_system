<x-layouts::app :title="__('New Announcement')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('announcements.index') }}">Announcements</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Create</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        <div>
            <flux:heading size="lg">Create Announcement</flux:heading>
            <flux:subheading>Draft a new update for the community.</flux:subheading>
        </div>

        {{-- Call the Livewire Component --}}
        <livewire:announcement-form />
        
    </div>
</x-layouts::app>