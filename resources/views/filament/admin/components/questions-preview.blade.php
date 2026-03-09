<div class="w-full h-full p-4 overflow-y-auto flex justify-center items-start text-gray-900" 
     wire:key="q-preview-root">
     
    <!-- A4 PDF Document Style Wrapper bg and logic -->
    <div id="a4-printable-area" class="a4-wrapper relative w-full shadow-lg border border-gray-200" 
         dir="rtl" 
         style="background-color: #F8FAFC; max-width: 800px; min-height: 800px; border-radius: 12px; font-family: 'Inter', system-ui, -apple-system, sans-serif; height: max-content; padding-bottom: 2rem;">

        <!-- App Header Navbar (Mock) -->
        <div class="print:hidden px-5 py-4 flex items-center justify-between border-b border-gray-200" style="background-color: rgba(248, 250, 252, 0.95); border-top-left-radius: 12px; border-top-right-radius: 12px;">
            <div class="w-10 h-10 rounded-full bg-white shadow-sm border border-[#E2E8F0] flex items-center justify-center text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </div>
            <!-- Progress Bar Mock -->
            <div class="flex-1 mx-6 flex flex-col items-center gap-1.5">
                <div class="text-[11px] font-black text-[#0077B6] uppercase tracking-widest">{{ $ownerRecord->name ?? 'مراجعة المكتسبات' }}</div>
                <div class="h-2 w-full bg-[#E2E8F0] rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-[#00B4D8] to-[#0B84D4] w-2/3 rounded-full"></div>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-white shadow-sm border border-[#E2E8F0] flex items-center justify-center text-[#EC4899]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
        </div>

        @if(empty($questions))
            <div class="flex flex-col items-center justify-center p-8 text-center mt-20">
                <div class="w-24 h-24 bg-white shadow-sm rounded-3xl flex items-center justify-center mb-6 border border-[#E2E8F0]">
                    <svg class="w-10 h-10 text-[#CBD5E1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <h3 class="text-[18px] font-black text-[#0F172A] mb-2">الشاشة بانتظارك</h3>
                <p class="text-[14px] text-[#64748B] font-medium leading-relaxed">قم بوضع بيانات الأسئلة عبر النسخ واللصق لتشاهد كيف ستظهر في واجهة التطبيق.</p>
                
                @if(!empty($raw))
                     <div class="mt-8 p-4 bg-[#FEF2F2] border border-[#FECDD3] text-[#BE123C] text-xs rounded-2xl w-full font-bold shadow-sm">
                        <span class="block text-[14px] mb-1">⚠️ تنسيق غير صحيح</span>
                        تأكد من إغلاق كافة الأقواس وسلامة بنية JSON المدخلة.
                    </div>
                @endif
            </div>
        @else
            @php
                $unit = null;
                $material = null;
                $currentRecord = $ownerRecord ?? (isset($getRecord) ? $getRecord() : null);
                
                if ($currentRecord && method_exists($currentRecord, 'unit')) {
                    $unit = $currentRecord->unit()->first();
                    $material = $unit && method_exists($unit, 'material') ? $unit->material()->first() : null;
                }
            @endphp
            <!-- The Actual Content -->
            <div class="w-full px-6 pt-8 flex flex-col" style="gap: 1.5rem;">
                
                <!-- Document Header (Printed Title) -->
                @if($currentRecord)
                <div class="mb-4 flex flex-col items-center justify-center text-center">
                    <h1 class="text-[22px] font-black text-[#0F172A] mb-3">{{ $currentRecord->name ?? 'الفصل غير متوفر' }}</h1>
                    <div class="flex items-center gap-2 text-[13px] font-bold text-[#64748B]">
                        <span class="px-4 py-1.5 rounded-full bg-[#F1F5F9] border border-[#E2E8F0]">{{ $material ? $material->name : 'المادة' }}</span>
                        <svg class="w-4 h-4 text-[#CBD5E1] shrink-0 transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"/></svg>
                        <span class="px-4 py-1.5 rounded-full bg-[#F0F9FF] border border-[#BAE6FD] text-[#0284C7]">{{ $unit ? $unit->name : 'الوحدة' }}</span>
                    </div>
                </div>
                @endif

                @foreach($questions as $index => $q)
                    <div class="question-card bg-white border border-[#F1F5F9] w-full block" style="border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">


                            
                            <!-- Question Header Strip -->
                            <div class="px-5 py-3 flex justify-between items-center border-b border-[#F8FAFC]">
                                <div class="flex gap-2 items-center">
                                    <div class="px-3 py-1 rounded-full bg-[#F0F9FF] text-[#0284C7] text-[10px] font-black tracking-widest uppercase">
                                        {{ 
                                            match($q['question_type'] ?? '') {
                                                'multiple_choices' => 'اختيار من متعدد',
                                                'true_or_false' => 'صواب أو خطأ',
                                                'fill_in_the_blanks' => 'املأ الفراغات',
                                                'pick_the_intruder' => 'الكلمة الدخيلة',
                                                'match_with_arrows' => 'صل بخط',
                                                default => 'سؤال نوع مجهول'
                                            }
                                        }}
                                    </div>
                                    <div class="px-2.5 py-1 rounded-full bg-[#F1F5F9] text-[#64748B] text-[10px] font-black">
                                        {{ $index + 1 }}/{{ count($questions) }}
                                    </div>
                                </div>
                                <div class="flex gap-1">
                                    <div class="w-1.5 h-1.5 rounded-full {{ ($q['scope'] ?? '') === 'lesson' ? 'bg-[#10B981]' : 'bg-[#EC4899]' }}"></div>
                                </div>
                            </div>

                            <!-- Question Body -->
                            <div class="px-5 py-5 flex-1">
                                <h2 class="outline-none focus:ring-2 focus:ring-[#0284C7] focus:bg-white rounded p-1 transition-all text-[16px] font-bold text-[#0F172A] leading-[1.6] mb-5">
                                    {{ $q['question'] ?? 'أين نص السؤال؟' }}
                                </h2>

                                <!-- Dynamic Answer UI based on Question Type -->
                                @if(isset($q['question_type']))
                                    <div class="space-y-3.5">
                                        
                                        <!-- UI: Multiple Choice -->
                                        @if($q['question_type'] === 'multiple_choices' && isset($q['options']['choices']))
                                            @foreach($q['options']['choices'] as $oIdx => $opt)
                                                <div class="relative group flex items-center p-3 rounded-xl border-2 transition-all {{ !empty($opt['is_correct']) ? 'bg-[#F0FDF4] border-[#22C55E]' : 'bg-white border-[#E2E8F0]' }}">
                                                    
                                                    <!-- Circle Selection Indicator -->
                                                    <div class="w-[18px] h-[18px] rounded-full border-2 flex items-center justify-center shrink-0 ml-3 {{ !empty($opt['is_correct']) ? 'border-[#22C55E] bg-[#22C55E] text-white shadow-sm' : 'border-[#CBD5E1] bg-[#F8FAFC]' }}">
                                                        @if(!empty($opt['is_correct']))
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        @endif
                                                    </div>
                                                    
                                                    <span class="outline-none focus:ring-2 focus:ring-[#22C55E] focus:bg-white rounded px-1.5 py-0.5 transition-all text-[14px] font-bold flex-1 {{ !empty($opt['is_correct']) ? 'text-[#166534]' : 'text-[#334155]' }}">
                                                        {{ $opt['option'] ?? '' }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        @endif

                                        <!-- UI: True or False -->
                                        @if($q['question_type'] === 'true_or_false')
                                            <div class="grid grid-cols-2 gap-3 pt-1">
                                                <div class="flex flex-col items-center justify-center py-4 rounded-xl border-2 shadow-sm {{ isset($q['options']['correct']) && $q['options']['correct'] ? 'bg-[#F0FDF4] border-[#22C55E]' : 'bg-white border-[#E2E8F0]' }}">
                                                    <div class="w-8 h-8 rounded-full mb-2 flex items-center justify-center {{ isset($q['options']['correct']) && $q['options']['correct'] ? 'bg-[#22C55E] text-white' : 'bg-[#F8FAFC] text-[#94A3B8] border border-[#E2E8F0]' }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    </div>
                                                    <span class="text-[14px] font-black {{ isset($q['options']['correct']) && $q['options']['correct'] ? 'text-[#15803D]' : 'text-[#64748B]' }}">صح</span>
                                                </div>
                                                <div class="flex flex-col items-center justify-center py-4 rounded-xl border-2 shadow-sm {{ isset($q['options']['correct']) && !$q['options']['correct'] ? 'bg-[#FEF2F2] border-[#EF4444]' : 'bg-white border-[#E2E8F0]' }}">
                                                    <div class="w-8 h-8 rounded-full mb-2 flex items-center justify-center {{ isset($q['options']['correct']) && !$q['options']['correct'] ? 'bg-[#EF4444] text-white' : 'bg-[#F8FAFC] text-[#94A3B8] border border-[#E2E8F0]' }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </div>
                                                    <span class="text-[14px] font-black {{ isset($q['options']['correct']) && !$q['options']['correct'] ? 'text-[#B91C1C]' : 'text-[#64748B]' }}">خطأ</span>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- UI: Pick the Intruder Bubble Map -->
                                        @if($q['question_type'] === 'pick_the_intruder' && isset($q['options']['words']))
                                            <div class="flex flex-wrap gap-2.5 justify-center py-3 bg-[#F8FAFC] rounded-xl border border-[#F1F5F9]">
                                                @foreach($q['options']['words'] as $wIdx => $word)
                                                    <div class="cursor-text outline-none focus:ring-2 focus:ring-[#F43F5E] px-4 py-2 rounded-full text-[14px] font-bold border-2 transition-all {{ !empty($word['is_intruder']) ? 'bg-[#F43F5E] text-white border-[#F43F5E] shadow-[0_4px_10px_rgba(244,63,94,0.3)] transform scale-105' : 'bg-white text-[#334155] border-[#E2E8F0] shadow-sm' }}">
                                                        {{ $word['word'] ?? '' }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- UI: Match with Arrows Cards (Drag & Drop Mock) -->
                                        @if($q['question_type'] === 'match_with_arrows' && isset($q['options']['pairs']))
                                            <div class="flex gap-4 items-stretch justify-center relative py-1">
                                                <div class="flex-1 space-y-2.5 z-10 w-full">
                                                    @foreach($q['options']['pairs'] as $pIdx => $p)
                                                        <div class="bg-white p-3 rounded-xl shadow-sm text-[13px] font-bold text-center border-2 border-[#E2E8F0] text-[#0F172A] relative flex items-center justify-center min-h-[44px]">
                                                            <span class="outline-none focus:ring-2 focus:ring-[#0284C7] rounded px-1">{{ $p['first'] ?? '' }}</span>
                                                            <div class="absolute inset-y-0 left-0 w-1.5 bg-[#E2E8F0] rounded-l-md opacity-30"></div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                
                                                <div class="w-10 flex flex-col justify-between items-center py-4 text-[#CBD5E1]">
                                                    @foreach($q['options']['pairs'] as $p)
                                                        <svg class="w-full h-1" preserveAspectRatio="none" viewBox="0 0 100 10" fill="none"><path d="M0,5 L100,5" stroke="currentColor" stroke-width="6" stroke-linecap="round" stroke-dasharray="1 14"/></svg>
                                                    @endforeach
                                                </div>

                                                <div class="flex-1 space-y-2.5 z-10 w-full">
                                                    @foreach($q['options']['pairs'] as $pIdx => $p)
                                                        <div class="bg-[#F8FAFC] p-3 rounded-xl shadow-sm border-2 border-dashed border-[#CBD5E1] text-[13px] font-bold text-center text-[#475569] transition-colors relative flex items-center justify-center min-h-[44px]">
                                                            <span class="outline-none focus:ring-2 focus:ring-[#0284C7] rounded px-1 bg-white">{{ $p['second'] ?? '' }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                        
                                        <!-- UI: Fill in Blanks Desktop -->
                                        @if($q['question_type'] === 'fill_in_the_blanks' && isset($q['options']['paragraph']))
                                            <div class="bg-[#FFFBEB] p-5 rounded-xl border-2 border-[#FEF3C7] text-[15px] leading-[2.1] font-semibold text-[#1E3A8A]">
                                                {!! preg_replace('/\[(.*?)\]/', '<span class="inline-block px-2.5 py-0.5 mx-1 bg-[#FEF08A] border-b-2 border-[#FACC15] rounded text-[#854D0E] min-w-[50px] text-center shadow-[inset_0_2px_4px_rgba(255,255,255,0.5)]"><span class="opacity-50 text-[11px]">...</span></span>', $q['options']['paragraph']) !!}
                                            </div>
                                        @endif

                                    </div>
                                @endif
                                    
                                    @if(!empty($q['explanation_text']))
                                        <div class="mt-5 p-3.5 bg-[#F0FDF4] border border-[#BBF7D0] flex gap-3 text-[#166534]" style="border-radius: 12px;">
                                            <div class="w-7 h-7 rounded-full bg-[#22C55E]/20 flex items-center justify-center shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <div class="outline-none focus:ring-2 focus:ring-[#22C55E] rounded px-1 bg-white/50 text-[12px] leading-relaxed font-bold pt-1 w-full">
                                                {{ $q['explanation_text'] }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                @endforeach
            </div>

            <!-- PDF Download Button -->
            <div class="print:hidden px-6 pb-10 pt-4 flex justify-center w-full">
                <button type="button" 
                        x-data 
                        @click="
                            const area = document.getElementById('a4-printable-area');
                            if(area) {
                                const w = window.open('', '_blank');
                                let s = '';
                                document.querySelectorAll('link[rel=\'stylesheet\'], style').forEach(e => s += e.outerHTML);
                                w.document.write('<html dir=\'rtl\'><head><title>تحميل الأسئلة - PDF</title>'+s+'<style>@page { size: auto; margin: 15mm 0mm; } body { background-color: white !important; display: flex; justify-content: center; padding: 0; margin: 0; } #a4-printable-area { box-shadow: none !important; border: none !important; background-color: #fff !important; } .print\\\\:hidden { display: none !important; } .question-card { page-break-inside: avoid !important; break-inside: avoid !important; box-shadow: none !important; border: 2px solid #E2E8F0 !important; margin-bottom: 20px !important; border-radius: 16px !important; } * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }</style></head><body style=\'background-color: white !important; padding: 20px;\'>'+area.outerHTML+'<scr'+'ipt>setTimeout(() => { window.print(); window.close(); }, 500);</scr'+'ipt></body></html>');
                                w.document.close();
                                w.focus();
                            }
                        "
                        class="flex items-center gap-3 bg-[#EF4444] hover:bg-[#DC2626] text-white px-10 py-4 rounded-[1.25rem] font-black text-[16px] shadow-[0_8px_25px_rgba(239,68,68,0.35)] transition-all transform hover:-translate-y-1 active:translate-y-0 active:border-b-0 border-b-[4px] border-[#B91C1C]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    تحميل PDF
                </button>
            </div>
        @endif
    </div>
</div>


