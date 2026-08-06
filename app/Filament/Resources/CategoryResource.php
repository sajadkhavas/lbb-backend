<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components\{FileUpload, Grid, RichEditor, Tabs, Textarea, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'دسته‌بندی‌ها';
    protected static ?string $modelLabel = 'دسته';
    protected static ?string $pluralModelLabel = 'دسته‌بندی‌ها';
    protected static ?string $navigationGroup = 'محتوا';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('cat')->tabs([
                Tabs\Tab::make('اطلاعات پایه')->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')->label('نام فارسی')->required(),
                        TextInput::make('name_en')->label('نام انگلیسی (برای slug)')->required(),
                    ]),
                    TextInput::make('slug')->label('Slug (URL)')->unique(ignoreRecord: true),
                    TextInput::make('icon')->label('آیکون (Lucide)')->placeholder('Zap, Gauge, Cpu …'),
                    Textarea::make('description')->label('توضیح کوتاه')->rows(2),
                    FileUpload::make('image')->label('تصویر دسته')->image()->directory('categories'),
                    Grid::make(2)->schema([
                        TextInput::make('sort_order')->numeric()->default(0)->label('ترتیب'),
                        Toggle::make('is_active')->label('فعال')->default(true),
                    ]),
                ]),
                Tabs\Tab::make('هیرو و محتوای سئو')->schema([
                    TextInput::make('hero_title')->label('عنوان هیرو'),
                    Textarea::make('hero_subtitle')->label('زیرعنوان هیرو')->rows(2),
                    RichEditor::make('long_description')->label('محتوای سئو (600+ کلمه)')->columnSpanFull(),
                ]),
                Tabs\Tab::make('سئو')->schema([
                    TextInput::make('meta_title')->maxLength(60)->label('عنوان متا'),
                    Textarea::make('meta_description')->maxLength(160)->rows(2)->label('توضیح متا'),
                    TextInput::make('meta_keywords')->label('کلمات کلیدی'),
                    FileUpload::make('og_image')->image()->label('تصویر OG')->directory('categories/og'),
                ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->copyable(),
                Tables\Columns\TextColumn::make('products_count')->counts('products')->label('محصولات'),
                Tables\Columns\TextColumn::make('sort_order')->label('ترتیب')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getRelations(): array { return [CategoryResource\RelationManagers\SubcategoriesRelationManager::class]; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
