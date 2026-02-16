<x-layouts::app :title="$announcement->title">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('announcements.index') }}">Announcements</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>View</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $announcement->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
        {{-- Page Header & Actions --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">Announcement Details</flux:heading>
                <flux:subheading>View the full details and content of this announcement.</flux:subheading>
            </div>
            
            <flux:button href="{{ route('announcements.index') }}" variant="ghost" icon="arrow-left" size="sm">
                Back to List
            </flux:button>
        </div>

        <flux:card class="p-0 overflow-hidden">
            {{-- Cover Image --}}
            @if($announcement->cover_image)
                <div class="w-full h-64 bg-zinc-100 dark:bg-zinc-800">
                    <img src="{{ Storage::url($announcement->cover_image) }}" alt="Cover" class="w-full h-full object-cover">
                </div>
            @endif

            <div class="p-6 sm:p-8 space-y-6">
                {{-- Card Header --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2 mb-2">
                         @if($announcement->priority === 'emergency')
                            <flux:badge color="red">Emergency</flux:badge>
                        @elseif($announcement->priority === 'high')
                            <flux:badge color="orange">Important</flux:badge>
                        @endif

                        <flux:badge color="zinc" inset="top bottom">{{ $announcement->created_at->format('M d, Y') }}</flux:badge>
                    </div>

                    {{-- Updated to Flux Heading/Subheading --}}
                    <div>
                        <flux:heading size="xl" level="1">{{ $announcement->title }}</flux:heading>
                        <flux:subheading>
                            Posted by {{ $announcement->author->name ?? 'Admin' }}
                        </flux:subheading>
                    </div>
                </div>

                <flux:separator />

                {{-- Content --}}
                <div class="prose dark:prose-invert max-w-none text-zinc-600 dark:text-zinc-300 whitespace-pre-line">
                    {{ $announcement->content }}
                </div>
            </div>

            {{-- Footer / Actions --}}
            <div class="bg-zinc-50 dark:bg-zinc-900/50 p-4 flex justify-between items-center border-t border-zinc-200 dark:border-zinc-700">
                <span class="text-xs text-zinc-500">Status: {{ ucfirst($announcement->status) }}</span>
                <flux:button href="{{ route('announcements.edit', $announcement->id) }}" size="sm" icon="pencil-square">
                    Edit Announcement
                </flux:button>
            </div>
        </flux:card>
    </div>
</x-layouts::app>