<x-layouts::app :title="__('Identity Verifications')">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item :href="route('users.verifications')">Verifications</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- Drop your Livewire component right here --}}
    <livewire:admin.verifications />

</x-layouts::app>