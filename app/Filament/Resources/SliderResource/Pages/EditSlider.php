<?php
namespace App\Filament\Resources\SliderResource\Pages;
use App\Filament\Resources\SliderResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
class EditSlider extends EditRecord {
    protected static string $resource = SliderResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
