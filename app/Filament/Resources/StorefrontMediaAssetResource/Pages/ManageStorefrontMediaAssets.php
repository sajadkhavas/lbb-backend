<?php

namespace App\Filament\Resources\StorefrontMediaAssetResource\Pages;

use App\Filament\Resources\StorefrontMediaAssetResource;
use App\Models\StorefrontMediaAsset;
use App\Support\AdminImageUpload;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class ManageStorefrontMediaAssets extends ManageRecords
{
    protected static string $resource = StorefrontMediaAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('bulkUpload')->label('آپلود گروهی تصاویر')
            ->icon('heroicon-o-arrow-up-tray')
            ->form([
                Forms\Components\FileUpload::make('files')->label('تصاویر')->multiple()->image()
                    ->storeFiles(false)->acceptedFileTypes(AdminImageUpload::acceptedMimeTypes())
                    ->maxSize(AdminImageUpload::maxKilobytes())->maxFiles(50)
                    ->rules(AdminImageUpload::dimensionRules())->required(),
                Forms\Components\Select::make('usage')->label('کاربرد اولیه')
                    ->options(['unassigned' => 'تخصیص‌نیافته', 'hero' => 'بنر',
                        'brand' => 'برند', 'category' => 'دسته', 'product' => 'محصول',
                        'article' => 'مقاله', 'gallery' => 'گالری', 'page' => 'صفحه'])
                    ->default('unassigned')->required(),
                Forms\Components\Textarea::make('notes')->label('یادداشت مشترک')->rows(2),
            ])
            ->action(function (array $data): void {
                $ready = 0;
                $pending = 0;
                foreach ($data['files'] ?? [] as $file) {
                    if (! $file instanceof TemporaryUploadedFile) continue;
                    $title = Str::of(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        ->replace(['_', '-'], ' ')->squish()->limit(220, '')->toString();
                    $asset = StorefrontMediaAsset::query()->create([
                        'title' => $title ?: 'تصویر LBB', 'usage' => $data['usage'],
                        'status' => 'pending', 'notes' => $data['notes'] ?? null,
                    ]);
                    try {
                        $asset->addMedia($file)->usingName($asset->title)->toMediaCollection('source');
                        $asset->markReadyAfterUpload() ? $ready++ : $pending++;
                    } catch (Throwable $exception) {
                        $asset->delete();
                        report($exception);
                        $pending++;
                    }
                }
                Notification::make()->title("{$ready} تصویر آماده؛ {$pending} تصویر نیازمند بررسی")
                    ->body('تصاویر آماده بدون تأیید دستی قابل استفاده‌اند.')->send();
            }),
            Actions\CreateAction::make()->label('بارگذاری تصویر')
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
