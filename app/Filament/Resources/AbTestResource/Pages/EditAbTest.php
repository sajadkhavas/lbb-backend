<?php

namespace App\Filament\Resources\AbTestResource\Pages;

use App\Filament\Resources\AbTestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAbTest extends EditRecord
{
    protected static string $resource = AbTestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
