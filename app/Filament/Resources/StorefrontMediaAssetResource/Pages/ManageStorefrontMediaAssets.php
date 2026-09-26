<?php

namespace App\Filament\Resources\StorefrontMediaAssetResource\Pages;

use App\Filament\Resources\StorefrontMediaAssetResource;
use App\Models\StorefrontMediaAsset;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;

class ManageStorefrontMediaAssets extends ManageRecords
{
    protected static string $resource = StorefrontMediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('بارگذاری تصویر')
            ->mutateFormDataUsing(function (array $data): array {
                $data['status'] = 'pending';

                return $data;
            })
            ->after(function (StorefrontMediaAsset $record): void {
                if ($record->markReadyAfterUpload()) {
                    Notification::make()->success()->title('نسخه WebP آماده شد؛ تصویر خودکار تأیید شد.')->send();
                } else {
                    Notification::make()->warning()->title('تصویر ذخیره شد، اما نسخه بهینه هنوز آماده نیست.')
                        ->body('فایل اصلی حفظ شد. پس از بررسی وضعیت پردازش، از «بازسازی تصویر بهینه» استفاده کنید.')
                        ->send();
                }
            })];
    }
}
