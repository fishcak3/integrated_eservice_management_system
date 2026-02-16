<x-layouts::app :title="__('Former Officials')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4">

        {{-- Header --}}
        <div>
            <flux:heading size="lg">Former Officials</flux:heading>
            <flux:subheading>History of past barangay officials and their terms.</flux:subheading>
        </div>

        {{-- Navigation Tabs --}}
        <div class="border-b border-zinc-200 dark:border-zinc-700 px-2">
            <flux:navbar scrollable>
                <flux:navbar.item href="{{ route('officials.index') }}">
                    Current Officials
                </flux:navbar.item>

                {{-- Active Tab --}}
                <flux:navbar.item href="{{ route('officials.former') }}" current>
                    Former Officials
                </flux:navbar.item>

                <flux:navbar.item href="{{ route('positions.posIndex') }}">
                    Manage Positions
                </flux:navbar.item>
            </flux:navbar>
        </div>

        {{-- Main Content --}}
        <flux:card class="flex-1 p-0 overflow-hidden">
            
            {{-- Search Toolbar --}}
            <div class="flex flex-col justify-between gap-4 p-4 sm:flex-row sm:items-center border-b border-zinc-200 dark:border-zinc-700">
                <div class="relative w-full sm:w-80">
                    <form method="GET">
                        <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search history..." /> 
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <flux:table :paginate="$officials">
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Former Position</flux:table.column>
                    <flux:table.column>Term Duration</flux:table.column>
                    <flux:table.column align="end">Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($officials as $official)
                        <flux:table.row :key="$official->id">
                            
                            {{-- Name --}}
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar src="{{ $official->resident->avatar_url ?? '' }}" initials="{{ substr($official->resident->fname, 0, 1) }}" />
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $official->resident->full_name }}
                                    </div>
                                </div>
                            </flux:table.cell>

                            {{-- Position --}}
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">{{ $official->position->title }}</flux:badge>
                            </flux:table.cell>

                            {{-- Term Dates --}}
                            <flux:table.cell>
                                <div class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($official->date_start)->format('M Y') }} - 
                                    {{ $official->date_end ? \Carbon\Carbon::parse($official->date_end)->format('M Y') : 'Unknown' }}
                                </div>
                            </flux:table.cell>

                            {{-- Actions --}}
                            <flux:table.cell align="end">
                                <flux:button href="{{ route('officials.show', $official->id) }}" size="xs" variant="ghost" icon="eye">
                                    View
                                </flux:button>
                            </flux:table.cell>

                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="text-center text-gray-500 py-6">
                                No former officials records found.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>

        </flux:card>
    </div>
</x-layouts::app>