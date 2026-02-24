<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AutomatedNotificationResource\Pages;
use App\Models\AutomatedNotification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AutomatedNotificationResource extends Resource
{
    protected static ?string $model = AutomatedNotification::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    public static function getNavigationGroup(): ?string
    {
        return 'الإدارة';
    }

    public static function getNavigationLabel(): string
    {
        return 'الإشعارات التلقائية';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الإشعارات التلقائية';
    }

    public static function getModelLabel(): string
    {
        return 'إشعار تلقائي';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الإشعار')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم تعريفي داخلي (مثل: تذكير الاستمرار 3 أيام)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('trigger_type')
                            ->label('شرط الإرسال التلقائي')
                            ->options([
                                'daily_streak_reminder' => 'تذكير يومي بالحفاظ على التقدم (يُرسل ليلاً في حال لم يدرس اليوم)',
                                'inactive_1_day' => 'غياب عن التطبيق لمدة يوم واحد (تذكير بالعودة)',
                                'inactive_3_days' => 'غياب عن التطبيق لمدة 3 أيام',
                                'inactive_7_days' => 'غياب عن التطبيق لمدة أسبوع (أين أنت؟)',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('عنوان الإشعار الذي سيظهر للطالب')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('body')
                            ->label('نص ورسالة الإشعار')
                            ->required()
                            ->maxLength(65535),
                        Forms\Components\FileUpload::make('image')
                            ->label('صورة الإشعار (اختياري)')
                            ->image()
                            ->directory('automated_notifications')
                            ->nullable(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('الإشعار مُفعل (سيتم إرساله اوتوماتيكياً)')
                            ->default(true),
                    ])->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable(),
                Tables\Columns\TextColumn::make('trigger_type')
                    ->label('الشرط')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'daily_streak_reminder' => 'اهمال يومي للتقدم',
                        'inactive_1_day' => 'غياب يوم',
                        'inactive_3_days' => 'غياب 3 أيام',
                        'inactive_7_days' => 'غياب أسبوع',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('title')->label('العنوان'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('مُفعل')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('آخر تعديل')->dateTime()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAutomatedNotifications::route('/'),
            'create' => Pages\CreateAutomatedNotification::route('/create'),
            'edit' => Pages\EditAutomatedNotification::route('/{record}/edit'),
        ];
    }
}
