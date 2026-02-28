<?php

namespace App\Filament\Admin\Resources\AppAssetResource\Pages;

use App\Filament\Admin\Resources\AppAssetResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditAppAsset extends EditRecord
{
    protected static string $resource = AppAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('✅ تم تحديث الصورة')
            ->body('سيقوم التطبيق بتنزيل الصورة الجديدة تلقائياً عند الفتح القادم');
    }

}
