<?php

namespace App\Filament\Resources\BakeryFaqResource\Pages;

use App\Filament\Resources\BakeryFaqResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageBakeryFaqs extends ManageRecords
{
    protected static string $resource = BakeryFaqResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
