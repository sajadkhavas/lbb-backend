<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BakeryCategoryResource\Pages;
use App\Models\BakeryCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BakeryCategoryResource extends Resource
{
    protected static ?string $model = BakeryCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'دسته‌های بیکری';

    protected static ?string $modelLabel = 'دسته بیکری';

    protected static ?string $pluralModelLabel = 'دسته‌های بیکری';

    protected static ?string $navigationGroup = 'فروشگاه وینیمی';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('اطلاعات دسته')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('نام دسته')
                        ->required()
                        ->maxLength(120)
                        ->live(onBlur: true),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->maxLength(140)
                        ->helperText('در صورت خالی‌بودن از نام ساخته می‌شود.'),
                    Forms\Components\Textarea::make('description')
                        ->label('توضیح دسته')
                        ->rows(4)
                        ->columnSpanFull(),
                    Forms\Components\FileUpload::make('image_path')
                        ->label('تصویر دسته')
                        ->image()
                        ->imageEditor()
                        ->directory('bakery/categories')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->label('فعال در فروشگاه')
                        ->default(true),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('ترتیب نمایش')
                        ->numeric()
                        ->minValue(0)
                        ->default(0),
                ])
                ->columns(2),
            Forms\Components\Section::make('سئو')
                ->schema([
                    Forms\Components\TextInput::make('meta_title')
                        ->label('عنوان سئو')
                        ->maxLength(70),
                    Forms\Components\Textarea::make('meta_description')
                        ->label('توضیح سئو')
                        ->maxLength(180)
                        ->rows(3),
                ])
                ->columns(2)
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('تصویر')
                    ->square(),
                Tables\Columns\TextColumn::make('name')
                    ->label('نام')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('محصولات')
                    ->counts('products')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتیب')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخرین تغییر')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
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
            'index' => Pages\ListBakeryCategories::route('/'),
            'create' => Pages\CreateBakeryCategory::route('/create'),
            'edit' => Pages\EditBakeryCategory::route('/{record}/edit'),
        ];
    }
}
