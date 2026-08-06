<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeatureFlagResource\Pages;
use App\Models\FeatureFlag;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeatureFlagResource extends Resource
{
    protected static ?string $model = FeatureFlag::class;
    protected static ?string $navigationIcon  = 'heroicon-o-flag';
    protected static ?string $navigationLabel = 'Feature Flags';
    protected static ?string $modelLabel      = 'Feature Flag';
    protected static ?string $pluralModelLabel = 'Feature Flags';
    protected static ?string $navigationGroup = 'پیشرفته';
    protected static ?int    $navigationSort  = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('اطلاعات')->schema([
                TextInput::make('name')
                    ->label('نام')
                    ->required(),

                TextInput::make('key')
                    ->label('کلید (key)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('مثال: show_new_checkout')
                    ->helperText('برای استفاده در کد: FeatureFlag::isEnabled("key")'),

                Textarea::make('description')
                    ->label('توضیحات')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_enabled')
                    ->label('فعال')
                    ->default(false)
                    ->onColor('success')
                    ->offColor('danger'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('key')->label('کلید')->badge()->color('gray')->copyable(),
                Tables\Columns\TextColumn::make('description')->label('توضیحات')->limit(50)->default('—'),
                Tables\Columns\ToggleColumn::make('is_enabled')->label('فعال'),
                Tables\Columns\TextColumn::make('updated_at')->label('بروزرسانی')->dateTime('Y/m/d H:i')->sortable(),
            ])
            ->defaultSort('id', 'desc')
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
            'index'  => Pages\ListFeatureFlags::route('/'),
            'create' => Pages\CreateFeatureFlag::route('/create'),
            'edit'   => Pages\EditFeatureFlag::route('/{record}/edit'),
        ];
    }
}
