<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationGroup = 'محتوا';
    protected static ?string $label = 'سوال متداول';
    protected static ?string $pluralLabel = 'سوالات متداول';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('question')
                ->label('سوال')
                ->required()
                ->columnSpanFull(),
            Forms\Components\RichEditor::make('answer')
                ->label('پاسخ')
                ->required()
                ->columnSpanFull(),
            Forms\Components\TextInput::make('category')
                ->label('دسته‌بندی'),
            Forms\Components\TextInput::make('order')
                ->label('ترتیب')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->label('سوال')
                    ->searchable()
                    ->limit(60),
                Tables\Columns\TextColumn::make('category')
                    ->label('دسته‌بندی')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order')
                    ->label('ترتیب')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
            ])
            ->reorderable('order')
            ->defaultSort('order')
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
            'index'  => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit'   => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
