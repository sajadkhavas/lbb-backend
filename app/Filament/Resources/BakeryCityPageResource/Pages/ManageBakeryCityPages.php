<?php

namespace App\Filament\Resources\BakeryCityPageResource\Pages;

use App\Filament\Resources\BakeryCityPageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBakeryCityPages extends ManageRecords
{
    protected static string $resource = BakeryCityPageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
