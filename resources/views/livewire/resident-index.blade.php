<?php

// Note: If you are using Livewire Volt's class-based API, 
// you typically need to import Livewire\Volt\Component;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithPagination;

    public string $routePrefix;
    public string $search = '';

    public function mount()
    {
        // Check the logged-in user's role and set the route prefix accordingly.
        // Assuming your routes are named like 'admin.residents.index' and 'official.residents.index'
        $this->routePrefix = Auth::user()->role === 'admin' ? 'admin' : 'official';
    }

    // You will need to define your properties and methods here, for example:
    // public $global_sitios = [];
    // public $sortField = 'id';
    // public $sortDirection = 'asc';
    
    // public function sortBy($field) { ... }
    
    // public function with() {
    //     return [ 'residents' => ... ];
    // }
};
?>

<x-layouts::app :title="__('Resident Management')">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item>Residents</flux:breadcrumbs.item>
            <flux:breadcrumbs.item href="{{ route('official.residents.index') }}">Resident List</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                id: true,
                resident: true,
                address: true,
                status: true,
                sector: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Top Header Area --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Residents</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">Manage and track all resident records and household information.</flux:subheading>
            </div>
            
            <div class="flex gap-2">
                <flux:button href="{{ route('official.residents.create') }}" variant="primary" icon="plus">
                    New Resident
                </flux:button>
            </div>
        </div>

        {{-- Livewire Reactive Filters & Search Area --}}
        <div class="flex flex-col gap-4">
            
            {{-- Resident Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Sitio Filter --}}
                <flux:dropdown>
                    <flux:badge as="button" rounded color="{{ !empty($sitios) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($sitios) ? count($sitios) . ' Sitios' : 'Sitio' }}
                    </flux:badge>
                    <flux:menu class="w-48 p-3 space-y-2 max-h-72 overflow-y-auto">
                        <flux:heading size="sm" class="mb-2">Filter Sitio</flux:heading>
                        @foreach($global_sitios as $sitio)
                            <flux:checkbox wire:model.live="sitios" value="{{ $sitio }}" label="{{ $sitio }}" />
                        @endforeach
                    </flux:menu>
                </flux:dropdown>

                {{-- Status Filter --}}
                <flux:dropdown>
                    <flux:badge as="button" rounded color="{{ !empty($statuses) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($statuses) ? count($statuses) . ' Statuses' : 'Status' }}
                    </flux:badge>
                    <flux:menu class="w-48 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Status</flux:heading>
                        <flux:checkbox wire:model.live="statuses" value="active" label="Active" />
                        <flux:checkbox wire:model.live="statuses" value="inactive" label="Inactive" />
                        <flux:checkbox wire:model.live="statuses" value="deceased" label="Deceased" />
                        <flux:checkbox wire:model.live="statuses" value="pending" label="Pending" />
                        <flux:checkbox wire:model.live="statuses" value="transferred" label="Transferred" />
                    </flux:menu>
                </flux:dropdown>

                {{-- Sector Filter --}}
                <flux:dropdown>
                    <flux:badge as="button" rounded color="{{ !empty($sectors) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($sectors) ? count($sectors) . ' Sectors' : 'Sector' }}
                    </flux:badge>
                    <flux:menu class="w-56 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Sectors</flux:heading>
                        <flux:checkbox wire:model.live="sectors" value="senior_citizen" label="Senior Citizen" />
                        <flux:checkbox wire:model.live="sectors" value="is_pwd" label="PWD" />
                        <flux:checkbox wire:model.live="sectors" value="solo_parent" label="Solo Parent" />
                        <flux:checkbox wire:model.live="sectors" value="is_4ps" label="4Ps Beneficiary" />
                        <flux:checkbox wire:model.live="sectors" value="voter" label="Registered Voter" />
                        <flux:checkbox wire:model.live="sectors" value="ofw" label="OFW" />
                        <flux:checkbox wire:model.live="sectors" value="unemployed" label="Unemployed" />
                        <flux:checkbox wire:model.live="sectors" value="out_of_school_children" label="Out of School Youth" />    
                        <flux:checkbox wire:model.live="sectors" value="osa" label="OSA" />  
                        <flux:checkbox wire:model.live="sectors" value="laborforce" label="Part of Labor Force" />    
                        <flux:checkbox wire:model.live="sectors" value="isy_isc" label="ISY/ISC" />    
                    </flux:menu>
                </flux:dropdown>

                {{-- Family Head Filter --}}
                <flux:dropdown>
                    <flux:badge as="button" rounded color="{{ !empty($is_family_head) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($is_family_head) ? 'Role Selected' : 'Family Role' }}
                    </flux:badge>
                    <flux:menu class="w-48 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Role</flux:heading>
                        <flux:checkbox wire:model.live="is_family_head" value="1" label="Family Head" />
                        <flux:checkbox wire:model.live="is_family_head" value="0" label="Member" />
                    </flux:menu>
                </flux:dropdown>

                {{-- Clear Filters --}}
                @if(!empty($statuses) || !empty($sectors) || !empty($sitios) || !empty($is_family_head) || !empty($search))
                    <flux:button wire:click="clearFilters" size="sm" variant="subtle" icon="x-mark">
                        Clear
                    </flux:button>
                @endif
            </div>

            {{-- Toolbar: Search & Actions --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Search --}}
                <div class="flex-1 max-w-sm">
                    <flux:input 
                        wire:model.live.debounce.300ms="search" 
                        icon="magnifying-glass" 
                        placeholder="Search residents..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                    />
                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.id" label="Resident ID" />
                            <flux:checkbox x-model="cols.resident" label="Resident" />
                            <flux:checkbox x-model="cols.address" label="Address" />
                            <flux:checkbox x-model="cols.status" label="Status" />
                            <flux:checkbox x-model="cols.sector" label="Sector" />
                        </flux:menu>
                    </flux:dropdown>

                    {{-- Export Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="arrow-down-tray">Export</flux:button>
                        <flux:menu>
                            <flux:modal.trigger name="import-official.residents" cursor="pointer">
                                <flux:menu.item icon="arrow-down-tray">Import CSV</flux:menu.item>
                            </flux:modal.trigger>
                            <flux:menu.separator />
                            <flux:modal.trigger name="export-csv-confirm" cursor="pointer">
                                <flux:menu.item icon="arrow-up-tray">Export CSV</flux:menu.item>
                            </flux:modal.trigger>
                            <flux:modal.trigger name="export-pdf-confirm" cursor="pointer">
                                <flux:menu.item icon="document-arrow-up">Export PDF</flux:menu.item>
                            </flux:modal.trigger>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="border border-zinc-200 dark:border-zinc-800 rounded-xl overflow-hidden bg-transparent mt-2">
            <flux:table class="whitespace-nowrap">
                <flux:table.columns>
                    {{-- Checkbox Column --}}
                    <flux:table.column class="w-10 pl-4 pr-0">
                        <flux:checkbox />
                    </flux:table.column>
                    
                    {{-- Headers with Arrows & Toggle Logic --}}
                    <flux:table.column x-show="cols.id" class="font-medium text-zinc-400">
                        <div wire:click="sortBy('id')" class="flex items-center gap-1 cursor-pointer hover:text-white transition-colors">
                            Resident ID <flux:icon.arrows-up-down variant="micro" />
                        </div>
                    </flux:table.column>
                    
                    <flux:table.column x-show="cols.resident" class="font-medium text-zinc-400">
                        <div wire:click="sortBy('fname')" class="flex items-center gap-1 cursor-pointer hover:text-white transition-colors">
                            Resident <flux:icon.arrows-up-down variant="micro" />
                        </div>
                    </flux:table.column>
                    
                    <flux:table.column x-show="cols.address" class="font-medium text-zinc-400">
                        <div wire:click="sortBy('address')" class="flex items-center gap-1 cursor-pointer hover:text-white transition-colors">
                            Address <flux:icon.arrows-up-down variant="micro" />
                        </div>
                    </flux:table.column>
                    
                    <flux:table.column x-show="cols.status" class="font-medium text-zinc-400">Status</flux:table.column>
                    <flux:table.column x-show="cols.sector" class="font-medium text-zinc-400">Sector</flux:table.column>
                    
                    {{-- Actions Column --}}
                    <flux:table.column align="end" class="font-medium text-zinc-400"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($residents as $resident)
                        <flux:table.row :key="$resident->id" class=" dark:hover:bg-zinc-900/50 transition-colors">
                            {{-- Row Checkbox --}}
                            <flux:table.cell class="pl-4 pr-0">
                                <flux:checkbox />
                            </flux:table.cell>

                            {{-- ID --}}
                            <flux:table.cell class="font-bold text-zinc-900 dark:text-white">
                                RES-{{ str_pad($resident->id, 4, '0', STR_PAD_LEFT) }}
                            </flux:table.cell>

                            {{-- Avatar + Name --}}
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    @php
                                        $initials = strtoupper(substr($resident->fname, 0, 1) . substr($resident->lname, 0, 1));
                                    @endphp
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-900/30 text-xs font-medium text-emerald-500 ring-1 ring-inset ring-emerald-900/50">
                                        {{ $initials }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $resident->fname }} {{ $resident->lname }}</span>
                                        <span class="text-xs text-zinc-500">{{ $resident->phone_number ?? 'No contact' }}</span>
                                    </div>
                                </div>
                            </flux:table.cell>

                            {{-- Address --}}
                            <flux:table.cell class="text-zinc-600 dark:text-zinc-300">
                                @if($resident->household)
                                    {{ $resident->household->sitio ? 'Sitio ' . $resident->household->sitio : '' }}
                                @else
                                    <span class="text-zinc-500">N/A</span>
                                @endif
                            </flux:table.cell>

                            {{-- Status Badge --}}
                            <flux:table.cell>
                                @php
                                    $badgeConfig = match($resident->status) {
                                        'active'      => ['color' => 'emerald', 'label' => 'Active'],
                                        'inactive'    => ['color' => 'zinc',    'label' => 'Inactive'],
                                        'pending'     => ['color' => 'yellow',  'label' => 'Pending'],
                                        'transferred' => ['color' => 'sky',     'label' => 'Transferred'],
                                        'deceased'    => ['color' => 'red',     'label' => 'Deceased'],
                                        default       => ['color' => 'zinc',    'label' => 'Unknown'],
                                    };
                                @endphp
                                <flux:badge color="{{ $badgeConfig['color'] }}" size="sm" class="rounded-full px-2.5">
                                    {{ $badgeConfig['label'] }}
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Sectors --}}
                            <flux:table.cell>
                                @if($resident->senior_citizen || $resident->is_pwd || $resident->solo_parent)
                                    <div class="flex gap-1 text-zinc-400">
                                        @if($resident->senior_citizen) <flux:icon.user variant="micro" title="Senior Citizen" /> @endif
                                        @if($resident->is_pwd) <flux:icon.heart variant="micro" title="PWD" /> @endif
                                        @if($resident->solo_parent) <flux:icon.users variant="micro" title="Solo Parent" /> @endif
                                    </div>
                                @else
                                    <span class="text-zinc-500">—</span>
                                @endif
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('official.residents.show', $resident->id) }}" icon="eye">View Details</flux:menu.item>
                                        <flux:menu.item href="{{ route('official.residents.edit', $resident->id) }}" icon="pencil-square">Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:modal.trigger name="delete-resident-{{ $resident->id }}">
                                            <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
                                        </flux:modal.trigger>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>

                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="text-center text-zinc-500 py-8">
                                No residents found.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                {{ $residents->links() }}
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
    <flux:modal name="import-official.residents" class="min-w-[28rem]">
        <form action="{{ route('official.residents.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <flux:heading size="lg">Import residents via CSV</flux:heading>
                <flux:text class="mt-2">
                    Upload a valid CSV file to bulk import official.residents. Make sure your column headers exactly match the required database fields.
                </flux:text>
            </div>

            <div>
                <flux:input type="file" name="file" accept=".csv" required />
                <flux:text class="text-xs mt-2 text-zinc-500">
                    Accepted format: .csv (Max size: 5MB)
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" icon="arrow-up-tray">Upload & Import</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Modals --}}
    @foreach ($residents as $resident)
        <x-delete-modal 
            name="delete-resident-{{ $resident->id }}" 
            action="{{ route('official.residents.destroy', $resident->id) }}"
        >
            This will permanently delete the resident profile for <strong>{{ $resident->fname }} {{ $resident->lname }}</strong>. This action cannot be undone.
        </x-delete-modal>
    @endforeach

    {{-- Export CSV Modal --}}
    <flux:modal name="export-csv-confirm" class="min-w-[28rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Export to CSV</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to export the resident records to a CSV file? This may take a moment depending on the number of records.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                
                <flux:modal.close>
                    <flux:button href="{{ route('official.residents.export.csv') }}" variant="primary" icon="arrow-up-tray">
                        Yes, Export CSV
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Export PDF Modal --}}
    <flux:modal name="export-pdf-confirm" class="min-w-[28rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Export to PDF</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to export the resident records to a PDF document? This may take a moment depending on the number of records.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                
                <flux:modal.close>
                    <flux:button href="{{ route('official.residents.export.pdf') }}" variant="primary" icon="document-arrow-up">
                        Yes, Export PDF
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

</x-layouts::app>