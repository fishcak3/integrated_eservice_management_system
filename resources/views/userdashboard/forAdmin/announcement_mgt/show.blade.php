<x-layouts::app :title="$announcement->title">

    <x-slot name="header">
        <flux:breadcrumbs class="mb-2">
            <flux:breadcrumbs.item href="{{ route('announcements.index') }}">Announcements</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>View</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $announcement->title }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>
    </x-slot>

    {{-- MAIN CONTENT --}}
    {{-- Removed 'h-full' here so the page can grow and scroll --}}
    <div class="flex w-full flex-1 flex-col gap-6 rounded-xl p-0">
        
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
                         {{-- 1. Status Badge --}}
                         @php
                            $isExpired = $announcement->expires_at && $announcement->expires_at->isPast();
                            $badgeColor = $isExpired ? 'red' : ($announcement->status === 'published' ? 'green' : 'zinc');
                            $statusLabel = $isExpired ? 'Expired' : ucfirst($announcement->status);
                         @endphp
                         
                         <flux:badge :color="$badgeColor">{{ $statusLabel }}</flux:badge>

                        {{-- 2. Publish Date --}}
                        <flux:badge color="zinc" inset="top bottom">
                            {{ $announcement->publish_at ? $announcement->publish_at->format('M d, Y') : $announcement->created_at->format('M d, Y') }}
                        </flux:badge>
                    </div>

                    {{-- Heading --}}
                    <div>
                        <flux:heading size="xl" level="1">{{ $announcement->title }}</flux:heading>
                        <flux:subheading>
                            Posted by {{ $announcement->author->name ?? 'Barangay Official' }} 
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
                <div class="flex flex-col">
                    @if($announcement->expires_at)
                        <span class="text-xs text-zinc-500">Expires: {{ $announcement->expires_at->format('M d, Y h:i A') }}</span>
                    @else
                        <span class="text-xs text-zinc-500">No expiration date</span>
                    @endif
                </div>

                <flux:button href="{{ route('announcements.edit', $announcement) }}" size="sm" icon="pencil-square">
                    Edit Announcement
                </flux:button>
            </div>
        </flux:card>
    </div>
</x-layouts::app>