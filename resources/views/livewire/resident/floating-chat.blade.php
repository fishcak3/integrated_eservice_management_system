{{-- Added wire:poll.3s to the root so it checks for replies even when closed --}}
<div wire:poll.3s class="fixed bottom-5 right-5 z-50">
    
    {{-- Floating Toggle Button --}}
    {{-- Added 'relative' class to support the absolute badge --}}
    <button wire:click="toggleChat" class="relative bg-primary hover:bg-green-600 text-white w-14 h-14 rounded-full shadow-lg transition-all transform hover:scale-110 flex items-center justify-center">
        
        {{-- Notification Badge --}}
        @if(isset($unreadCount) && $unreadCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 border-2 border-white dark:border-zinc-900 rounded-full -translate-y-1 translate-x-1">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif

        @if(!$isOpen)
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        @endif
    </button>

    @if($isOpen)
        <div class="absolute bottom-20 right-0 w-[350px] md:w-[400px] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col h-[550px] max-h-[80vh]">
            
            {{-- Chat Header --}}
            <div class="px-5 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shrink-0 z-10 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <div>
                        <h3 class="font-semibold text-sm text-zinc-900 dark:text-zinc-100">Barangay Assistant</h3>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Online | Ask me about our services</p>
                    </div>
                </div>
            </div>

            {{-- Messages Area --}}
            {{-- Removed wire:poll from here since the parent div handles it now --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-5 bg-white dark:bg-[#0f0f11]" 
                 id="chat-content"
                 x-data
                 x-init="
                    $el.scrollTop = $el.scrollHeight;
                    let observer = new MutationObserver(() => $el.scrollTop = $el.scrollHeight);
                    observer.observe($el, { childList: true });
                 ">
                 
                {{-- Bot Welcome Message & FAQ Chips --}}
                <div class="flex flex-col items-start w-full">
                    <span class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-1 px-1">Barangay Bot</span>
                    <div class="max-w-[85%] px-4 py-2.5 rounded-2xl text-[14px] shadow-sm leading-relaxed bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 rounded-tl-sm border border-zinc-200 dark:border-zinc-700/50">
                        Hello! I am your Barangay Assistant. How can I help you today?
                    </div>

                    {{-- FAQ Chips --}}
                    @if(auth()->check() && count($faqs) > 0)
                        <div class="flex flex-wrap gap-2 mt-3 w-full pl-1">
                            @foreach($faqs as $keyword => $response)
                                <button wire:click="sendFaq('{{ $keyword }}')" 
                                        class="text-[12px] bg-white dark:bg-zinc-900 border border-primary text-primary hover:bg-primary hover:text-white px-3 py-1.5 rounded-full transition-colors cursor-pointer text-left shadow-sm">
                                    {{ $keyword }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(auth()->check())
                    @foreach($messages as $msg)
                        {{-- CRUCIAL: Added wire:key and changed strict === to loose == --}}
                        <div wire:key="chat-{{ $msg->id }}" class="flex flex-col w-full {{ $msg->sender_id == auth()->id() ? 'items-end' : 'items-start' }}">
                            
                            {{-- Sender Name Label --}}
                            <span class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-1 px-1">
                                @if(is_null($msg->sender_id))
                                    Barangay Bot
                                @elseif($msg->sender_id == auth()->id())
                                    You
                                @else
                                    Admin ({{ $msg->sender->name ?? 'Support' }})
                                @endif
                            </span>
                            
                            {{-- Message Bubble --}}
                            <div class="max-w-[85%] px-4 py-2.5 rounded-2xl text-[14px] shadow-sm leading-relaxed 
                                {{ $msg->sender_id == auth()->id() 
                                    ? 'bg-primary text-white rounded-tr-sm' 
                                    : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-100 rounded-tl-sm border border-zinc-200 dark:border-zinc-700/50' }}">
                                {{ $msg->message }}
                            </div>

                        </div>
                    @endforeach
                @else
                    {{-- Not Logged In State --}}
                    <div class="flex flex-col items-center justify-center pt-10 text-center">
                        <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800/50 flex items-center justify-center mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 max-w-[200px]">Please log in to your account to chat with a live Admin.</p>
                    </div>
                @endif
            </div>

            {{-- Input Area --}}
            <div class="p-4 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shrink-0">
                <div class="flex items-center gap-2">
                    <input type="text" wire:model="newMessage" wire:keydown.enter="sendMessage" placeholder="Type a message..." 
                           class="flex-1 bg-zinc-100 dark:bg-zinc-800/50 border border-transparent focus:border-primary rounded-full px-5 py-2.5 text-sm focus:ring-0 text-zinc-800 dark:text-white dark:placeholder-zinc-500 outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                           {{ !auth()->check() ? 'disabled' : '' }}>
                           
                    <button wire:click="sendMessage" class="bg-primary hover:bg-green-600 text-white w-10 h-10 rounded-full flex items-center justify-center transition-all shadow-sm shrink-0 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-primary" {{ !auth()->check() ? 'disabled' : '' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-90 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </div>
            </div>
            
        </div>
    @endif
</div>