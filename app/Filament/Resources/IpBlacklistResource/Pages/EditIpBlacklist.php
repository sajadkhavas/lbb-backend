<?php

namespace App\Filament\Resources\IpBlacklistResource\Pages;

use App\Filament\Resources\IpBlacklistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIpBlacklist extends EditRecord
{
    protected static string $resource = IpBlacklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
