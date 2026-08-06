<?php

namespace App\Filament\Resources\MaintenanceSettingResource\Pages;

use App\Filament\Resources\MaintenanceSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceSettings extends ListRecords
{
    protected static string $resource = MaintenanceSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
