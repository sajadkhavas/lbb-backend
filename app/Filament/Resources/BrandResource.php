<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms\Components\{FileUpload, Grid, RichEditor, Tabs, Textarea, TextInput, Toggle, Select};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;
    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'برندها';
    protected static ?string $modelLabel = 'برند';
    protected static ?string $pluralModelLabel = 'برندها';
    protected static ?string $navigationGroup = 'محتوا';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('brand')->tabs([
                Tabs\Tab::make('اطلاعات پایه')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')->label('نام برند')->required(),
                        TextInput::make('slug')->label('Slug')->unique(ignoreRecord: true),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('country')->label('کشور')->options([
                            'DE' => 'آلمان', 'US' => 'آمریکا', 'UK' => 'انگلستان',
                            'JP' => 'ژاپن', 'CH' => 'سوئیس', 'NL' => 'هلند', 'IT' => 'ایتالیا', 'FR' => 'فرانسه',
                        ]),
                        TextInput::make('website')->url()->label('وب‌سایت'),
                    ]),
                    FileUpload::make('logo')->image()->label('لوگو')->directory('brands'),
                    Textarea::make('description')->label('توضیح کوتاه')->rows(2),
                    RichEditor::make('long_description')->label('محتوای سئو (800+ کلمه)')->columnSpanFull(),
                    Grid::make(3)->schema([
                        TextInput::make('sort_order')->numeric()->default(0)->label('ترتیب'),
                        Toggle::make('is_featured')->label('برند ویژه'),
                        Toggle::make('is_active')->default(true)->label('فعال'),
                    ]),
                ]),
                Tabs\Tab::make('سئو')->schema([
                    TextInput::make('meta_title')->maxLength(60)->label('عنوان متا'),
                    Textarea::make('meta_description')->maxLength(160)->rows(2)->label('توضیح متا'),
                    TextInput::make('meta_keywords')->label('کلمات کلیدی'),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->label('')->size(40),
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country')->label('کشور')->badge(),
                Tables\Columns\TextColumn::make('products_count')->counts('products')->label('محصولات'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('ویژه'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('فعال'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit'   => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
