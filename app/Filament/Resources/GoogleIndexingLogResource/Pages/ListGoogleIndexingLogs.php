<?php

namespace App\Filament\Resources\GoogleIndexingLogResource\Pages;

use App\Filament\Resources\GoogleIndexingLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGoogleIndexingLogs extends ListRecords
{
    protected static string $resource = GoogleIndexingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
