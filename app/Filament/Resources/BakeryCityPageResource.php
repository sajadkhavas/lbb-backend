<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BakeryCityPageResource\Pages;
use App\Models\BakeryCityPage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BakeryCityPageResource extends Resource
{
    protected static ?string $model = BakeryCityPage::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'صفحات شهری';

    protected static ?string $modelLabel = 'صفحه شهری';

    protected static ?string $pluralModelLabel = 'صفحات شهری';

    protected static ?string $navigationGroup = 'محتوا';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('city')->label('شهر')->required()->maxLength(100),
            Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(160),
            Forms\Components\Toggle::make('is_active')->label('فعال')->default(true),
            Forms\Components\TextInput::make('title')->label('عنوان')->required()->maxLength(220)->columnSpanFull(),
            Forms\Components\Textarea::make('description')->label('خلاصه')->rows(3)->columnSpanFull(),
            Forms\Components\RichEditor::make('content')->label('محتوا')->columnSpanFull(),
            Forms\Components\TextInput::make('meta_title')->label('عنوان سئو')->maxLength(220),
            Forms\Components\Textarea::make('meta_description')->label('توضیح سئو')->rows(3),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('city')->label('شهر')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->label('عنوان')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->copyable()->searchable(),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('آخرین تغییر')->dateTime('Y/m/d H:i')->sortable(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->label('فعال')])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort('city');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageBakeryCityPages::route('/')];
    }
}
