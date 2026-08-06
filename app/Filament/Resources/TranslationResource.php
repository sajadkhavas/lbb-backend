<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TranslationResource\Pages;
use App\Models\Translation;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TranslationResource extends Resource
{
    protected static ?string $model = Translation::class;
    protected static ?string $navigationIcon  = 'heroicon-o-language';
    protected static ?string $navigationLabel = 'Translation Manager';
    protected static ?string $modelLabel      = 'ترجمه';
    protected static ?string $pluralModelLabel = 'ترجمه‌ها';
    protected static ?string $navigationGroup = 'پیشرفته';
    protected static ?int    $navigationSort  = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('اطلاعات')->schema([
                TextInput::make('group')
                    ->label('گروه')
                    ->required()
                    ->placeholder('مثال: messages, buttons, errors'),

                TextInput::make('key')
                    ->label('کلید')
                    ->required()
                    ->placeholder('مثال: submit, cancel, success'),
            ])->columns(2),

            Section::make('ترجمه‌ها')->schema([
                TextInput::make('value.fa')
                    ->label('فارسی 🇮🇷')
                    ->required(),

                TextInput::make('value.en')
                    ->label('English 🇬🇧'),

                TextInput::make('value.ar')
                    ->label('العربية 🇸🇦'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')->label('گروه')->badge()->color('primary')->searchable(),
                Tables\Columns\TextColumn::make('key')->label('کلید')->searchable(),
                Tables\Columns\TextColumn::make('value.fa')->label('فارسی')->limit(40),
                Tables\Columns\TextColumn::make('value.en')->label('English')->limit(40)->default('—'),
                Tables\Columns\TextColumn::make('updated_at')->label('بروزرسانی')->dateTime('Y/m/d')->sortable(),
            ])
            ->defaultSort('group')
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('گروه')
                    ->options(fn () => Translation::distinct()->pluck('group', 'group')->toArray()),
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
            'index'  => Pages\ListTranslations::route('/'),
            'create' => Pages\CreateTranslation::route('/create'),
            'edit'   => Pages\EditTranslation::route('/{record}/edit'),
        ];
    }
}
