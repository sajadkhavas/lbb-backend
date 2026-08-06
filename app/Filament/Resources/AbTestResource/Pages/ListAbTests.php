<?php

namespace App\Filament\Resources\AbTestResource\Pages;

use App\Filament\Resources\AbTestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAbTests extends ListRecords
{
    protected static string $resource = AbTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
