<div wire:poll.2s class="max-w-3xl mx-auto h-[80vh] flex flex-col bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
    
    <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
        <flux:heading size="lg">Support Chat</flux:heading>
        <flux:text size="sm" class="text-zinc-500">Chat directly with the Barangay Administration.</flux:text>
    </div>

    <div class="flex-1 overflow-y-auto p-4 space-y-4"
         x-data
         x-init="
            $el.scrollTop = $el.scrollHeight;
            let observer = new MutationObserver(() => $el.scrollTop = $el.scrollHeight);
            observer.observe($el, { childList: true });
         ">
        @forelse($messages as $msg)
            <div class="flex flex-col {{ $msg->sender_id === auth()->id() ? 'items-end' : 'items-start' }}">
                <span class="text-xs text-zinc-500 mb-1">
                    {{ $msg->sender->first_name }}
                    @if($msg->sender_id !== auth()->id()) <span class="text-primary">(Admin)</span> @endif
                </span>
                
                <div class="max-w-[80%] p-3 rounded-2xl {{ $msg->sender_id === auth()->id() ? 'bg-primary text-white rounded-tr-none' : 'bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-100 rounded-tl-none' }}">
                    {{ $msg->message }}
                </div>
            </div>
        @empty
            <div class="text-center text-zinc-500 mt-10">
                No messages yet. Send a message to start a conversation with an admin!
            </div>
        @endforelse
    </div>

    <div class="p-4 border-t border-zinc-200 dark:border-zinc-700 flex gap-2" wire:ignore>
        <flux:input wire:model="newMessage" wire:keydown.enter="sendMessage" placeholder="Type your message..." class="flex-1" />
        <flux:button wire:click="sendMessage" variant="primary">Send</flux:button>
    </div>
</div>