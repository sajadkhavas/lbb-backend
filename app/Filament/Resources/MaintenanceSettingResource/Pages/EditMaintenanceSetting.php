<?php

namespace App\Filament\Resources\MaintenanceSettingResource\Pages;

use App\Filament\Resources\MaintenanceSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceSetting extends EditRecord
{
    protected static string $resource = MaintenanceSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
