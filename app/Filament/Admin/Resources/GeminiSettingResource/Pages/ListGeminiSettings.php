<?php

namespace App\Filament\Admin\Resources\GeminiSettingResource\Pages;

use App\Filament\Admin\Resources\GeminiSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListGeminiSettings extends ListRecords
{
    protected static string $resource = GeminiSettingResource::class;

    public function mount(): void
    {
        // Ensure default prompt exists
        \App\Models\GeminiSetting::firstOrCreate(
            ['key' => 'system_prompt'],
            ['value' => \App\Services\GeminiAiService::getDefaultTemplate()]
        );

        // Ensure API Key record exists (even if empty)
        \App\Models\GeminiSetting::firstOrCreate(
            ['key' => 'api_key'],
            ['value' => env('GEMINI_API_KEY')]
        );

        // Ensure default model is selected
        \App\Models\GeminiSetting::firstOrCreate(
            ['key' => 'model_name'],
            ['value' => 'gemini-1.5-flash']
        );
    }
}


