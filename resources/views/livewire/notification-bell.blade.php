<div>
    <flux:dropdown position="bottom" align="end">
        {{-- 1. The Trigger: Flux automatically uses this first element as the clickable trigger --}}
        <flux:button variant="subtle" class="relative w-10 h-10 flex items-center justify-center !p-0" aria-label="Notifications">
            
            {{-- We moved the icon inside so the button layout stays perfectly centered --}}
            <flux:icon name="bell" class="w-5 h-5 text-zinc-600 dark:text-zinc-300" />
            
            {{-- Unread Number Badge --}}
            @if($this->unreadCount > 0)
                <div class="absolute -top-1.5 -right-1.5 flex items-center justify-center min-w-[20px] h-[20px] px-1 text-[10px] font-bold text-white bg-red-500 border-2 border-white dark:border-zinc-900 rounded-full z-10">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </div>
            @endif

        </flux:button>

        {{-- 2. The Content: Use flux:menu --}}
        <flux:menu class="w-80 !p-0 overflow-hidden">
            
            {{-- Header --}}
            <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-800 flex justify-between items-center bg-zinc-50 dark:bg-zinc-900/50">
                <span class="font-semibold text-sm text-zinc-800 dark:text-zinc-200">Notifications</span>
                @if($this->unreadCount > 0)
                    <button wire:click.stop="markAllAsRead" class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 hover:underline transition-colors">
                        Mark all as read
                    </button>
                @endif
            </div>

            {{-- Notification List --}}
            <div class="max-h-96 overflow-y-auto">
                @forelse($this->notifications as $notification)
                    <div 
                        wire:click="markAsRead('{{ $notification->id }}')"
                        class="px-4 py-3 border-b border-zinc-100 dark:border-zinc-800 cursor-pointer transition-colors {{ $notification->read_at ? 'opacity-60 hover:bg-zinc-50 dark:hover:bg-zinc-800/50' : 'bg-blue-50/50 dark:bg-blue-900/10 hover:bg-blue-50 dark:hover:bg-blue-900/20' }}"
                    >
                        <div class="flex items-start gap-3">
                            <flux:icon :name="$notification->data['icon'] ?? 'bell'" class="mt-0.5 text-zinc-400 shrink-0" variant="micro" />
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                    {{ $notification->data['title'] }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 line-clamp-2">
                                    {{ $notification->data['message'] }}
                                </p>
                                <p class="text-[10px] text-zinc-400 mt-1.5 uppercase tracking-wider font-medium">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-zinc-500 flex flex-col items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
                            <flux:icon name="bell-slash" class="text-zinc-400 dark:text-zinc-500 h-5 w-5" />
                        </div>
                        <p>No new notifications right now.</p>
                    </div>
                @endforelse
            </div>
            
            {{-- Optional Footer (Only visible to Admins) --}}
            @if(auth()->user()->role === 'admin')
                <div class="px-4 py-2.5 text-center border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer" wire:navigate href="{{ route('settings.logs') }}">
                    <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">View all logs</span>
                </div>
            @endif
        </flux:menu>
    </flux:dropdown>
</div>