<?php

namespace App\Filament\Resources\BakeryGalleryItemResource\Pages;

use App\Filament\Resources\BakeryGalleryItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBakeryGalleryItems extends ManageRecords
{
    protected static string $resource = BakeryGalleryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
