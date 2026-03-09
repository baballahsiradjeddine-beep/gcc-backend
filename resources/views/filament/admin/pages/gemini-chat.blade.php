<x-filament-panels::page>
    <div class="flex flex-col space-y-4 h-[calc(100vh-250px)]">
        <!-- Model Selection Top Bar -->
        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-3 rtl:space-x-reverse">
                <div class="p-2 bg-primary-100 dark:bg-primary-900 rounded-lg">
                    <x-heroicon-o-cpu-chip class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">الموديل النشط</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">اختر المحرك الذي تود التحدث معه</p>
                </div>
            </div>
            
            <div class="w-64">
                <select wire:model="selectedModel" class="w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    @foreach(\App\Services\GeminiAiService::listModels() ?: [
                        'gemini-1.5-flash-latest' => 'Gemini 1.5 Flash (سريع)',
                        'gemini-1.5-pro-latest' => 'Gemini 1.5 Pro (ذكي)',
                        'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash (تجريبي)',
                    ] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Chat History Area -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50/50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600" id="chat-container">
            @if(empty($chatHistory))
                <div class="flex flex-col items-center justify-center h-full space-y-4 text-center">
                    <div class="p-4 bg-white dark:bg-gray-800 rounded-full shadow-xl animate-bounce">
                        <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 text-primary-500" />
                    </div>
                    <div class="max-w-md">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">أهلاً بك في فضاء Gemini!</h2>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">أنا مساعدك الذكي، يمكنك سؤالي عن أي شيء، تحليل البيانات، أو حتى الدردشة للمتعة. كيف يمكنني مساعدتك اليوم؟</p>
                    </div>
                </div>
            @else
                @foreach($chatHistory as $chat)
                    <div class="flex {{ $chat['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-2xl p-4 shadow-sm {{ $chat['role'] === 'user' 
                            ? 'bg-primary-600 text-white rounded-tr-none' 
                            : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-100 dark:border-gray-700 rounded-tl-none' }}">
                            <div class="flex items-center space-x-2 rtl:space-x-reverse mb-1 opacity-70">
                                @if($chat['role'] === 'user')
                                    <span class="text-[10px] uppercase font-bold tracking-wider">أنت</span>
                                    <x-heroicon-s-user class="w-3 h-3" />
                                @else
                                    <x-heroicon-s-sparkles class="w-3 h-3" />
                                    <span class="text-[10px] uppercase font-bold tracking-wider">Gemini ({{ $selectedModel }})</span>
                                @endif
                            </div>
                            <div class="prose prose-sm dark:prose-invert max-w-none">
                                {!! nl2br(e($chat['content'])) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            @if($loading)
                <div class="flex justify-start animate-pulse">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl rounded-tl-none p-4 border border-gray-100 dark:border-gray-700 flex items-center space-x-2 rtl:space-x-reverse">
                        <div class="flex space-x-1 rtl:space-x-reverse">
                            <div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce [animation-delay:0.2s]"></div>
                            <div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce [animation-delay:0.4s]"></div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Gemini يفكّر...</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Input Area -->
        <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
            <form wire:submit.prevent="sendMessage" class="flex items-end space-x-3 rtl:space-x-reverse">
                <div class="flex-1 relative">
                    <textarea 
                        wire:model.defer="message"
                        placeholder="اكتب رسالتك هنا... (Shift + Enter للسطر الجديد)"
                        rows="1"
                        class="w-full bg-gray-50 dark:bg-gray-900 border-none rounded-xl focus:ring-2 focus:ring-primary-500 dark:text-white px-4 py-3 resize-none scrollbar-none transition-all duration-200"
                        onkeydown="if(event.keyCode === 13 && !event.shiftKey) { event.preventDefault(); @this.sendMessage(); }"
                        oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'
                    ></textarea>
                </div>
                
                <button 
                    type="submit" 
                    wire:loading.attr="disabled"
                    class="p-3 bg-primary-600 hover:bg-primary-500 text-white rounded-xl shadow-md transition-all duration-200 hover:scale-105 active:scale-95 disabled:opacity-50 disabled:scale-100"
                >
                    <x-heroicon-o-paper-airplane class="w-6 h-6 rtl:rotate-180" />
                </button>
            </form>
            <div class="mt-2 flex justify-between items-center text-[10px] text-gray-400 dark:text-gray-500 px-2">
                <span>مدعوم بتقنيات Gemini 1.5 & 2.0</span>
                <span>اضغط Enter للإرسال</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:load', function () {
            const container = document.getElementById('chat-container');
            const scrollToBottom = () => {
                container.scrollTop = container.scrollHeight;
            };
            
            scrollToBottom();
            
            Livewire.on('messageReceived', () => {
                setTimeout(scrollToBottom, 50);
            });
            
            window.addEventListener('scroll-to-bottom', () => {
                setTimeout(scrollToBottom, 50);
            });
        });
    </script>

    <style>
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .dark .scrollbar-thin::-webkit-scrollbar-thumb { background: #475569; }
        .prose { max-width: 100% !important; }
    </style>
</x-filament-panels::page>
