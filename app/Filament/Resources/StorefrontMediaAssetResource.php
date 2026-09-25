<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StorefrontMediaAssetResource\Pages;
use App\Models\StorefrontMediaAsset;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StorefrontMediaAssetResource extends Resource
{
    protected static ?string $model = StorefrontMediaAsset::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'کتابخانه تصاویر';
    protected static ?string $navigationGroup = 'محتوا';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            SpatieMediaLibraryFileUpload::make('source_image')
                ->label('فایل اصلی')->collection('source')->image()->imageEditor()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(10240)
                ->rules(['dimensions:max_width=6000,max_height=6000'])
                ->required(fn (?StorefrontMediaAsset $record): bool => $record === null)
                ->disabled(fn (?StorefrontMediaAsset $record): bool => $record !== null)
                ->helperText('فایل اصلی حفظ می‌شود؛ پیش‌نمایش و تصویر کوچک WebP ساخته می‌شوند. برای جایگزینی، رسانه جدید بسازید.')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('title')->label('عنوان داخلی')->required()->maxLength(220),
            Forms\Components\TextInput::make('alt_text')->label('متن جایگزین')->maxLength(500),
            Forms\Components\Select::make('usage')->label('کاربرد')
                ->options([
                    'unassigned' => 'تخصیص‌نیافته', 'product' => 'محصول',
                    'category' => 'دسته', 'article' => 'مقاله', 'gallery' => 'گالری',
                    'page' => 'صفحه', 'hero' => 'بنر', 'brand' => 'برند',
                ])->default('unassigned')->required(),
            Forms\Components\Select::make('status')->label('وضعیت')
                ->options(['pending' => 'نیازمند بررسی', 'ready' => 'آماده استفاده', 'rejected' => 'رد شده'])
                ->default('pending')->required()
                ->helperText('فقط پس از آماده شدن نسخه WebP کمتر از ۱MB قابل تأیید است.'),
            Forms\Components\Textarea::make('notes')->label('یادداشت')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ImageColumn::make('preview')->label('تصویر')
                ->getStateUsing(fn (StorefrontMediaAsset $record): ?string => $record->previewUrl())->square(),
            Tables\Columns\TextColumn::make('title')->label('عنوان')->searchable(),
            Tables\Columns\TextColumn::make('usage')->label('کاربرد')->badge(),
            Tables\Columns\TextColumn::make('status')->label('وضعیت')->badge(),
            Tables\Columns\TextColumn::make('updated_at')->label('آخرین ویرایش')->dateTime()->sortable(),
        ])->actions([Tables\Actions\EditAction::make()])->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageStorefrontMediaAssets::route('/')];
    }
}
