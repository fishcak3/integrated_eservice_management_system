<x-layouts::app :title="__('Household Management')">

    <x-slot:header>
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item :href="route('admin.dashboard')">Dashboard</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Households</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot:header>

    {{-- MAIN CONTENT --}}
    <div x-data="{
            cols: {
                id: true,
                location: true,
                members: true
            }
         }" 
         class="flex h-full w-full flex-1 flex-col gap-6 p-0">

        {{-- Top Header Area --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl" class="font-bold">Households</flux:heading>
                <flux:subheading class="text-zinc-500 dark:text-zinc-400">Manage and track all household records and family members.</flux:subheading>
            </div>

        </div>

        {{-- Master Form for Filters & Search --}}
        <form method="GET" action="{{ url()->current() }}" x-on:change="$el.submit()" class="flex flex-col gap-4">
            @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif

            {{-- Household Filters --}}
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

                {{-- Family Members Filter --}}
                <flux:dropdown>
                    @php $currentMembers = (array) request('members', []); @endphp
                    <flux:badge as="button" rounded color="{{ !empty($currentMembers) ? 'blue' : 'zinc' }}" icon="chevron-down" size="lg">
                        {{ !empty($currentMembers) ? count($currentMembers) . ' Selected' : 'Family Members' }}
                    </flux:badge>
                    <flux:menu class="w-56 p-3 space-y-2">
                        <flux:heading size="sm" class="mb-2">Member Count</flux:heading>
                        <flux:checkbox name="members[]" value="1-3" label="1 to 3 Members" :checked="in_array('1-3', $currentMembers)" />
                        <flux:checkbox name="members[]" value="4-6" label="4 to 6 Members" :checked="in_array('4-6', $currentMembers)" />
                        <flux:checkbox name="members[]" value="7-9" label="7 to 9 Members" :checked="in_array('7-9', $currentMembers)" />
                        <flux:checkbox name="members[]" value="10+" label="10+ Members" :checked="in_array('10+', $currentMembers)" />
                    </flux:menu>
                </flux:dropdown>

                {{-- Clear Filters --}}
                @if(request('sitios') || request('members') || request('search'))
                    <flux:button href="{{ route('official.residents.household', ['type' => request('type')]) }}" size="sm" variant="subtle" icon="x-mark">
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
                        placeholder="Search Household ID..." 
                        class="w-full bg-transparent dark:bg-zinc-900/50"
                    />
                </div>

                <div class="flex items-center gap-2">
                    {{-- Columns Toggle Dropdown --}}
                    <flux:dropdown>
                        <flux:button cursor="pointer" variant="subtle" icon="adjustments-horizontal">Columns</flux:button>
                        <flux:menu class="w-56 p-3 space-y-2">
                            <flux:heading size="sm" class="mb-2">Toggle columns</flux:heading>
                            <flux:checkbox x-model="cols.id" label="Household ID" x-on:change.stop />
                            <flux:checkbox x-model="cols.location" label="Location / Address" x-on:change.stop />
                            <flux:checkbox x-model="cols.members" label="Family Members" x-on:change.stop />
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

                    <flux:table.column x-show="cols.id">Household ID</flux:table.column>
                    <flux:table.column x-show="cols.location">Location / Address</flux:table.column>
                    <flux:table.column align="center" x-show="cols.members">Family Members</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($households as $household)
                        <flux:table.row 
                            :key="$household->household_id"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('official.residents.household.show', $household->id) }}'"
                        >
                            {{-- Row Checkbox --}}
                            <flux:table.cell onclick="event.stopPropagation()">
                                <div class="flex justify-center">
                                    <flux:checkbox />
                                </div>
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.id">
                                {{ $household->household_number }}
                            </flux:table.cell>

                            <flux:table.cell x-show="cols.location" class="text-zinc-600 dark:text-zinc-300">
                                {{ $household->sitio ? 'Sitio ' . $household->sitio : 'N/A' }}
                            </flux:table.cell>

                            <flux:table.cell align="center" x-show="cols.members">
                                <flux:badge rounded color="blue" size="sm" class="rounded-full px-2.5">
                                    {{ $household->members_count }} Members
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end"  onclick="event.stopPropagation()">
                                    <flux:menu.item href="{{ route('official.residents.household.show', $household->id) }}" icon="eye">View</flux:menu.item>
                            </flux:table.cell>

                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="text-center text-zinc-500 py-8">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.home class="h-10 w-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                                    <p>No households found.</p>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

            {{-- Pagination Footer --}}
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-transparent">
                {{ $households->links() }}
            </div>
        </div>
    </div>

</x-layouts::app>