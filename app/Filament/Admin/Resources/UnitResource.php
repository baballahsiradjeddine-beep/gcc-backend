<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ContentDirection;
use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\UnitResource\Pages;
use App\Filament\Admin\Resources\UnitResource\RelationManagers\ChaptersRelationManager;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    public static function getNavigationGroup(): ?string
    {
        return  __(AdminNavigation::UNIT_RESOURCE['group']);
    }

    public static function getModelLabel(): string
    {
        return __('custom.models.unit');
    }

    public static function getPluralModelLabel(): string
    {
        return __('custom.models.units');
    }

    protected static ?string $model = Unit::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static bool $isGloballySearchable = true;

    protected static ?string $navigationIcon = AdminNavigation::UNIT_RESOURCE['icon'];

    protected static ?int $navigationSort = AdminNavigation::UNIT_RESOURCE['sort'];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('custom.forms.unit.create.section.infos'))->schema([
                    TextInput::make('name')
                        ->required()
                        ->minLength(3)
                        ->label(__('custom.models.unit.name')),

                    Select::make('material')
                        ->relationship('material', 'code')
                        ->searchable()
                        // ->preload()
                        ->required()
                        ->label(__('custom.models.unit.material')),

                    Select::make('direction')->native(false)
                        ->options(ContentDirection::class)
                        ->enum(ContentDirection::class)
                        ->default(ContentDirection::INHERIT)
                        ->required()
                        ->label(__('custom.direction.label')),

                    Textarea::make('description')
                        ->rows(4)
                        ->columnSpan(2)
                        ->label(__('custom.models.unit.description')),

                    Select::make('subscriptions')
                        ->multiple()
                        ->relationship('subscriptions', 'name')
                        ->searchable()
                        ->preload()
                        ->label(__('custom.models.unit.subscriptions'))
                        ->columnSpan(2),

                    Forms\Components\Toggle::make('active')
                        ->label(__('custom.models.active'))
                        ->default(true),
                ])->columns(2)
                    ->columnSpan(2),

                Section::make(__('custom.forms.unit.create.section.image'))->schema([
                    SpatieMediaLibraryFileUpload::make('image')
                        ->multiple(false)
                        ->label('')
                        ->collection('image')
                        ->image()
                        ->imageEditor(),
                ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->toggleable()
                    ->conversion('thumb')
                    ->placeholder(__('custom.table.image.empty'))
                    ->label(__('custom.forms.unit.create.section.image'))
                    ->collection('image')
                    ->circular(),

                TextColumn::make('name')
                    ->label(__('custom.models.unit.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('description')
                    ->limit(30)
                    ->label(__('custom.models.unit.description')),

                TextColumn::make('material.code')
                    ->badge()
                    ->label(__('custom.models.unit.material'))
                    ->sortable()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('chapters_count')
                    ->badge()
                    ->label(__('custom.models.chapters'))
                    ->counts('chapters')
                    ->sortable()
                    ->colors(['primary']),

                TextColumn::make('subscriptions.name')
                    ->label(__('custom.models.subscriptions'))
                    ->badge(),

                Tables\Columns\ToggleColumn::make('active')
                    ->label(__('custom.models.active'))
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                // Tables\Filters\SelectFilter::make('material')->relationship("material", "code")->multiple()->preload()
                //     ->searchable()->label(__('custom.models.materials')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('ai_deep_content')
                    ->label('توليد فصول وأسئلة بذكاء (Deep AI)')
                    ->icon('heroicon-o-cpu-chip')
                    ->color('info')
                    ->modalHeading('توليد فصول وأسئلة بذكاء (Deep AI)')
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
                                ->content(new \Illuminate\Support\HtmlString('
                                    <div x-show="isAnalyzing" 
                                         x-data="{ 
                                            steps: [\'بدء التحليل...\', \'فحص ملف الـ PDF...\', \'الاتصال بـ Gemini AI...\', \'الذكاء الاصطناعي يقرأ المحتوى الآن...\', \'استخراج العناوين والوصف...\', \'جاري تنسيق النتائج النهائية...\'],
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
                            Forms\Components\Actions\Action::make('analyze_pdf')
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
                                        \Filament\Notifications\Notification::make()->danger()->title('يرجى رفع ملف أولاً')->send();
                                        return;
                                    }

                                    $resolvePath = function($state) {
                                        if (empty($state)) return null;
                                        if (is_object($state) && method_exists($state, 'getRealPath')) return $state->getRealPath();
                                        $pathStr = is_array($state) ? reset($state) : $state;
                                        if (is_object($pathStr) && method_exists($pathStr, 'getRealPath')) return $pathStr->getRealPath();
                                        $pathStr = (string)$pathStr;
                                        $p = \Illuminate\Support\Facades\Storage::disk('public')->path($pathStr);
                                        if (file_exists($p) && !is_dir($p)) return $p;
                                        $sysT = sys_get_temp_dir() . '/' . basename($pathStr);
                                        if (file_exists($sysT)) return $sysT;
                                        $hash = str_replace('livewire-file:', '', $pathStr);
                                        foreach ([storage_path('app/livewire-tmp'), storage_path('app/public/livewire-tmp')] as $dir) {
                                            if (!file_exists($dir)) continue;
                                            foreach (\Illuminate\Support\Facades\File::files($dir) as $f) {
                                                if (str_contains($f->getFilename(), $hash)) return $f->getRealPath();
                                            }
                                        }
                                        return null;
                                    };

                                    $path = $resolvePath($fileState);
                                    if (!$path) {
                                        \Filament\Notifications\Notification::make()->danger()->title('لم يتم العثور على الملف')->send();
                                        return;
                                    }

                                    try {
                                        $chapters = \App\Services\GeminiAiService::analyzePdfForChapters(
                                            $path, 
                                            $get('extra_analysis_instructions')
                                        );
                                        $set('chapters_preview', $chapters);
                                        \Filament\Notifications\Notification::make()->success()->title('تم تحليل الملف بنجاح')->send();
                                    } catch (\Exception $e) {
                                        \Filament\Notifications\Notification::make()->danger()->title('خطأ في التحليل')->body($e->getMessage())->send();
                                    } finally {
                                        // Tell Alpine analysis is done
                                        \Filament\Support\Facades\FilamentAsset::register([
                                            \Filament\Support\Assets\Js::make('reset-ai-status', 'document.dispatchEvent(new CustomEvent("ai-finished"))'),
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
                                Forms\Components\Actions\Action::make('select_all')
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
                    ->action(function (array $data, Unit $record): void {
                        set_time_limit(600); // 10 minutes for final processing
                        $fileData = $data['pdf_file'];

                        $resolvePathFinal = function($state) {
                            if (empty($state)) return null;
                            if (is_object($state) && method_exists($state, 'getRealPath')) return $state->getRealPath();
                            $pathStr = is_array($state) ? reset($state) : $state;
                            if (is_object($pathStr) && method_exists($pathStr, 'getRealPath')) return $pathStr->getRealPath();
                            
                            $pathStr = (string)$pathStr;
                            $p = \Illuminate\Support\Facades\Storage::disk('public')->path($pathStr);
                            if (file_exists($p) && !is_dir($p)) return $p;
                            
                            $sysT = sys_get_temp_dir() . '/' . basename($pathStr);
                            if (file_exists($sysT)) return $sysT;
                            
                            $hash = str_replace('livewire-file:', '', $pathStr);
                            foreach ([storage_path('app/livewire-tmp'), storage_path('app/public/livewire-tmp')] as $dir) {
                                if (!file_exists($dir)) continue;
                                foreach (\Illuminate\Support\Facades\File::files($dir) as $f) {
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

                        // Process each selected preview
                        foreach ($data['chapters_preview'] as $preview) {
                            if (!($preview['is_selected'] ?? false)) continue;
                            
                            // 1. Create the Chapter
                            $chapter = \App\Models\Chapter::create([
                                'name' => $preview['title'],
                                'description' => $preview['description'],
                                'active' => true,
                                'direction' => $record->direction,
                            ]);

                            // 2. Link to Unit
                            $maxSort = \DB::table('chapter_unit')
                                ->where('unit_id', $record->id)
                                ->max('sort') ?? 0;
                                
                            $record->chapters()->attach($chapter, ['sort' => $maxSort + 1]);

                            // 3. Generate Questions for this specific chapter
                            try {
                                $questions = \App\Services\GeminiAiService::generateQuestionsForChapter(
                                    $filePath, 
                                    $cData['title'], 
                                    $cData['description'], 
                                    (int)$cData['q_count']
                                );

                                foreach ($questions as $qIdx => $qData) {
                                    $question = \App\Models\Question::create([
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
                                \Illuminate\Support\Facades\Log::error('AI Multi-Chapter Generation Error', [
                                    'chapter' => $cData['title'],
                                    'error' => $e->getMessage()
                                ]);
                                // Continue to next chapter even if one fails
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('تمت المعالجة بنجاح!')
                            ->body("تم إنشاء {$createdChapters} فصول وإجمالي {$totalQuestions} سؤال ذكي.")
                            ->duration(10000)
                            ->send();
                        
                        // Clean up temp file
                        if (file_exists($filePath)) unlink($filePath);
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ChaptersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
