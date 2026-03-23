<x-layouts::app :title="__('Resident Management')">
        <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Pending Approval</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        <livewire:admin.resident-requests />

    </div>

</x-layouts::app>