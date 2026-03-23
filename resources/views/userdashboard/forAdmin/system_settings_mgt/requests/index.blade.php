<x-layouts::app>

    <x-slot:header>
        @if(request('type') === 'complaint')
            <flux:breadcrumbs class="mb-2">
                <flux:breadcrumbs.item>Settings</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('settings.request', ['type' => 'complaint']) }}" :current="true">Complaints</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        @else
            <flux:breadcrumbs class="mb-2">
                <flux:breadcrumbs.item>Settings</flux:breadcrumbs.item>
                <flux:breadcrumbs.item href="{{ route('settings.request', ['type' => 'document']) }}" :current="true">Document Requests</flux:breadcrumbs.item>
            </flux:breadcrumbs>
        @endif
    </x-slot:header>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Page Header --}}
        {{-- Checking the request type to swap the header --}}
        @if(request('type', 'document') === 'document')
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="lg" level="1">Request Settings</flux:heading>
                    <flux:subheading>Configure document fees, requirements, and availability.</flux:subheading>
                </div>

                <flux:button variant="primary" icon="plus" href="{{ route('settings.request.create', ['type' => 'document']) }}" wire:navigate>
                    Add Document Type
                </flux:button>
            </div>
        @else
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="lg" level="1">Complaint Settings</flux:heading>
                    <flux:subheading>Manage complaint categories and their default severity levels.</flux:subheading>
                </div>

                <flux:button variant="primary" icon="plus" href="{{ route('settings.request.create', ['type' => 'complaint']) }}" wire:navigate>
                    Add Complaint Type
                </flux:button>
            </div>
        @endif

        {{-- SUCCESS / ERROR MESSAGES --}}
        @if(session('status'))
            <div style="color: green; font-weight: bold; padding: 10px; border: 1px solid green; background-color: #e6ffe6; border-radius: 5px; margin-bottom: 20px;">
                {{ session('status') }}
            </div>
        @endif

        {{-- Main Content Swap --}}
        @if(request('type', 'document') === 'document')
            
            {{-- SECTION 1: Document Types --}}
            <flux:card class="flex-1 p-0 overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex flex-col md:flex-row md:items-center gap-4">
                    
                    <form method="GET" action="{{ route('settings.request') }}" class="w-full max-w-sm flex items-center gap-2">
                        {{-- Keeps the user on the document tab --}}
                        <input type="hidden" name="type" value="document">
                        
                        <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search documents..." class="w-full"/>
                        <flux:button type="submit" variant="ghost" class="sr-only">Search</flux:button>
                    </form>

                    {{-- Optional: Add a clear button if they are currently searching --}}
                    @if(request('search') && request('type', 'document') === 'document')
                        <flux:button variant="ghost" size="sm" href="{{ route('settings.request', ['type' => 'document']) }}" wire:navigate class="text-zinc-500">
                            Clear
                        </flux:button>
                    @endif

                </div>
                
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Name</flux:table.column>
                        <flux:table.column>Fee</flux:table.column>
                        <flux:table.column>Requirements</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>
                    
                    <flux:table.rows>
                        @forelse ($documentTypes as $doc)
                            <flux:table.row>
                                <flux:table.cell class="font-medium">{{ $doc->name }}</flux:table.cell>
                                <flux:table.cell>₱{{ number_format($doc->fee, 2) }}</flux:table.cell>
                                <flux:table.cell class="text-xs text-zinc-500 max-w-[200px] truncate" title="{{ $doc->requirements }}">
                                    {{ $doc->requirements ?: 'None' }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($doc->is_active)
                                        <flux:badge color="green" size="sm">Active</flux:badge>
                                    @else
                                        <flux:badge color="red" size="sm">Inactive</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" class="text-zinc-600" 
                                        href="{{ route('settings.request.edit', ['type' => 'document', 'id' => $doc->id]) }}" 
                                        wire:navigate>
                                        Edit
                                    </flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center text-zinc-500 py-6">
                                    No document types found. Click "Add Document Type" to create one.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>

        @else

            {{-- SECTION 2: Complaint Types --}}
            <flux:card class="flex-1 p-0 overflow-hidden">
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 flex flex-col md:flex-row md:items-center gap-4">
                    
                    <form method="GET" action="{{ route('settings.request') }}" class="w-full max-w-sm flex items-center gap-2">
                        {{-- Keeps the user on the complaint tab --}}
                        <input type="hidden" name="type" value="complaint">
                        
                        <flux:input name="search" value="{{ request('search') }}" icon="magnifying-glass" placeholder="Search complaints..." class="w-full"/>
                        <flux:button type="submit" variant="ghost" class="sr-only">Search</flux:button>
                    </form>

                    {{-- Optional: Add a clear button if they are currently searching --}}
                    @if(request('search') && request('type') === 'complaint')
                        <flux:button variant="ghost" size="sm" href="{{ route('settings.request', ['type' => 'complaint']) }}" wire:navigate class="text-zinc-500">
                            Clear
                        </flux:button>
                    @endif
                    
                </div>
                
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Name</flux:table.column>
                        <flux:table.column>Severity</flux:table.column>
                        <flux:table.column>Description</flux:table.column>
                        <flux:table.column align="end">Actions</flux:table.column>
                    </flux:table.columns>
                    
                    <flux:table.rows>
                        @forelse ($complaintTypes as $complaint)
                            <flux:table.row>
                                <flux:table.cell class="font-medium">{{ $complaint->name }}</flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $color = match($complaint->severity_level) {
                                            'critical' => 'red',
                                            'high' => 'orange',
                                            'medium' => 'yellow',
                                            default => 'green',
                                        };
                                    @endphp
                                    <flux:badge :color="$color" size="sm">{{ ucfirst($complaint->severity_level) }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="text-xs text-zinc-500 max-w-[250px] truncate" title="{{ $complaint->description }}">
                                    {{ $complaint->description ?: 'No description' }}
                                </flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" class="text-zinc-600" 
                                        href="{{ route('settings.request.edit', ['type' => 'complaint', 'id' => $complaint->id]) }}" 
                                        wire:navigate>
                                        Edit
                                    </flux:button>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="text-center text-zinc-500 py-6">
                                    No complaint types found.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>

        @endif

    </div>
</x-layouts::app>