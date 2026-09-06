<?php

namespace App\Filament\Resources\ProductMediaAssetResource\Pages;

use App\Filament\Resources\ProductMediaAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProductMediaAssets extends ManageRecords
{
    protected static string $resource = ProductMediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
