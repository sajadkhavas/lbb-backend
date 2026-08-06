<?php

namespace App\Filament\Resources\SchemaMarkupResource\Pages;

use App\Filament\Resources\SchemaMarkupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchemaMarkups extends ListRecords
{
    protected static string $resource = SchemaMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
