<?php
namespace App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
class EditProduct extends EditRecord {
    protected static string $resource = ProductResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
