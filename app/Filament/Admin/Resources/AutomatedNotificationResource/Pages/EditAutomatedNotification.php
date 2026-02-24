<?php

namespace App\Filament\Admin\Resources\AutomatedNotificationResource\Pages;

use App\Filament\Admin\Resources\AutomatedNotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAutomatedNotification extends EditRecord
{
    protected static string $resource = AutomatedNotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
