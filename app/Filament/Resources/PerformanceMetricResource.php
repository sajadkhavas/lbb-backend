<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerformanceMetricResource\Pages;
use App\Models\PerformanceMetric;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class PerformanceMetricResource extends Resource
{
    protected static ?string $model = PerformanceMetric::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'سئو';
    protected static ?string $label = 'متریک عملکرد';
    protected static ?string $pluralLabel = 'متریک‌های عملکرد';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('page_url')->label('آدرس صفحه')->required(),
            Forms\Components\TextInput::make('lcp')->label('LCP (ms)')->numeric(),
            Forms\Components\TextInput::make('fid')->label('FID (ms)')->numeric(),
            Forms\Components\TextInput::make('cls')->label('CLS')->numeric(),
            Forms\Components\TextInput::make('fcp')->label('FCP (ms)')->numeric(),
            Forms\Components\TextInput::make('ttfb')->label('TTFB (ms)')->numeric(),
            Forms\Components\Select::make('device_type')
                ->label('نوع دستگاه')
                ->options(['mobile' => 'موبایل', 'desktop' => 'دسکتاپ']),
            Forms\Components\TextInput::make('browser')->label('مرورگر'),
            Forms\Components\TextInput::make('country')->label('کشور'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page_url')->searchable()->label('صفحه')->limit(40),
                Tables\Columns\TextColumn::make('lcp')
                    ->label('LCP')
                    ->formatStateUsing(fn($state) => $state ? round($state).' ms' : '-')
                    ->color(fn($state) => $state < 2500 ? 'success' : ($state < 4000 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('fid')
                    ->label('FID')
                    ->formatStateUsing(fn($state) => $state ? round($state).' ms' : '-')
                    ->color(fn($state) => $state < 100 ? 'success' : ($state < 300 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('cls')
                    ->label('CLS')
                    ->formatStateUsing(fn($state) => $state ? round($state, 3) : '-')
                    ->color(fn($state) => $state < 0.1 ? 'success' : ($state < 0.25 ? 'warning' : 'danger')),
                Tables\Columns\TextColumn::make('fcp')->label('FCP')->formatStateUsing(fn($state) => $state ? round($state).' ms' : '-'),
                Tables\Columns\TextColumn::make('ttfb')->label('TTFB')->formatStateUsing(fn($state) => $state ? round($state).' ms' : '-'),
                Tables\Columns\BadgeColumn::make('device_type')
                    ->label('دستگاه')
                    ->formatStateUsing(fn($state) => $state === 'mobile' ? 'موبایل' : 'دسکتاپ'),
                Tables\Columns\TextColumn::make('country')->label('کشور'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('تاریخ'),
            ])
            ->filters([
                SelectFilter::make('device_type')
                    ->label('نوع دستگاه')
                    ->options(['mobile' => 'موبایل', 'desktop' => 'دسکتاپ']),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelationManagers(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerformanceMetrics::route('/'),
        ];
    }
}
