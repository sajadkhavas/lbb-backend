<?php

namespace App\Filament\Resources\BakeryCategoryResource\Pages;

use App\Filament\Resources\BakeryCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBakeryCategories extends ListRecords
{
    protected static string $resource = BakeryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
