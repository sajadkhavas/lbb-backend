<?php

namespace App\Filament\Resources\BakeryPostResource\Pages;

use App\Filament\Resources\BakeryPostResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBakeryPosts extends ManageRecords
{
    protected static string $resource = BakeryPostResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
