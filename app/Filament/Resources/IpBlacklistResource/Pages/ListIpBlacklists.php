<?php

namespace App\Filament\Resources\IpBlacklistResource\Pages;

use App\Filament\Resources\IpBlacklistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIpBlacklists extends ListRecords
{
    protected static string $resource = IpBlacklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
