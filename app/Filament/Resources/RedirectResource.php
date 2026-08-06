<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Redirect;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;
    protected static ?string $navigationIcon   = 'heroicon-o-arrow-uturn-right';
    protected static ?string $navigationLabel  = 'Redirect Manager';
    protected static ?string $navigationGroup  = 'سئو';
    protected static ?int    $navigationSort   = 1;
    protected static ?string $modelLabel       = 'ریدایرکت';
    protected static ?string $pluralModelLabel = 'ریدایرکت‌ها';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                TextInput::make('from_url')
                    ->label('آدرس قدیمی (From)')
                    ->placeholder('/old-page')
                    ->required()
                    ->maxLength(500)
                    ->helperText('آدرس قدیمی که باید redirect بشه'),

                TextInput::make('to_url')
                    ->label('آدرس جدید (To)')
                    ->placeholder('/new-page')
                    ->required()
                    ->maxLength(500)
                    ->helperText('آدرس جدیدی که کاربر به اون هدایت میشه'),
            ]),

            Grid::make(3)->schema([
                Select::make('status_code')
                    ->label('نوع Redirect')
                    ->options([
                        301 => '301 — Permanent (دائمی) — توصیه شده برای SEO',
                        302 => '302 — Temporary (موقت)',
                        307 => '307 — Temporary (با حفظ Method)',
                        308 => '308 — Permanent (با حفظ Method)',
                    ])
                    ->default(301)
                    ->required(),

                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),

                Placeholder::make('hit_count')
                    ->label('تعداد استفاده')
                    ->content(fn ($record) => $record ? number_format($record->hit_count) . ' بار' : '—'),
            ]),

            Textarea::make('note')
                ->label('یادداشت')
                ->placeholder('مثال: صفحه قدیمی محصول X به صفحه جدید منتقل شد')
                ->rows(2)
                ->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('from_url')
                    ->label('From')
                    ->searchable()
                    ->limit(40)
                    ->copyable(),

                Tables\Columns\TextColumn::make('to_url')
                    ->label('To')
                    ->searchable()
                    ->limit(40)
                    ->copyable(),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('نوع')
                    ->badge()
                    ->color(fn ($state) => match((int)$state) {
                        301 => 'success',
                        302 => 'warning',
                        default => 'info',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('hit_count')
                    ->label('استفاده')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state) . '×'),

                Tables\Columns\TextColumn::make('note')
                    ->label('یادداشت')
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخرین تغییر')
                    ->dateTime('Y/m/d')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('وضعیت'),
                SelectFilter::make('status_code')
                    ->label('نوع')
                    ->options([301 => '301', 302 => '302', 307 => '307', 308 => '308']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('hit_count', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit'   => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}
