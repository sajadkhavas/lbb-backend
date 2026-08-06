<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms\Components\{Textarea, TextInput, Toggle};
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SubcategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'subcategories';
    protected static ?string $title = 'زیرمجموعه‌ها';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('نام فارسی')->required(),
            TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true),
            TextInput::make('full_name_en')->label('نام کامل انگلیسی'),
            Textarea::make('description')->label('توضیح')->rows(2),
            TextInput::make('sort_order')->numeric()->default(0)->label('ترتیب'),
            Toggle::make('is_active')->default(true)->label('فعال'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام'),
                Tables\Columns\TextColumn::make('slug')->copyable(),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->reorderable('sort_order');
    }
}
