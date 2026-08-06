<?php

namespace App\Filament\Resources\BakeryContentPageResource\Pages;

use App\Filament\Resources\BakeryContentPageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBakeryContentPages extends ManageRecords
{
    protected static string $resource = BakeryContentPageResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
