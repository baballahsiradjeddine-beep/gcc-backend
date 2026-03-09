<?php

namespace App\Filament\Admin\Resources\GeminiSettingResource\Pages;

use App\Filament\Admin\Resources\GeminiSettingResource;
use App\Services\GeminiAiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditGeminiSetting extends EditRecord
{
    protected static string $resource = GeminiSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset_to_default')
                ->label('إعادة التعيين للافتراضي')
                ->color('danger')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn () => $this->record->key === 'system_prompt')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'value' => GeminiAiService::getDefaultTemplate()
                    ]);

                    $this->refreshFormData(['value']);

                    Notification::make()
                        ->title('تمت إعادة التعيين')
                        ->body('تمت استعادة النص البرمجي الافتراضي بنجاح.')
                        ->success()
                        ->send();
                }),
        ];
    }
}

