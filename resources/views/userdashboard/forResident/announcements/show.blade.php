<x-layouts::app :title="$announcement->title">

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto">
        
        {{-- Back Navigation --}}
        <div class="flex items-center gap-4 mb-2">
            <flux:button href="{{ route('resident.announcements.index') }}" variant="ghost" icon="arrow-left" size="sm">
                Back to Announcements
            </flux:button>
        </div>

        {{-- Main Announcement Card --}}
        <flux:card class="overflow-hidden p-0">
            
            {{-- Cover Image --}}
            @if($announcement->image_url)
                <img 
                    src="{{ $announcement->image_url }}" 
                    alt="{{ $announcement->title }}" 
                    class="w-full h-64 sm:h-80 md:h-[400px] object-cover border-b border-zinc-200 dark:border-zinc-700"
                />
            @endif

            <div class="p-6 sm:p-8 md:p-10">
                {{-- Header / Meta Information --}}
                <div class="mb-8 border-b border-zinc-200 dark:border-zinc-700 pb-6">
                    <flux:heading size="xl" class="mb-4 text-2xl sm:text-3xl font-bold">
                        {{ $announcement->title }}
                    </flux:heading>
                    
                    <div class="flex flex-wrap items-center gap-4 text-sm text-zinc-500">
                        <span class="flex items-center gap-1.5">
                            <flux:icon name="calendar" class="w-4 h-4" />
                            {{ $announcement->publish_at ? $announcement->publish_at->format('F j, Y \a\t h:i A') : $announcement->created_at->format('F j, Y') }}
                        </span>
                        
                        @if($announcement->author)
                            <span class="flex items-center gap-1.5">
                                <flux:icon name="user" class="w-4 h-4" />
                                Posted by: {{ $announcement->author->name }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Announcement Content --}}
                {{-- If you use a rich-text editor (HTML), use {!! !!} --}}
                {{-- If it's just a plain textarea, use: {!! nl2br(e($announcement->content)) !!} --}}
                <div class="prose prose-zinc dark:prose-invert max-w-none text-base leading-relaxed">
                    {!! $announcement->content !!}
                </div>
            </div>

        </flux:card>
    </div>

</x-layouts::app>