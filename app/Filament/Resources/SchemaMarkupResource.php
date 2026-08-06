<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchemaMarkupResource\Pages;
use App\Models\SchemaMarkup;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SchemaMarkupResource extends Resource
{
    protected static ?string $model = SchemaMarkup::class;
    protected static ?string $navigationIcon  = 'heroicon-o-code-bracket';
    protected static ?string $navigationLabel = 'Schema Manager';
    protected static ?string $modelLabel      = 'Schema';
    protected static ?string $pluralModelLabel = 'Schema ها';
    protected static ?string $navigationGroup = 'سئو';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('اطلاعات')->schema([
                TextInput::make('name')
                    ->label('نام')
                    ->required(),

                Select::make('type')
                    ->label('نوع Schema')
                    ->options([
                        'Organization'   => 'Organization',
                        'Product'        => 'Product',
                        'Article'        => 'Article',
                        'FAQPage'        => 'FAQ Page',
                        'BreadcrumbList' => 'Breadcrumb',
                        'WebSite'        => 'WebSite',
                        'LocalBusiness'  => 'Local Business',
                    ])
                    ->required(),

                Select::make('page_type')
                    ->label('نوع صفحه')
                    ->options([
                        'global'  => 'همه صفحات',
                        'product' => 'محصول',
                        'blog'    => 'بلاگ',
                        'page'    => 'صفحه ثابت',
                    ]),

                TextInput::make('page_slug')
                    ->label('Slug صفحه (اختیاری)')
                    ->placeholder('مثال: gas-generators'),

                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ])->columns(2),

            Section::make('JSON-LD Data')->schema([
                Textarea::make('data')
                    ->label('داده‌های JSON')
                    ->rows(15)
                    ->required()
                    ->helperText('داده‌ها را به صورت JSON وارد کنید')
                    ->formatStateUsing(fn ($state) => is_array($state)
                        ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : $state)
                    ->dehydrateStateUsing(fn ($state) => json_decode($state, true) ?? []),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('type')->label('نوع')->badge()->color('primary'),
                Tables\Columns\TextColumn::make('page_type')->label('صفحه')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('page_slug')->label('Slug')->default('—'),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('بروزرسانی')->dateTime('Y/m/d')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')->label('نوع')
                    ->options([
                        'Organization' => 'Organization',
                        'Product'      => 'Product',
                        'Article'      => 'Article',
                        'FAQPage'      => 'FAQ Page',
                    ]),
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
            'index'  => Pages\ListSchemaMarkups::route('/'),
            'create' => Pages\CreateSchemaMarkup::route('/create'),
            'edit'   => Pages\EditSchemaMarkup::route('/{record}/edit'),
        ];
    }
}
