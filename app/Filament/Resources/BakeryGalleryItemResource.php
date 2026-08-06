<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BakeryGalleryItemResource\Pages;
use App\Models\BakeryGalleryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BakeryGalleryItemResource extends Resource
{
    protected static ?string $model = BakeryGalleryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'گالری بیکری';

    protected static ?string $modelLabel = 'تصویر گالری';

    protected static ?string $pluralModelLabel = 'گالری بیکری';

    protected static ?string $navigationGroup = 'محتوا';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('عنوان')->required()->maxLength(220),
            Forms\Components\TextInput::make('sort_order')->label('ترتیب')->numeric()->default(0)->required(),
            Forms\Components\Toggle::make('is_active')->label('فعال')->default(true),
            Forms\Components\TextInput::make('image_url')->label('آدرس تصویر')->url()->required()->columnSpanFull(),
            Forms\Components\TextInput::make('link_url')->label('لینک مقصد')->url()->nullable()->columnSpanFull(),
            Forms\Components\Textarea::make('caption')->label('توضیح')->rows(3)->columnSpanFull(),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')->label('تصویر')->square(),
                Tables\Columns\TextColumn::make('title')->label('عنوان')->searchable(),
                Tables\Columns\TextColumn::make('sort_order')->label('ترتیب')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('آخرین تغییر')->dateTime('Y/m/d H:i')->sortable(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->label('فعال')])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageBakeryGalleryItems::route('/')];
    }
}
