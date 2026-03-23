<x-layouts::app :title="__('All Announcements')">
    
    <x-slot:header>
        <flux:sidebar.header class="font-bold text-lg">
            Integrated E-Service Management System
        </flux:sidebar.header>
    </x-slot:header>

    {{-- Page Header --}}
    <div class="mb-8 flex flex-col gap-2">
        <flux:heading size="xl">Barangay Announcements</flux:heading>
        <flux:subheading>Stay updated with the latest news, events, and advisories in our community.</flux:subheading>
    </div>

    {{-- Announcements Grid --}}
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($announcements as $announcement)
            <flux:card class="flex flex-col !p-0 overflow-hidden h-full hover:shadow-md transition-shadow">
                
                {{-- Cover Image or Fallback Placeholder --}}
                @if($announcement->cover_image)
                    <img src="{{ Storage::url($announcement->cover_image) }}" alt="{{ $announcement->title }}" class="h-48 w-full object-cover">
                @else
                    <div class="h-48 w-full bg-emerald-50 flex items-center justify-center text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-500">
                        <flux:icon.megaphone class="size-12 opacity-50" />
                    </div>
                @endif

                {{-- Content --}}
                <div class="p-5 flex flex-col flex-grow space-y-3">
                    <div>
                        <h3 class="font-bold text-zinc-900 dark:text-zinc-100 line-clamp-2" title="{{ $announcement->title }}">
                            {{ $announcement->title }}
                        </h3>
                        <div class="flex items-center gap-1.5 text-xs text-zinc-500 mt-1">
                            <flux:icon.calendar class="size-4" />
                            <span>{{ ($announcement->publish_at ?? $announcement->created_at)->format('M d, Y') }}</span>
                        </div>
                    </div>
                    
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 line-clamp-3 flex-grow">
                        {{ Str::limit(strip_tags($announcement->content), 120) }}
                    </p>
                    
                    <div class="pt-4 mt-auto">
                        <flux:button href="{{ route('resident.announcements.show', $announcement->slug ?? $announcement->id) }}" variant="subtle" class="w-full">
                            Read Full Announcement
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        @empty
            <div class="col-span-full py-16 text-center text-zinc-500 dark:text-zinc-400">
                <flux:icon.bell-slash class="size-12 mx-auto mb-4 opacity-50" />
                <p class="text-lg font-medium text-zinc-900 dark:text-zinc-100">No announcements yet</p>
                <p class="text-sm mt-1">Check back later for community updates.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination Links --}}
    <div class="mt-8">
        {{ $announcements->links() }}
    </div>

</x-layouts::app>