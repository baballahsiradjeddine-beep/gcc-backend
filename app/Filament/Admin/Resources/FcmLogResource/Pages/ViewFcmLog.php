<?php

namespace App\Filament\Admin\Resources\FcmLogResource\Pages;

use App\Filament\Admin\Resources\FcmLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFcmLog extends ViewRecord
{
    protected static string $resource = FcmLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
