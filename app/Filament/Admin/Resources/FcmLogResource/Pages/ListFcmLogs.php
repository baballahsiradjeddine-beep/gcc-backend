<?php

namespace App\Filament\Admin\Resources\FcmLogResource\Pages;

use App\Filament\Admin\Resources\FcmLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFcmLogs extends ListRecords
{
    protected static string $resource = FcmLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
