<?php
namespace App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
class EditBrand extends EditRecord {
    protected static string $resource = BrandResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
