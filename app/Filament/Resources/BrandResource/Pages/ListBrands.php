<?php
namespace App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListBrands extends ListRecords
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
