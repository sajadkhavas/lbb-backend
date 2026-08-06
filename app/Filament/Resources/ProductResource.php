<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use FilamentTiptapEditor\Enums\TiptapOutput;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'محصولات';
    protected static ?string $modelLabel      = 'محصول';
    protected static ?string $pluralModelLabel = 'محصولات';
    protected static ?string $navigationGroup = 'محتوا';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('product')->tabs([

                Tabs\Tab::make('اطلاعات پایه')->icon('heroicon-o-document-text')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')->label('نام محصول')->required()->maxLength(200)->live(onBlur: true),
                        TextInput::make('model')->label('مدل')->maxLength(100),
                    ]),
                    Grid::make(3)->schema([
                        Select::make('category_id')->label('دسته‌بندی')
                            ->relationship('category', 'name')->searchable()->required()->preload(),
                        Select::make('subcategory_id')->label('زیرمجموعه')
                            ->relationship('subcategory', 'name')->searchable()->preload(),
                        Select::make('brand_id')->label('برند')
                            ->relationship('brand', 'name')->searchable()->preload(),
                    ]),
                    Grid::make(3)->schema([
                        Select::make('country')->label('کشور سازنده')->options([
                            'DE' => '🇩🇪 آلمان', 'US' => '🇺🇸 امریکا', 'UK' => '🇬🇧 انگلستان',
                            'JP' => '🇯🇵 ژاپن', 'CH' => '🇨🇭 سوئیس', 'NL' => '🇳🇱 هلند',
                            'IT' => '🇮🇹 ایتالیا', 'FR' => '🇫🇷 فرانسه',
                        ]),
                        Select::make('price_range')->label('رنج قیمتی')->options([
                            'budget' => 'ارزان', 'mid' => 'متوسط', 'premium' => 'گران',
                        ]),
                        Select::make('status')->label('وضعیت')
                            ->options(['published' => 'منتشر شده', 'draft' => 'پیش‌نویس', 'archived' => 'آرشیو'])
                            ->default('draft')->required(),
                    ]),
                    Textarea::make('description')->label('توضیحات کوتاه')->rows(3)->required(),
                    Textarea::make('excerpt')->label('چکیده (300 کاراکتر)')->rows(2)->maxLength(300),
                    Grid::make(2)->schema([
                        Toggle::make('in_stock')->label('موجود در انبار')->default(true),
                        Toggle::make('is_featured')->label('محصول ویژه'),
                    ]),
                    TextInput::make('sort_order')->label('ترتیب نمایش')->numeric()->default(0),
                ]),

                Tabs\Tab::make('محتوای کامل')->icon('heroicon-o-document-duplicate')->schema([
                    TiptapEditor::make('long_description')
                        ->label('محتوای کامل محصول (500+ کلمه)')
                        ->output(TiptapOutput::Html)
                        ->columnSpanFull()
                        ->extraInputAttributes(['style' => 'min-height: 400px']),
                ]),

                Tabs\Tab::make('مشخصات فنی')->icon('heroicon-o-adjustments-horizontal')->schema([
                    KeyValue::make('specs')->label('مشخصات فنی')
                        ->keyLabel('نام مشخصه')->valueLabel('مقدار')
                        ->addActionLabel('افزودن مشخصه')->columnSpanFull(),
                    Select::make('usage')->label('نوع کاربرد')->multiple()->options([
                        'educational' => 'آموزشی', 'research' => 'پژوهشی', 'industrial' => 'صنعتی',
                    ]),
                    KeyValue::make('applications')->label('کاربردها')->columnSpanFull(),
                ]),

                Tabs\Tab::make('تصاویر و گالری')->icon('heroicon-o-photo')->schema([
                    FileUpload::make('image')->label('تصویر اصلی')->image()
                        ->imageEditor()->directory('products')->columnSpanFull(),
                    FileUpload::make('gallery')->label('گالری تصاویر')->image()->multiple()
                        ->maxFiles(10)->reorderable()->directory('products/gallery')->columnSpanFull(),
                    FileUpload::make('og_image')->label('تصویر OG (سئو شبکه‌های اجتماعی)')
                        ->image()->directory('products/og'),
                ]),

                Tabs\Tab::make('سئو پیشرفته')->icon('heroicon-o-magnifying-glass')->schema([
                    Section::make('Meta Tags')->schema([
                        TextInput::make('meta_title')->label('Meta Title')->maxLength(60)->live()
                            ->helperText(fn ($state) => 'تعداد کاراکتر: ' . strlen($state ?? '') . ' / 60')
                            ->suffixIcon(fn ($state) => strlen($state ?? '') > 60 ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),
                        Textarea::make('meta_description')->label('Meta Description')->maxLength(160)->rows(3)->live()
                            ->helperText(fn ($state) => 'تعداد کاراکتر: ' . strlen($state ?? '') . ' / 160'),
                        TextInput::make('meta_keywords')->label('Meta Keywords')->helperText('کلمات کلیدی جدا شده با کاما'),
                        Select::make('schema_type')->label('نوع Schema.org')
                            ->options(['Product' => 'Product', 'ItemPage' => 'ItemPage'])->default('Product'),
                    ]),
                    Section::make('پیش‌نمایش گوگل')->schema([
                        Placeholder::make('google_preview')->label('')
                            ->content(fn ($record) => $record
                                ? new \Illuminate\Support\HtmlString('
                                    <div style="font-family:Arial,sans-serif;max-width:600px;padding:16px;border:1px solid #e0e0e0;border-radius:8px;background:#fff;">
                                        <div style="font-size:14px;color:#1a0dab;font-weight:bold;margin-bottom:4px;">
                                            ' . e($record->meta_title ?: $record->name) . '
                                        </div>
                                        <div style="font-size:12px;color:#006621;margin-bottom:4px;">
                                            toolmaster.com › محصولات › ' . e($record->slug ?? '') . '
                                        </div>
                                        <div style="font-size:13px;color:#545454;">
                                            ' . e(substr($record->meta_description ?: $record->description ?? '', 0, 160)) . '
                                        </div>
                                    </div>')
                                : new \Illuminate\Support\HtmlString('<div style="padding:12px;background:#f9f9f9;border-radius:8px;color:#666;">بعد از ذخیره محصول، پیش‌نمایش گوگل اینجا نشان داده می‌شود.</div>')
                            ),
                    ]),
                ]),

            ])->columnSpanFull()->persistTabInQueryString(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('')->circular()->size(40),
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable()->sortable()->limit(40),
                Tables\Columns\TextColumn::make('model')->label('مدل')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('category.name')->label('دسته')->badge(),
                Tables\Columns\TextColumn::make('brand.name')->label('برند')->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('وضعیت')->badge()
                    ->colors(['success' => 'published', 'warning' => 'draft', 'danger' => 'archived']),
                Tables\Columns\IconColumn::make('in_stock')->label('موجودی')->boolean(),
                Tables\Columns\IconColumn::make('is_featured')->label('ویژه')->boolean()->toggleable(),
                Tables\Columns\TextColumn::make('view_count')->label('بازدید')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('rfq_count')->label('استعلام')->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'published' => 'منتشر شده', 'draft' => 'پیش‌نویس', 'archived' => 'آرشیو',
                ]),
                Tables\Filters\SelectFilter::make('category_id')->relationship('category', 'name')->label('دسته'),
                Tables\Filters\SelectFilter::make('brand_id')->relationship('brand', 'name')->label('برند'),
                Tables\Filters\TernaryFilter::make('in_stock')->label('موجودی'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('ویژه'),
            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make('table')->fromTable()
                        ->withFilename('محصولات-' . date('Y-m-d'))
                        ->withColumns([
                            Column::make('name')->heading('نام محصول'),
                            Column::make('model')->heading('مدل'),
                            Column::make('category.name')->heading('دسته‌بندی'),
                            Column::make('brand.name')->heading('برند'),
                            Column::make('country')->heading('کشور'),
                            Column::make('status')->heading('وضعیت'),
                            Column::make('in_stock')->heading('موجودی'),
                            Column::make('view_count')->heading('بازدید'),
                            Column::make('rfq_count')->heading('استعلام'),
                        ]),
                ])->label('خروجی Excel'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exports([
                        ExcelExport::make('selected')->fromTable()
                            ->withFilename('محصولات-انتخابی-' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('name')->heading('نام محصول'),
                                Column::make('model')->heading('مدل'),
                                Column::make('category.name')->heading('دسته‌بندی'),
                                Column::make('brand.name')->heading('برند'),
                                Column::make('status')->heading('وضعیت'),
                            ]),
                    ])->label('خروجی Excel (انتخابی)'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
