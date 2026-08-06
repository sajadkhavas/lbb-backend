<?php

namespace App\Filament\Resources\SchemaMarkupResource\Pages;

use App\Filament\Resources\SchemaMarkupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchemaMarkup extends EditRecord
{
    protected static string $resource = SchemaMarkupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
