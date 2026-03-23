<div x-data="chatbot({{ auth()->check() ? 'true' : 'false' }}, @js($chatFaqs))" class="fixed bottom-5 right-5 z-50">
    {{-- Floating Toggle Button --}}
    <button @click="toggleChat" class="bg-[#16a34a] hover:bg-green-500 text-white w-14 h-14 rounded-full shadow-lg transition-all transform hover:scale-110 flex items-center justify-center">
        <svg x-show="!isOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
        <svg x-show="isOpen" style="display: none;" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    {{-- Main Chat Window --}}
    <div x-show="isOpen" style="display: none;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         class="absolute bottom-20 right-0 w-[350px] md:w-[400px] bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col h-[550px] max-h-[80vh]">
        
        {{-- Chat Header (Solid Green) --}}
        <div class="px-5 py-4 bg-[#16a34a] shrink-0 z-10 flex justify-between items-center rounded-t-2xl">
            <div class="flex items-center gap-3">
                <div>
                    <h3 class="font-semibold text-sm text-white">Barangay Assistant</h3>
                    <p class="text-[11px] text-green-100">Ask me about our services</p>
                </div>
            </div>
        </div>

        {{-- Messages Area (Light Background) --}}
        <div class="flex-1 overflow-y-auto p-5 space-y-5 bg-white" id="chat-content">
            
            {{-- Static Bot Welcome Message & FAQ Chips --}}
            <div class="flex flex-col items-start w-full">
                <span class="text-[11px] text-gray-500 mb-1 px-1">Barangay Bot</span>
                <div class="max-w-[85%] px-4 py-2.5 rounded-2xl text-[14px] shadow-sm leading-relaxed bg-gray-100 text-gray-800 rounded-tl-sm border border-gray-200">
                    Hello! I am your Barangay Assistant. How can I help you today?
                </div>

                {{-- Quick Replies / Chips --}}
                <div class="flex flex-wrap gap-2 mt-3 w-full pl-1" x-show="showQuickReplies">
                    <template x-for="faq in Object.keys(kb).filter(k => !['hello', 'hi'].includes(k))" :key="faq">
                        <button @click="userInput = faq; sendMessage()" 
                                class="text-[12px] bg-white border border-[#16a34a] text-[#16a34a] hover:bg-[#16a34a] hover:text-white px-3.5 py-1.5 rounded-full transition-colors cursor-pointer text-left shadow-sm capitalize"
                                x-text="faq">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Dynamic Messages Loop --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div class="flex flex-col w-full" :class="msg.role === 'user' ? 'items-end' : 'items-start'">
                    
                    {{-- Sender Name Label --}}
                    <span class="text-[11px] text-gray-500 mb-1 px-1" x-text="msg.role === 'user' ? 'You' : 'Barangay Bot'"></span>
                    
                    {{-- Message Bubble --}}
                    <div class="max-w-[85%] px-4 py-2.5 rounded-2xl text-[14px] shadow-sm leading-relaxed" 
                         :class="msg.role === 'user' 
                            ? 'bg-[#16a34a] text-white rounded-tr-sm' 
                            : 'bg-gray-100 text-gray-800 rounded-tl-sm border border-gray-200'" 
                         x-text="msg.text">
                    </div>
                </div>
            </template>
        </div>

        {{-- Input Area --}}
        <div class="p-4 bg-white shrink-0 border-t border-gray-200">
            <div class="flex items-center gap-2">
                <input type="text" x-model="userInput" @keyup.enter="sendMessage" placeholder="Type a message..." 
                       class="flex-1 bg-gray-100 border border-transparent focus:border-[#16a34a] rounded-lg px-4 py-2.5 text-sm focus:ring-0 text-gray-800 placeholder-gray-500 outline-none transition-all">
                
                <button @click="sendMessage" class="bg-[#16a34a] hover:bg-green-500 text-white w-10 h-10 rounded-lg flex items-center justify-center transition-all shadow-sm shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform rotate-90 ml-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function chatbot(isLoggedIn, dynamicFaqs) {
    return {
        isOpen: false,
        userInput: '',
        isLoggedIn: isLoggedIn,
        messages: [], 
        kb: dynamicFaqs, 
        quickReplies: [],
        showQuickReplies: true,

        init() {
            if (this.kb) {
                this.quickReplies = Object.keys(this.kb).slice(0, 4).map(key => {
                    return key.charAt(0).toUpperCase() + key.slice(1);
                });
            }
        },
        
        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    const el = document.getElementById('chat-content');
                    el.scrollTop = el.scrollHeight;
                });
            }
        },
        
        sendMessage() {
            if (this.userInput.trim() === '') return;

            const originalInput = this.userInput;
            const input = originalInput.toLowerCase();
            
            this.messages.push({ role: 'user', text: originalInput });
            this.userInput = '';

            this.$nextTick(() => {
                const el = document.getElementById('chat-content');
                el.scrollTop = el.scrollHeight;
            });

            setTimeout(() => {
                let response = "";
                let foundMatch = false;
                
                for (let key in this.kb) {
                    if (input.includes(key)) {
                        let answer = this.kb[key];
                        
                        if (typeof answer === 'object' && answer !== null) {
                            response = this.isLoggedIn ? answer.auth : answer.guest;
                        } else {
                            response = answer; 
                        }
                        
                        response = String(response);
                        foundMatch = true;
                        break;
                    }
                }

                // Pure bot fallback with no admin forwarding logic
                if (!foundMatch) {
                    response = "I'm sorry, I don't have the answer to that right now. Please ask me about our barangay services, or try rephrasing your question!";
                }

                this.messages.push({ role: 'bot', text: response });
                
                this.$nextTick(() => {
                    const el = document.getElementById('chat-content');
                    el.scrollTop = el.scrollHeight;
                });
            }, 500);
        }
    }
}
</script>