<x-layouts::app :title="__('Household Details')">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('admin.residents.household') }}">Households</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $household->household_number }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- Header Area --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div class="space-y-2">
            <flux:heading size="xl" level="1">
                Household #{{ $household->household_number }}
            </flux:heading>
            <flux:text variant="subtle">
                View household information and family members.
            </flux:text>
        </div>
        
        {{-- Optional: Add an Edit Button here if you have an edit route --}}
        {{-- <flux:button href="{{ route('admin.residents.household.edit', $household->id) }}" variant="primary" icon="pencil">
            Edit Household
        </flux:button> --}}
    </div>

    {{-- Main Layout Container --}}
    <div class="space-y-10">

        {{-- Section 1: Household Details --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Household Details</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Basic location information and overview of the household.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-6">
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Sitio</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $household->sitio ?? 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Total Members</dt>
                            <dd class="mt-2 text-sm font-medium text-zinc-900 dark:text-white">
                                <flux:badge color="blue" size="sm" class="rounded-full px-2.5">
                                    {{ $household->members->count() }} People
                                </flux:badge>
                            </dd>
                        </div>
                    </dl>
                </flux:card>
            </div>
        </div>

        {{-- Section 2: Family Members Table --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg">Family Members</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    A list of all residents registered under this household.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <flux:card class="p-0 overflow-hidden">
                    <flux:table class="whitespace-nowrap bg-transparent">
                        <flux:table.columns>
                            <flux:table.column>Name</flux:table.column>
                            <flux:table.column>Relation</flux:table.column>
                            <flux:table.column>Contact</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column ></flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($household->members as $resident)
                                <flux:table.row>
                                    {{-- Name --}}
                                    <flux:table.cell>
                                        {{ $resident->fname }} {{ $resident->lname }}
                                    </flux:table.cell>

                                    {{-- Role --}}
                                    <flux:table.cell color="primary">
                                        @if(strtolower($resident->relation_to_head) === 'head')
                                            <span >Family Head</span>
                                        @else
                                            {{ ucfirst($resident->relation_to_head ?? 'Member') }}
                                        @endif
                                    </flux:table.cell>

                                    {{-- Contact --}}
                                    <flux:table.cell class="text-zinc-600 dark:text-zinc-300">
                                        {{ $resident->phone_number ?? 'N/A' }}
                                    </flux:table.cell>

                                    {{-- Status --}}
                                    <flux:table.cell>
                                        <flux:badge rounded size="sm" color="{{ strtolower($resident->status) === 'active' ? 'green' : 'zinc' }}">
                                            {{ ucfirst($resident->status ?? 'Active') }}
                                        </flux:badge>
                                    </flux:table.cell>

                                    {{-- Actions --}}
                                    <flux:table.cell align="end">
                                        <flux:button href="{{ route('admin.residents.show', $resident->id) }}" size="sm" variant="ghost">
                                            View Profile
                                        </flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="5" class="py-12 text-center text-zinc-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <flux:icon.users class="h-8 w-8 text-zinc-300 dark:text-zinc-600 mb-2" />
                                            <p>No members found in this household.</p>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            </div>
        </div>

        {{-- Section 3: Danger Zone --}}
        <div class="grid grid-cols-1 gap-x-8 gap-y-8 first:pt-0 md:grid-cols-3">
            <div class="px-4 sm:px-0">
                <flux:heading size="lg" class="text-red-600 dark:text-red-500">Danger Zone</flux:heading>
                <flux:text variant="subtle" class="mt-1">
                    Permanently delete this household from the system. This action cannot be undone.
                </flux:text>
            </div>

            <div class="md:col-span-2">
                <x-warning-card 
                    title="Delete Household" 
                    description="Once you delete a household, there is no going back. Please be certain."
                >
                    <flux:modal.trigger name="delete-household-{{ $household->id }}">
                        <flux:button variant="danger">Delete Household</flux:button>
                    </flux:modal.trigger>
                </x-warning-card>
            </div>
        </div>

    </div>

    {{-- Modals --}}
    <x-delete-modal 
        name="delete-household-{{ $household->id }}" 
        action="{{ route('admin.residents.household.destroy', $household->id) }}"
    >
        This will permanently delete the household profile for <strong>Household #{{ $household->household_number }}</strong>. This action cannot be undone.
    </x-delete-modal>

</x-layouts::app>