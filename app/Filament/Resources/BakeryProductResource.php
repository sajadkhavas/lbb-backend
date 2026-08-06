<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BakeryProductResource\Pages;
use App\Models\BakeryProduct;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BakeryProductResource extends Resource
{
    protected static ?string $model = BakeryProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-cake';

    protected static ?string $navigationLabel = 'محصولات بیکری';

    protected static ?string $modelLabel = 'محصول بیکری';

    protected static ?string $pluralModelLabel = 'محصولات بیکری';

    protected static ?string $navigationGroup = 'فروشگاه وینیمی';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make('bakery-product')
                ->tabs([
                    Forms\Components\Tabs\Tab::make('اطلاعات محصول')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('نام محصول')
                                        ->required()
                                        ->maxLength(180)
                                        ->live(onBlur: true),
                                    Forms\Components\TextInput::make('product_code')
                                        ->label('کد محصول')
                                        ->required()
                                        ->maxLength(80)
                                        ->unique(ignoreRecord: true),
                                    Forms\Components\TextInput::make('slug')
                                        ->label('Slug')
                                        ->maxLength(200)
                                        ->helperText('در صورت خالی‌بودن از نام محصول ساخته می‌شود.'),
                                    Forms\Components\Select::make('category_id')
                                        ->label('دسته‌بندی')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Forms\Components\Textarea::make('short_description')
                                        ->label('توضیح کوتاه')
                                        ->rows(3)
                                        ->maxLength(320)
                                        ->columnSpanFull(),
                                    Forms\Components\RichEditor::make('description')
                                        ->label('توضیح کامل')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                            Forms\Components\Section::make('انتشار و تحویل')
                                ->schema([
                                    Forms\Components\Toggle::make('is_active')
                                        ->label('فعال در فروشگاه')
                                        ->default(false),
                                    Forms\Components\Toggle::make('is_featured')
                                        ->label('پیشنهاد وینیمی')
                                        ->default(false),
                                    Forms\Components\Toggle::make('requires_cooling')
                                        ->label('نیازمند نگهداری و ارسال سرد')
                                        ->default(false),
                                    Forms\Components\TextInput::make('preparation_time_days')
                                        ->label('زمان آماده‌سازی (روز)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(30),
                                    Forms\Components\TextInput::make('sort_order')
                                        ->label('ترتیب نمایش')
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(0),
                                ])
                                ->columns(3),
                        ]),
                    Forms\Components\Tabs\Tab::make('قیمت و موجودی')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Forms\Components\Repeater::make('variants')
                                ->label('Variantهای قابل فروش')
                                ->relationship()
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('نام انتخاب')
                                        ->placeholder('مثلاً بسته ۶ عددی')
                                        ->required()
                                        ->maxLength(120),
                                    Forms\Components\TextInput::make('sku')
                                        ->label('SKU')
                                        ->required()
                                        ->maxLength(100)
                                        ->unique(ignoreRecord: true),
                                    Forms\Components\TextInput::make('weight_grams')
                                        ->label('وزن (گرم)')
                                        ->numeric()
                                        ->minValue(1),
                                    Forms\Components\TextInput::make('regular_price_toman')
                                        ->label('قیمت عادی (تومان)')
                                        ->numeric()
                                        ->required()
                                        ->minValue(1),
                                    Forms\Components\TextInput::make('sale_price_toman')
                                        ->label('قیمت فروش (تومان)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->lt('regular_price_toman'),
                                    Forms\Components\TextInput::make('stock_quantity')
                                        ->label('موجودی')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->default(0),
                                    Forms\Components\TextInput::make('low_stock_threshold')
                                        ->label('حد هشدار موجودی')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->default(5),
                                    Forms\Components\Toggle::make('is_default')
                                        ->label('انتخاب پیش‌فرض'),
                                    Forms\Components\Toggle::make('is_active')
                                        ->label('قابل فروش')
                                        ->default(true),
                                    Forms\Components\TextInput::make('sort_order')
                                        ->label('ترتیب')
                                        ->numeric()
                                        ->minValue(0)
                                        ->default(0),
                                ])
                                ->columns(3)
                                ->defaultItems(1)
                                ->minItems(1)
                                ->reorderableWithButtons()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Variant جدید')
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Tabs\Tab::make('ترکیبات و نگهداری')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->schema([
                            Forms\Components\TagsInput::make('ingredients')
                                ->label('ترکیبات')
                                ->separator(','),
                            Forms\Components\TagsInput::make('allergens')
                                ->label('آلرژن‌ها')
                                ->separator(','),
                            Forms\Components\TextInput::make('shelf_life')
                                ->label('ماندگاری')
                                ->maxLength(220),
                            Forms\Components\Textarea::make('storage_instructions')
                                ->label('روش نگهداری')
                                ->rows(4),
                            Forms\Components\Toggle::make('content_verified')
                                ->label('اطلاعات محصول تأیید شده است')
                                ->helperText('فقط بعد از بررسی ترکیبات، آلرژن، ماندگاری و نگهداری فعال شود.')
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Forms\Components\Tabs\Tab::make('رسانه')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('main_image')
                                ->label('تصویر اصلی')
                                ->collection('catalog-main')
                                ->image()
                                ->imageEditor()
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                                ->columnSpanFull(),
                            SpatieMediaLibraryFileUpload::make('gallery_images')
                                ->label('گالری')
                                ->collection('catalog-gallery')
                                ->multiple()
                                ->reorderable()
                                ->maxFiles(10)
                                ->image()
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('media_verified')
                                ->label('تصاویر متعلق به همین محصول و تأییدشده هستند')
                                ->columnSpanFull(),
                        ]),
                    Forms\Components\Tabs\Tab::make('سئو')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Forms\Components\TextInput::make('meta_title')
                                ->label('عنوان سئو')
                                ->maxLength(70),
                            Forms\Components\Textarea::make('meta_description')
                                ->label('توضیح سئو')
                                ->maxLength(180)
                                ->rows(3),
                        ])
                        ->columns(2),
                ])
                ->persistTabInQueryString()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('main_image')
                    ->label('تصویر')
                    ->collection('catalog-main')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('نام محصول')
                    ->searchable()
                    ->sortable()
                    ->limit(45),
                Tables\Columns\TextColumn::make('product_code')
                    ->label('کد')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('دسته')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('variants_count')
                    ->label('Variant')
                    ->counts('variants'),
                Tables\Columns\TextColumn::make('variants_sum_stock_quantity')
                    ->label('موجودی کل')
                    ->sum('variants', 'stock_quantity')
                    ->sortable(),
                Tables\Columns\IconColumn::make('requires_cooling')
                    ->label('سرد')
                    ->boolean(),
                Tables\Columns\IconColumn::make('content_verified')
                    ->label('محتوا')
                    ->boolean(),
                Tables\Columns\IconColumn::make('media_verified')
                    ->label('رسانه')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('ویژه')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('دسته')
                    ->relationship('category', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('پیشنهادی'),
                Tables\Filters\TernaryFilter::make('requires_cooling')->label('نیازمند سرما'),
                Tables\Filters\TernaryFilter::make('content_verified')->label('محتوای تأییدشده'),
                Tables\Filters\TernaryFilter::make('media_verified')->label('رسانه تأییدشده'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBakeryProducts::route('/'),
            'create' => Pages\CreateBakeryProduct::route('/create'),
            'edit' => Pages\EditBakeryProduct::route('/{record}/edit'),
        ];
    }
}
