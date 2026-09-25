<?php

namespace App\Filament\Resources\StorefrontMediaAssetResource\Pages;

use App\Filament\Resources\StorefrontMediaAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStorefrontMediaAssets extends ManageRecords
{
    protected static string $resource = StorefrontMediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('بارگذاری تصویر')];
    }
}
