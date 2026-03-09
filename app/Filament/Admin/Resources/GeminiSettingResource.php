<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\GeminiSettingResource\Pages;
use App\Models\GeminiSetting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GeminiSettingResource extends Resource
{
    protected static ?string $model = GeminiSetting::class;

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::GEMINI_SETTING_RESOURCE['group']);
    }

    public static function getModelLabel(): string
    {
        return 'إعدادات الذكاء الاصطناعي';
    }

    public static function getPluralModelLabel(): string
    {
        return 'إعدادات الذكاء الاصطناعي';
    }

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 16;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('إعدادات Gemini 🤖')
                    ->description('تخصيص المفتاح والتعليمات البرمجية.')
                    ->schema([
                        TextInput::make('key')
                            ->label('المفتاح')
                            ->disabled()
                            ->required()
                            ->formatStateUsing(fn ($state) => match($state) {
                                'system_prompt' => '🧠 الأمر البرمجي الأساسي (Prompt)',
                                'api_key' => '🔑 مفتاح الـ API (Gemini Key)',
                                'model_name' => '🤖 موديل الذكاء الاصطناعي (Model)',
                                default => $state
                            }),

                        Select::make('value')
                            ->label('اختر الموديل')
                            ->helperText('يتم جلب هذه القائمة مباشرة من جوجل لتشمل أحدث النماذج المتاحة لحسابك.')
                            ->options(function () {
                                $remoteModels = \App\Services\GeminiAiService::listModels();
                                
                                // Provide a fallback list if API fails or key is missing
                                $fallback = [
                                    'gemini-1.5-flash-latest' => 'Gemini 1.5 Flash (سريع واقتصادى)',
                                    'gemini-1.5-pro-latest' => 'Gemini 1.5 Pro (ذكى جداً)',
                                    'gemini-2.0-flash-exp' => 'Gemini 2.0 Flash (الجيل القادم)',
                                ];
                                
                                return !empty($remoteModels) ? $remoteModels : $fallback;
                            })
                            ->searchable()
                            ->visible(fn ($record) => $record?->key === 'model_name')
                            ->required(),

                        TextInput::make('value')
                            ->label('مفتاح الـ API')
                            ->password()
                            ->revealable()
                            ->visible(fn ($record) => $record?->key === 'api_key')
                            ->required(),

                        Textarea::make('value')
                            ->label('التعليمات (System Prompt)')
                            ->rows(20)
                            ->visible(fn ($record) => $record?->key === 'system_prompt')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('🧪 تجربة النموذج')
                    ->description('تأكد من أن الموديل يعمل بشكل صحيح عبر إرسال رسالة تجريبية.')
                    ->visible(fn ($record) => $record?->key === 'model_name')
                    ->schema([
                        TextInput::make('test_prompt')
                            ->label('رسالة تجريبية')
                            ->placeholder('اكتب شيئاً هنا، مثلاً: "مرحباً"')
                            ->hint('اضغط على أيقونة الإرسال للتجربة')
                            ->suffixAction(
                                \Filament\Forms\Components\Actions\Action::make('testAction')
                                    ->icon('heroicon-o-paper-airplane')
                                    ->color('primary')
                                    ->action(function ($state, $set, $record) {
                                        if (empty($state)) {
                                            Notification::make()->title('يرجى كتابة رسالة أولاً')->warning()->send();
                                            return;
                                        }

                                        try {
                                            $response = \App\Services\GeminiAiService::chat($state, $record->value);
                                            $set('test_response', $response);
                                            Notification::make()->title('تم استلام الرد بنجاح!')->success()->send();
                                        } catch (\Exception $e) {
                                            Notification::make()->title('خطأ في الاتصال')->body($e->getMessage())->danger()->send();
                                        }
                                    })
                            ),

                        Textarea::make('test_response')
                            ->label('رد الذكاء الاصطناعي')
                            ->rows(3)
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('سيظهر الرد هنا...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('نوع الإعداد')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'system_prompt' => '🧠 الأمر البرمجي الأساسي',
                        'api_key' => '🔑 مفتاح الـ API',
                        default => $state
                    })
                    ->searchable(),
                TextColumn::make('value')
                    ->label('القيمة / المعاينة')
                    ->formatStateUsing(fn ($state, $record) => $record->key === 'api_key' ? '********' : str($state)->limit(80))
                    ->limit(100),
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGeminiSettings::route('/'),
            'edit' => Pages\EditGeminiSetting::route('/{record}/edit'),
        ];
    }
}


