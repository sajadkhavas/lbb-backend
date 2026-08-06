<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NavigationItemResource\Pages;
use App\Models\NavigationItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NavigationItemResource extends Resource
{
    protected static ?string $model = NavigationItem::class;
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static ?string $navigationLabel = 'منوی سایت';
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('label')
                    ->label('عنوان منو')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('href')
                    ->label('لینک')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('parent_id')
                    ->label('زیرمنوی')
                    ->options(fn () => NavigationItem::whereNull('parent_id')->pluck('label', 'id'))
                    ->nullable()
                    ->placeholder('منوی اصلی (بدون والد)'),

                Forms\Components\TextInput::make('sort_order')
                    ->label('ترتیب نمایش')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('icon')
                    ->label('آیکون (اختیاری)')
                    ->maxLength(100),

                Forms\Components\Textarea::make('description')
                    ->label('توضیحات (اختیاری)')
                    ->rows(2),

                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('href')
                    ->label('لینک')
                    ->searchable(),

                Tables\Columns\TextColumn::make('parent.label')
                    ->label('والد')
                    ->default('منوی اصلی'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتیب')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListNavigationItems::route('/'),
            'create' => Pages\CreateNavigationItem::route('/create'),
            'edit'   => Pages\EditNavigationItem::route('/{record}/edit'),
        ];
    }
}
