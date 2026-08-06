<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SliderResource\Pages;
use App\Models\Slider;
use Filament\Forms\Components\{FileUpload, Grid, Textarea, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationLabel = 'اسلایدر صفحه اصلی';
    protected static ?string $modelLabel = 'اسلاید';
    protected static ?string $pluralModelLabel = 'اسلایدها';
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->label('عنوان')->required(),
            TextInput::make('subtitle')->label('زیرعنوان'),
            Textarea::make('description')->label('توضیحات')->rows(2),
            FileUpload::make('image')->image()->label('تصویر اسلاید')->required()
                ->directory('sliders')->columnSpanFull(),
            Grid::make(3)->schema([
                TextInput::make('link')->label('لینک')->url(),
                TextInput::make('button_text')->label('متن دکمه'),
                TextInput::make('badge')->label('بج (مثلاً: جدید)'),
            ]),
            Grid::make(2)->schema([
                TextInput::make('sort_order')->numeric()->default(0)->label('ترتیب'),
                Toggle::make('is_active')->default(true)->label('فعال'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('تصویر')->size(60),
                Tables\Columns\TextColumn::make('title')->label('عنوان')->searchable(),
                Tables\Columns\TextColumn::make('subtitle')->label('زیرعنوان'),
                Tables\Columns\TextColumn::make('sort_order')->label('ترتیب'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('فعال'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->reorderable('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit'   => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
