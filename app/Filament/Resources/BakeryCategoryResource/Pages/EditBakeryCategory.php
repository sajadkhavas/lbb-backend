<?php

namespace App\Filament\Resources\BakeryCategoryResource\Pages;

use App\Filament\Resources\BakeryCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBakeryCategory extends EditRecord
{
    protected static string $resource = BakeryCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
