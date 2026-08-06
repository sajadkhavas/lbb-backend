<?php

namespace App\Filament\Resources\BakeryProductResource\Pages;

use App\Filament\Resources\BakeryProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBakeryProduct extends EditRecord
{
    protected static string $resource = BakeryProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
