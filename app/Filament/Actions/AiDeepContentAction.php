<?php

namespace App\Filament\Actions;

use App\Models\Chapter;
use App\Models\Question;
use App\Models\Unit;
use App\Services\GeminiAiService;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Notifications\Notification;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Assets\Js;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class AiDeepContentAction
{
    public static function make(): Action
    {
        return Action::make('ai_deep_content')
            ->label('توليد فصول IA')
            ->icon('heroicon-o-cpu-chip')
            ->color('info')
            ->modalHeading('توليد فصول IA')
            ->form([
                Forms\Components\Group::make([
                    Forms\Components\FileUpload::make('pdf_file')
                        ->label('ملف الوحدة (PDF)')
                        ->acceptedFileTypes(['application/pdf'])
                        ->maxSize(15360)
                        ->directory('temp_ai_uploads')
                        ->required()
                        ->live()
                        ->extraAttributes(['x-on:change' => 'isAnalyzing = false'])
                        ->hint('يرجى رفع الملف، ثم الضغط على الزر الأخضر بالأسفل للتحليل.'),

                    Forms\Components\Textarea::make('extra_analysis_instructions')
                        ->label('تعليمات تحليل إضافية (اختياري)')
                        ->placeholder('مثال: ركز على القوانين الرياضية فقط، أو اقترح 3 فصول بحد أقصى...')
                        ->rows(2)
                        ->hint('هذه التعليمات ستُرسل للذكاء الاصطناعي مع الملف.'),

                    Forms\Components\Placeholder::make('ai_status')
                        ->label('')
                        ->content(new HtmlString('
                            <div x-show="isAnalyzing" 
                                 x-data="{ 
                                    steps: [\'بدء التحليل...\', \'فحص ملف الـ PDF...\', \'الاتصال بـ Gemini AI...\', \'IA يقرأ المحتوى الآن...\', \'استخراج العناوين والوصف...\', \'جاري تنسيق النتائج النهائية...\'],
                                    currentStep: 0,
                                    timer: null
                                 }"
                                 x-init="$watch(\'isAnalyzing\', value => {
                                    if(value) {
                                        currentStep = 0;
                                        timer = setInterval(() => { if(currentStep < steps.length - 1) currentStep++ }, 3000);
                                    } else {
                                        clearInterval(timer);
                                    }
                                 })"
                                 class="p-4 bg-info-50 border border-info-200 rounded-xl flex flex-col items-center justify-center space-y-4 animate-pulse">
                                <div class="flex items-center space-x-3 rtl:space-x-reverse">
                                    <div class="w-8 h-8 border-4 border-info-500 border-t-transparent rounded-full animate-spin"></div>
                                    <span class="text-lg font-bold text-info-700" x-text="steps[currentStep]"></span>
                                </div>
                                <div class="w-full bg-info-100 rounded-full h-2">
                                    <div class="bg-info-500 h-2 rounded-full transition-all duration-1000" :style="\'width: \' + ((currentStep + 1) * 16.6) + \'%\'"></div>
                                </div>
                                <p class="text-sm text-info-600">يرجى الانتظار، العملية قد تستغرق من 30 إلى 60 ثانية حسب حجم الملف...</p>
                            </div>
                        '))
                        ->hidden(fn ($get) => empty($get('pdf_file'))),
                ])
                ->extraAttributes([
                    'x-data' => '{ isAnalyzing: false }',
                    'x-on:ai-finished.window' => 'isAnalyzing = false',
                ]),

                Forms\Components\Actions::make([
                    FormAction::make('analyze_pdf')
                        ->label('قراءة وتحليل الملف الآن 🔍')
                        ->color('success')
                        ->extraAttributes([
                            'x-on:click' => 'isAnalyzing = true',
                            'x-bind:class' => 'isAnalyzing ? "opacity-50 pointer-events-none" : ""',
                        ])
                        ->action(function ($set, $get) {
                            set_time_limit(300);
                            $fileState = $get('pdf_file');
                            
                            if (!$fileState) {
                                Notification::make()->danger()->title('يرجى رفع ملف أولاً')->send();
                                return;
                            }

                            $resolvePath = function($state) {
                                if (empty($state)) return null;
                                if (is_object($state) && method_exists($state, 'getRealPath')) return $state->getRealPath();
                                $pathStr = is_array($state) ? reset($state) : $state;
                                if (is_object($pathStr) && method_exists($pathStr, 'getRealPath')) return $pathStr->getRealPath();
                                $pathStr = (string)$pathStr;
                                $p = Storage::disk('public')->path($pathStr);
                                if (file_exists($p) && !is_dir($p)) return $p;
                                $sysT = sys_get_temp_dir() . '/' . basename($pathStr);
                                if (file_exists($sysT)) return $sysT;
                                $hash = str_replace('livewire-file:', '', $pathStr);
                                foreach ([storage_path('app/livewire-tmp'), storage_path('app/public/livewire-tmp')] as $dir) {
                                    if (!file_exists($dir)) continue;
                                    foreach (File::files($dir) as $f) {
                                        if (str_contains($f->getFilename(), $hash)) return $f->getRealPath();
                                    }
                                }
                                return null;
                            };

                            $path = $resolvePath($fileState);
                            if (!$path) {
                                Notification::make()->danger()->title('لم يتم العثور على الملف')->send();
                                return;
                            }

                            try {
                                $chapters = GeminiAiService::analyzePdfForChapters(
                                    $path, 
                                    $get('extra_analysis_instructions')
                                );
                                $set('chapters_preview', $chapters);
                                Notification::make()->success()->title('تم تحليل الملف بنجاح')->send();
                            } catch (\Exception $e) {
                                Notification::make()->danger()->title('خطأ في التحليل')->body($e->getMessage())->send();
                            } finally {
                                FilamentAsset::register([
                                    Js::make('reset-ai-status', 'document.dispatchEvent(new CustomEvent("ai-finished"))'),
                                ]);
                            }
                        }),
                ]),

                Forms\Components\Repeater::make('chapters_preview')
                    ->label('الفصول المقترحة')
                    ->schema([
                        Forms\Components\Grid::make(12)->schema([
                            Forms\Components\Checkbox::make('is_selected')
                                ->label('تفعيل')
                                ->default(true)
                                ->columnSpan(2),
                            Forms\Components\TextInput::make('title')
                                ->label('عنوان الفصل')
                                ->required()
                                ->columnSpan(7),
                            Forms\Components\TextInput::make('q_count')
                                ->label('الأسئلة')
                                ->numeric()
                                ->default(10)
                                ->required()
                                ->columnSpan(3),
                            Forms\Components\Textarea::make('description')
                                ->label('وصف المحتوى')
                                ->rows(2)
                                ->required()
                                ->columnSpan(12),
                        ]),
                    ])
                    ->minItems(1)
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->collapsible()
                    ->collapsed(false)
                    ->cloneable()
                    ->hintAction(
                        FormAction::make('select_all')
                            ->label('تحديد الكل')
                            ->icon('heroicon-m-check-circle')
                            ->action(function ($component, $state, $set) {
                                $selected = array_map(function ($item) {
                                    $item['is_selected'] = true;
                                    return $item;
                                }, $state);
                                $set('chapters_preview', $selected);
                            })
                    ),
            ])
            ->action(function (array $data, $livewire, $record): void {
                // Determine the unit record. 
                $unit = $record;
                
                if (!($unit instanceof Unit)) {
                     // Inside a relation manager, if $record is null (header action), get owner record
                     if (method_exists($livewire, 'getOwnerRecord')) {
                         $unit = $livewire->getOwnerRecord();
                     }
                }

                if (!($unit instanceof Unit)) {
                    Notification::make()->danger()->title('خطأ في تحديد الوحدة')->send();
                    return;
                }

                set_time_limit(600);
                $fileData = $data['pdf_file'];

                $resolvePathFinal = function($state) {
                    if (empty($state)) return null;
                    if (is_object($state) && method_exists($state, 'getRealPath')) return $state->getRealPath();
                    $pathStr = is_array($state) ? reset($state) : $state;
                    if (is_object($pathStr) && method_exists($pathStr, 'getRealPath')) return $pathStr->getRealPath();
                    $pathStr = (string)$pathStr;
                    $p = Storage::disk('public')->path($pathStr);
                    if (file_exists($p) && !is_dir($p)) return $p;
                    $sysT = sys_get_temp_dir() . '/' . basename($pathStr);
                    if (file_exists($sysT)) return $sysT;
                    $hash = str_replace('livewire-file:', '', $pathStr);
                    foreach ([storage_path('app/livewire-tmp'), storage_path('app/public/livewire-tmp')] as $dir) {
                        if (!file_exists($dir)) continue;
                        foreach (File::files($dir) as $f) {
                            if (str_contains($f->getFilename(), $hash)) return $f->getRealPath();
                        }
                    }
                    return null;
                };

                $filePath = $resolvePathFinal($fileData);
                $chaptersData = $data['chapters_preview'] ?? [];

                if (empty($chaptersData)) return;

                $totalQuestions = 0;
                $createdChapters = 0;

                foreach ($data['chapters_preview'] as $preview) {
                    if (!($preview['is_selected'] ?? false)) continue;
                    
                    $chapter = Chapter::create([
                        'name' => $preview['title'],
                        'description' => $preview['description'],
                        'active' => true,
                        // Fix: chapter_level_id is required
                        'chapter_level_id' => \App\Models\ChapterLevel::first()?->id ?? 1,
                        'direction' => $unit->direction,
                    ]);

                    $maxSort = DB::table('chapter_unit')
                        ->where('unit_id', $unit->id)
                        ->max('sort') ?? 0;
                        
                    $unit->chapters()->attach($chapter, ['sort' => $maxSort + 1]);

                    try {
                        $questions = GeminiAiService::generateQuestionsForChapter(
                            $filePath, 
                            $preview['title'], 
                            $preview['description'], 
                            (int)$preview['q_count']
                        );

                        foreach ($questions as $qIdx => $qData) {
                            $question = Question::create([
                                'question' => $qData['question'],
                                'question_type' => $qData['question_type'],
                                'scope' => $qData['scope'] ?? 'exercice',
                                'direction' => strtoupper($qData['direction'] ?? 'INHERIT'),
                                'explanation_text' => $qData['explanation_text'] ?? null,
                                'options' => $qData['options'],
                            ]);

                            $chapter->questions()->attach($question, ['sort' => $qIdx + 1]);
                            $totalQuestions++;
                        }
                        $createdChapters++;
                    } catch (\Exception $e) {
                        Log::error('AI Multi-Chapter Generation Error', [
                            'chapter' => $preview['title'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                Notification::make()
                    ->success()
                    ->title('تمت المعالجة بنجاح!')
                    ->body("تم إنشاء {$createdChapters} فصول وإجمالي {$totalQuestions} سؤال ذكي.")
                    ->duration(10000)
                    ->send();
                
                if (file_exists($filePath)) unlink($filePath);
            });
    }
}
