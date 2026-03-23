<x-layouts::app :title="__('Resident Management')">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Residents</flux:breadcrumbs.item>
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
                <flux:button href="{{ route('admin.residents.create') }}" variant="primary" icon="plus">
                    New Resident
                </flux:button>
            </div>
        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ url()->current() }}" x-on:change="$el.submit()" class="flex flex-col gap-4">
            @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif

            {{-- Resident Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                
                {{-- Sitio Filter --}}
                <flux:dropdown>
                    @php $currentSitios = (array) request('sitios', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentSitios) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentSitios) ? count($currentSitios) . ' Sitios' : 'Sitio' }}
                    </flux:badge>
                    <flux:menu class="w-48 p-3 space-y-2 max-h-72 overflow-y-auto">
                        <flux:heading size="sm" class="mb-2">Filter Sitio</flux:heading>
                        @foreach($global_sitios as $sitio)
                            <flux:checkbox name="sitios[]" value="{{ $sitio }}" label="{{ $sitio }}" :checked="in_array($sitio, $currentSitios)" />
                        @endforeach
                    </flux:menu>
                </flux:dropdown>

                {{-- Status Filter --}}
                <flux:dropdown>
                    @php $currentStatuses = request('statuses', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentStatuses) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentStatuses) ? count($currentStatuses) . ' Statuses' : 'Status' }}
                    </flux:badge>
                    <flux:menu class="w-48 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Status</flux:heading>
                        <flux:checkbox name="statuses[]" value="active" label="Active" :checked="in_array('active', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="inactive" label="Inactive" :checked="in_array('inactive', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="deceased" label="Deceased" :checked="in_array('deceased', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="pending" label="Pending" :checked="in_array('pending', $currentStatuses)" />
                        <flux:checkbox name="statuses[]" value="transferred" label="Transferred" :checked="in_array('transferred', $currentStatuses)" />
                    </flux:menu>
                </flux:dropdown>

                {{-- Sector Filter --}}
                <flux:dropdown>
                    @php $currentSectors = request('sectors', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentSectors) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentSectors) ? count($currentSectors) . ' Sectors' : 'Sector' }}
                    </flux:badge>
                    <flux:menu class="w-56 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Filter Sectors</flux:heading>
                        <flux:checkbox name="sectors[]" value="senior_citizen" label="Senior Citizen" :checked="in_array('senior_citizen', $currentSectors)" />
                        <flux:checkbox name="sectors[]" value="is_pwd" label="PWD" :checked="in_array('is_pwd', $currentSectors)" />
                        <flux:checkbox name="sectors[]" value="solo_parent" label="Solo Parent" :checked="in_array('solo_parent', $currentSectors)" />
                        <flux:checkbox name="sectors[]" value="is_4ps" label="4Ps Beneficiary" :checked="in_array('is_4ps', $currentSectors)" />
                        <flux:checkbox name="sectors[]" value="voter" label="Registered Voter" :checked="in_array('voter', $currentSectors)" />
                        <flux:checkbox name="sectors[]" value="ofw" label="OFW" :checked="in_array('ofw', $currentSectors)" />
                        <flux:checkbox name="sectors[]" value="unemployed" label="Unemployed" :checked="in_array('unemployed', $currentSectors)" />
                        <flux:checkbox name="sectors[]" value="out_of_school_children" label="Out of School Youth" :checked="in_array('out_of_school_children', $currentSectors)" />    
                        <flux:checkbox name="sectors[]" value="osa" label="OSA" :checked="in_array('osa', $currentSectors)" />  
                        <flux:checkbox name="sectors[]" value="laborforce" label="Part of Labor Force" :checked="in_array('laborforce', $currentSectors)" />    
                        <flux:checkbox name="sectors[]" value="isy_isc" label="ISY/ISC" :checked="in_array('isy_isc', $currentSectors)" />    
                    </flux:menu>
                </flux:dropdown>

                {{-- Clear Filters --}}
                @if(request('statuses') || request('sectors') || request('sitios') || request('is_family_head') || request('search'))
                    <flux:button href="{{ route('admin.residents.index', ['type' => request('type')]) }}" size="sm" variant="subtle" icon="x-mark">
                        Clear
                    </flux:button>
                @endif
            </div>

            {{-- Toolbar: Search & Actions --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Search Input --}}
                <div class="flex-1 max-w-sm">
                    <flux:input 
                        name="search" 
                        value="{{ request('search') }}" 
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
                            {{-- We add x-on:change.stop here so checking these doesn't submit the filter form --}}
                            <flux:checkbox x-model="cols.id" label="Resident ID" x-on:change.stop />
                            <flux:checkbox x-model="cols.resident" label="Resident" x-on:change.stop />
                            <flux:checkbox x-model="cols.address" label="Address" x-on:change.stop />
                            <flux:checkbox x-model="cols.status" label="Status" x-on:change.stop />
                            <flux:checkbox x-model="cols.sector" label="Sector" x-on:change.stop />
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
        </form>

        {{-- Table Container --}}
        <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
            <flux:table class="whitespace-nowrap bg-transparent">
                <flux:table.columns>
                {{-- Checkbox Column --}}
                    <flux:table.column align="center">
                        <flux:checkbox />
                    </flux:table.column>

                    {{-- Remove w-full from here --}}
                    <flux:table.column x-show="cols.id">Resident ID</flux:table.column>
                    
                    {{-- Add w-full here --}}
                    <flux:table.column x-show="cols.resident" >Resident</flux:table.column>
                    
                    <flux:table.column x-show="cols.address">Address</flux:table.column>
                    <flux:table.column x-show="cols.status">Status</flux:table.column>
                    <flux:table.column x-show="cols.sector" >Sector</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($residents as $resident)
                        {{-- Added cursor-pointer and onclick to the row --}}
                        <flux:table.row 
                            :key="$resident->id" 
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('admin.residents.show', $resident->id) }}'"
                        >
                            {{-- Row Checkbox --}}
                            <flux:table.cell onclick="event.stopPropagation()">
                                <div class="flex justify-center">
                                    <flux:checkbox />
                                </div>
                            </flux:table.cell>

                            {{-- ID --}}
                            <flux:table.cell x-show="cols.id" >
                                RES-{{ str_pad($resident->id, 4, '0', STR_PAD_LEFT) }}
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.resident">
                                {{ $resident->fname }} {{ $resident->lname }}
                                    @php
                                        $initials = strtoupper(substr($resident->fname, 0, 1) . substr($resident->lname, 0, 1));
                                    @endphp

                            </flux:table.cell>

                            {{-- Address --}}
                            <flux:table.cell x-show="cols.address" class="text-zinc-600 dark:text-zinc-300">
                                @if($resident->household)
                                    {{ $resident->household->sitio ? 'Sitio ' . $resident->household->sitio : '' }}
                                @else
                                    <span class="text-zinc-500">N/A</span>
                                @endif
                            </flux:table.cell>

                            {{-- Status Badge --}}
                            <flux:table.cell x-show="cols.status">
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
                                <flux:badge rounded color="{{ $badgeConfig['color'] }}" size="sm" class="rounded-full px-2.5">
                                    {{ $badgeConfig['label'] }}
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Sectors --}}
                            <flux:table.cell align="center" x-show="cols.sector">
                                @php
                                    $activeSectors = [];
                                    if ($resident->senior_citizen) $activeSectors[] = ['label' => 'Senior Citizen', 'color' => 'amber'];
                                    if ($resident->is_pwd) $activeSectors[] = ['label' => 'PWD', 'color' => 'red'];
                                    if ($resident->solo_parent) $activeSectors[] = ['label' => 'Solo Parent', 'color' => 'purple'];
                                    if ($resident->is_4ps) $activeSectors[] = ['label' => '4Ps Beneficiary', 'color' => 'emerald'];
                                    if ($resident->ofw) $activeSectors[] = ['label' => 'OFW', 'color' => 'sky'];
                                    if ($resident->unemployed) $activeSectors[] = ['label' => 'Unemployed', 'color' => 'rose'];
                                    if ($resident->voter) $activeSectors[] = ['label' => 'Registered Voter', 'color' => 'indigo'];
                                    if ($resident->out_of_school_children) $activeSectors[] = ['label' => 'Out of School Youth', 'color' => 'orange'];
                                    if ($resident->osa) $activeSectors[] = ['label' => 'OSA', 'color' => 'cyan'];
                                    if ($resident->laborforce) $activeSectors[] = ['label' => 'Part of Labor Force', 'color' => 'teal'];
                                    if ($resident->isy_isc) $activeSectors[] = ['label' => 'ISY / ISC', 'color' => 'pink'];

                                    $totalSectors = count($activeSectors);
                                    $limit = 4;
                                    $visibleSectors = array_slice($activeSectors, 0, $limit);
                                    $hiddenCount = $totalSectors - $limit;
                                @endphp

                                @if($totalSectors > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($visibleSectors as $sector)
                                            <flux:badge rounded color="{{ $sector['color'] }}" size="sm">
                                                {{ $sector['label'] }}
                                            </flux:badge>
                                        @endforeach

                                        {{-- Show the remaining count if there are more than 4 --}}
                                        @if($hiddenCount > 0)
                                            <flux:badge rounded color="zinc" size="sm">
                                                +{{ $hiddenCount }}
                                            </flux:badge>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">N/A</span>
                                @endif
                            </flux:table.cell>

                            {{-- Actions (Added event.stopPropagation() so clicking the menu doesn't trigger the row click) --}}
                            <flux:table.cell onclick="event.stopPropagation()">
                                <flux:dropdown>
                                    <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" class="text-zinc-400 hover:text-white" />
                                    <flux:menu>
                                        <flux:menu.item href="{{ route('admin.residents.show', $resident->id) }}" icon="eye">View Details</flux:menu.item>
                                        <flux:menu.item href="{{ route('admin.residents.edit', $resident->id) }}" icon="pencil-square">Edit</flux:menu.item>
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
        <form action="{{ route('admin.residents.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <flux:heading size="lg">Import residentsvia CSV</flux:heading>
                <flux:text class="mt-2">
                    Upload a valid CSV file to bulk import admin.residents. Make sure your column headers exactly match the required database fields.
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
            action="{{ route('admin.residents.destroy', $resident->id) }}"
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
                    <flux:button href="{{ route('admin.residents.export.csv') }}" variant="primary" icon="arrow-up-tray">
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
                    <flux:button href="{{ route('admin.residents.export.pdf') }}" variant="primary" icon="document-arrow-up">
                        Yes, Export PDF
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

</x-layouts::app>