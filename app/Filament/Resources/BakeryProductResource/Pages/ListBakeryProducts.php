<?php

namespace App\Filament\Resources\BakeryProductResource\Pages;

use App\Filament\Resources\BakeryProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBakeryProducts extends ListRecords
{
    protected static string $resource = BakeryProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
