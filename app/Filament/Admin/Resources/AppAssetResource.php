<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\AppAssetResource\Pages;
use App\Models\AppAsset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AppAssetResource extends Resource
{
    protected static ?string $model = AppAsset::class;

    protected static ?string $recordTitleAttribute = 'label';

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::APP_GROUP);
    }

    public static function getModelLabel(): string
    {
        return 'صورة التطبيق';
    }

    public static function getPluralModelLabel(): string
    {
        return 'صور التطبيق';
    }

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('معلومات الصورة')->schema([
                TextInput::make('label')
                    ->label('الاسم')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('key')
                    ->label('المفتاح (للمطور)')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('هذا المفتاح يستخدمه التطبيق للتعرف على الصورة'),

                Placeholder::make('description')
                    ->label('الوصف')
                    ->content(fn (AppAsset $record): string => $record->description ?? '—'),

                Toggle::make('is_active')
                    ->label('مفعّل')
                    ->default(true),
            ]),

            Section::make('الصورة')->schema([
                FileUpload::make('image_url')
                    ->label('رفع صورة جديدة')
                    ->image()
                    ->imagePreviewHeight('200')
                    ->disk('public')
                    ->directory('app-assets')
                    ->visibility('public')
                    ->helperText('عند رفع صورة جديدة، يتم تحديثها في التطبيق تلقائياً')
                    ->afterStateUpdated(function ($state, $set, AppAsset $record) {
                        // Convert stored filename to full URL
                        if ($state) {
                            $url = Storage::disk('public')->url('app-assets/' . $state);
                            $set('image_url', $url);
                        }
                    }),

                Placeholder::make('version_hash')
                    ->label('رقم الإصدار')
                    ->content(fn (AppAsset $record): string => $record->version_hash ?? 'لم يُحدَّث بعد')
                    ->helperText('يتغير هذا الرقم تلقائياً عند تغيير الصورة حتى يعرف التطبيق أن هناك تحديثاً'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('الصورة')
                    ->height(60)
                    ->width(100)
                    ->defaultImageUrl(asset('images/placeholder.png')),

                TextColumn::make('label')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('key')
                    ->label('المفتاح')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                TextColumn::make('description')
                    ->label('الوصف')
                    ->limit(40)
                    ->color('gray'),

                TextColumn::make('version_hash')
                    ->label('الإصدار')
                    ->badge()
                    ->color('info')
                    ->fontFamily('mono')
                    ->placeholder('—'),

                ToggleColumn::make('is_active')
                    ->label('مفعّل'),

                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('key')
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppAssets::route('/'),
            'edit'  => Pages\EditAppAsset::route('/{record}/edit'),
        ];
    }
}
