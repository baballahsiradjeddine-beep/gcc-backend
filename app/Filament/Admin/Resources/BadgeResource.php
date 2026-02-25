<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\AdminNavigation;
use App\Filament\Admin\Resources\BadgeResource\Pages;
use App\Models\Badge;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class BadgeResource extends Resource
{
    protected static ?string $model = Badge::class;

    public static function getNavigationIcon(): string
    {
        return AdminNavigation::BADGE_RESOURCE['icon'];
    }

    public static function getNavigationGroup(): ?string
    {
        return __(AdminNavigation::BADGE_RESOURCE['group']);
    }

    public static function getNavigationSort(): ?int
    {
        return AdminNavigation::BADGE_RESOURCE['sort'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('min_points')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('max_points')
                            ->numeric(),
                        Forms\Components\ColorPicker::make('color'),
                        Forms\Components\TextInput::make('rank_order')
                            ->numeric()
                            ->default(0),
                        SpatieMediaLibraryFileUpload::make('icon')
                            ->collection('badge_icon')
                            ->image()
                            ->imageEditor(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('icon')
                    ->collection('badge_icon'),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('min_points')
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_points')
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color'),
                Tables\Columns\TextColumn::make('rank_order')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBadges::route('/'),
            'create' => Pages\CreateBadge::route('/create'),
            'edit' => Pages\EditBadge::route('/{record}/edit'),
        ];
    }
    
    public static function getPluralLabel(): ?string
    {
        return __('custom.models.badges');
    }

    public static function getModelLabel(): string
    {
        return __('custom.models.badge');
    }
}
