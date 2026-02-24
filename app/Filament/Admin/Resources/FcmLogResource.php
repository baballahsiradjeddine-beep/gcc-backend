<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FcmLogResource\Pages;
use App\Filament\Admin\Resources\FcmLogResource\RelationManagers;
use App\Models\FcmLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FcmLogResource extends Resource
{
    protected static ?string $model = FcmLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    public static function getNavigationGroup(): ?string
    {
        return 'الإدارة'; // Same group as ManageNotifications hopefully or general Administration
    }

    public static function getNavigationLabel(): string
    {
        return 'سجل الاشعارات المجربة';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id'),
                Forms\Components\TextInput::make('title'),
                Forms\Components\Textarea::make('body'),
                Forms\Components\TextInput::make('status'),
                Forms\Components\Textarea::make('response_body'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('User')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('body')->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        'exception' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListFcmLogs::route('/'),
            'view' => Pages\ViewFcmLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}
