<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShortUrlResource\Pages;
use App\Models\ShortUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ShortUrlResource extends Resource
{
    protected static ?string $model = ShortUrl::class;
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'بازاریابی';
    protected static ?string $label = 'لینک کوتاه';
    protected static ?string $pluralLabel = 'لینک‌های کوتاه';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('code')
                ->label('کد')
                ->required()
                ->unique(ignoreRecord: true)
                ->default(fn() => Str::random(6))
                ->helperText('کد یکتا برای لینک کوتاه'),
            Forms\Components\TextInput::make('title')
                ->label('عنوان'),
            Forms\Components\TextInput::make('destination_url')
                ->label('لینک مقصد')
                ->required()
                ->url()
                ->columnSpanFull(),
            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),
            Forms\Components\DateTimePicker::make('expires_at')
                ->label('انقضا در'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('کد')
                    ->searchable()
                    ->copyable()
                    ->url(fn($record) => url('/s/' . $record->code), true),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('destination_url')
                    ->label('مقصد')
                    ->limit(40)
                    ->url(fn($record) => $record->destination_url, true),
                Tables\Columns\TextColumn::make('click_count')
                    ->label('کلیک')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('انقضا')
                    ->dateTime()
                    ->sortable(),
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListShortUrls::route('/'),
            'create' => Pages\CreateShortUrl::route('/create'),
            'edit'   => Pages\EditShortUrl::route('/{record}/edit'),
        ];
    }
}
