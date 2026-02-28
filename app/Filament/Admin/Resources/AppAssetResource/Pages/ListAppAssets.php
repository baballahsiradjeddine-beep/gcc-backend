<?php

namespace App\Filament\Admin\Resources\AppAssetResource\Pages;

use App\Filament\Admin\Resources\AppAssetResource;
use Filament\Resources\Pages\ListRecords;

class ListAppAssets extends ListRecords
{
    protected static string $resource = AppAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
