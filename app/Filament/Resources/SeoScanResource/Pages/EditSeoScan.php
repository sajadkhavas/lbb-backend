<?php

namespace App\Filament\Resources\SeoScanResource\Pages;

use App\Filament\Resources\SeoScanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSeoScan extends EditRecord
{
    protected static string $resource = SeoScanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
