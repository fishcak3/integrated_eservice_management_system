{{-- Added wire:poll here so it checks for messages even when chat is closed --}}
<div wire:poll.5s class="fixed bottom-5 right-5 z-50">
    
    {{-- Floating Toggle Button --}}
    {{-- Added relative class to position the absolute badge --}}
    <button wire:click="toggleChat" class="relative bg-primary hover:bg-green-600 text-white p-4 rounded-full shadow-lg transition-all transform hover:scale-110 flex items-center justify-center">
        
        {{-- Notification Badge (Total Unread) --}}
        @if($totalUnread > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-500 border-2 border-white dark:border-zinc-900 rounded-full -translate-y-1/4 translate-x-1/4">
                {{ $totalUnread > 99 ? '99+' : $totalUnread }}
            </span>
        @endif

        @if(!$isOpen)
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        @endif
    </button>

    @if($isOpen)
        <div class="absolute bottom-20 right-0 w-[350px] sm:w-[500px] md:w-[750px] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden flex h-[600px] max-h-[80vh]">
            
            {{-- Conversations Sidebar --}}
            <div class="w-[280px] bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-800 flex flex-col shrink-0">
                
                {{-- Search Header --}}
                <div class="p-4 shrink-0 border-b border-zinc-200 dark:border-zinc-800">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-zinc-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input wire:model.live="searchQuery" type="text" placeholder="Search conversations..." 
                               class="w-full pl-9 pr-3 py-2 bg-zinc-200/50 dark:bg-zinc-800/50 border border-transparent rounded-lg text-sm focus:bg-white dark:focus:bg-zinc-800 focus:ring-2 focus:ring-primary focus:border-transparent dark:text-zinc-100 placeholder-zinc-500 transition-all outline-none">
                    </div>
                </div>
                
                {{-- User List --}}
                <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
                    @forelse($conversations as $conversation)
                        <div wire:click="selectResident({{ $conversation->id }})" 
                             class="group relative flex items-center gap-3 p-3 cursor-pointer rounded-xl text-sm transition-all duration-200 
                                    {{ $activeResidentId === $conversation->id 
                                        ? 'bg-zinc-200 dark:bg-zinc-800 shadow-sm' 
                                        : 'hover:bg-zinc-100 dark:hover:bg-zinc-800/50' }}">
                            
                            {{-- Active Indicator Line --}}
                            @if($activeResidentId === $conversation->id)
                                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-primary rounded-r-full"></div>
                            @endif

                            {{-- Avatar & Status --}}
                            <div class="relative shrink-0">
                                @if($conversation->profile_photo_url)
                                    <img src="{{ $conversation->profile_photo_url }}" alt="{{ $conversation->display_name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs uppercase bg-primary/10 text-primary dark:bg-primary/20">
                                        {{ $conversation->initials }}
                                    </div>
                                @endif
                                
                                {{-- Online Indicator --}}
                                <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white dark:border-zinc-900 
                                             {{ $conversation->isOnline() ? 'bg-green-500' : 'bg-zinc-400' }}">
                                </span>
                            </div>

                            {{-- Name & Status Text --}}
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold truncate text-zinc-900 dark:text-zinc-100 
                                          {{ $conversation->unread_count > 0 ? 'font-bold' : '' }}">
                                    {{ $conversation->display_name }}
                                </h4>
                                <p class="text-[12px] truncate {{ $conversation->unread_count > 0 ? 'text-primary font-medium' : 'text-zinc-500 dark:text-zinc-400' }}">
                                    {{ $conversation->unread_count > 0 ? 'New message...' : ($conversation->isOnline() ? 'Online' : 'Offline') }}
                                </p>
                            </div>

                            {{-- Unread Badge per user --}}
                            @if($conversation->unread_count > 0)
                                <div class="shrink-0 bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    {{ $conversation->unread_count }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-zinc-500 space-y-3 px-4 text-center mt-10">
                            <svg class="w-8 h-8 text-zinc-400 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            <span class="text-xs">No conversations found.</span>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Active Chat Area --}}
            <div class="flex-1 flex flex-col bg-white dark:bg-[#0f0f11] relative">
                @if($activeResidentId)
                    {{-- Chat Header --}}
                    <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shrink-0 z-10 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                            <div>
                                <h3 class="font-semibold text-sm text-zinc-900 dark:text-zinc-100">Resident Support</h3>
                                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Replying as Admin</p>
                            </div>
                        </div>
                    </div>

                    {{-- Messages List --}}
                    <div class="flex-1 overflow-y-auto p-5 space-y-5"
                         x-data
                         x-init="
                            $el.scrollTop = $el.scrollHeight;
                            let observer = new MutationObserver(() => $el.scrollTop = $el.scrollHeight);
                            observer.observe($el, { childList: true });
                         ">
                        @foreach($messages as $msg)
                            <div class="flex flex-col w-full {{ $msg->sender_id === auth()->id() ? 'items-end' : 'items-start' }}">
                                
                                {{-- Sender Name Label --}}
                                <span class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-1 px-1">
                                    @if(is_null($msg->sender_id))
                                        Barangay Bot
                                    @elseif($msg->sender_id === auth()->id())
                                        You
                                    @else
                                        {{ $msg->sender->display_name }}
                                    @endif
                                </span>
                                
                                {{-- Message Bubble --}}
                                <div class="max-w-[75%] px-4 py-2.5 rounded-2xl text-[14px] shadow-sm leading-relaxed
                                    {{ $msg->sender_id === auth()->id() 
                                        ? 'bg-primary text-white rounded-tr-sm' 
                                        : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 rounded-tl-sm border border-zinc-200 dark:border-zinc-700/50' }}">
                                    {{ $msg->message }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Input Area --}}
                    <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shrink-0">
                        <div class="flex items-center gap-2" wire:ignore>
                            <input type="text" wire:model="newMessage" wire:keydown.enter="sendMessage" placeholder="Type a message..." 
                                   class="flex-1 bg-zinc-100 dark:bg-zinc-800/50 border border-transparent focus:border-primary rounded-full px-5 py-2.5 text-sm focus:ring-0 text-zinc-800 dark:text-white dark:placeholder-zinc-500 outline-none transition-all">
                            
                            <button wire:click="sendMessage" class="bg-primary hover:bg-green-600 text-white w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-sm shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-90 ml-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="flex-1 flex flex-col items-center justify-center text-zinc-500 p-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-zinc-100 dark:bg-zinc-800/50 flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <h4 class="text-zinc-700 dark:text-zinc-300 font-medium mb-1">Your Messages</h4>
                        <p class="text-[13px] text-zinc-500 dark:text-zinc-500 max-w-[200px]">Select a conversation from the sidebar to view messages.</p>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>