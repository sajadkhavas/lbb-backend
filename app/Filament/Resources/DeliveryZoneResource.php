<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryZoneResource\Pages;
use App\Models\DeliveryZone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DeliveryZoneResource extends Resource
{
    protected static ?string $model = DeliveryZone::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'مناطق ارسال';

    protected static ?string $modelLabel = 'منطقه ارسال';

    protected static ?string $pluralModelLabel = 'مناطق ارسال';

    protected static ?string $navigationGroup = 'فروشگاه وینیمی';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('محدوده')
                ->schema([
                    Forms\Components\TextInput::make('name')->label('نام')->required()->maxLength(140),
                    Forms\Components\TextInput::make('province')->label('استان')->maxLength(100),
                    Forms\Components\TextInput::make('city')->label('شهر')->maxLength(100),
                    Forms\Components\TextInput::make('priority')->label('اولویت')->numeric()->default(100)->required(),
                    Forms\Components\Toggle::make('is_active')->label('فعال')->default(true),
                ])->columns(3),
            Forms\Components\Section::make('روش‌ها و هزینه‌ها')
                ->schema([
                    Forms\Components\Toggle::make('standard_enabled')->label('ارسال عادی'),
                    Forms\Components\TextInput::make('standard_fee_toman')->label('هزینه عادی')->numeric()->default(0)->suffix(' تومان'),
                    Forms\Components\Toggle::make('chilled_enabled')->label('ارسال سرد'),
                    Forms\Components\TextInput::make('chilled_fee_toman')->label('هزینه سرد')->numeric()->default(0)->suffix(' تومان'),
                    Forms\Components\Toggle::make('pickup_enabled')->label('تحویل حضوری'),
                    Forms\Components\TextInput::make('pickup_fee_toman')->label('هزینه حضوری')->numeric()->default(0)->suffix(' تومان'),
                    Forms\Components\TextInput::make('packaging_fee_toman')->label('هزینه بسته‌بندی')->numeric()->default(0)->suffix(' تومان'),
                    Forms\Components\TextInput::make('free_delivery_threshold_toman')->label('ارسال رایگان از مبلغ')->numeric()->nullable()->suffix(' تومان'),
                ])->columns(4),
            Forms\Components\Section::make('محدودیت عملیات')
                ->schema([
                    Forms\Components\TextInput::make('minimum_order_toman')->label('حداقل سفارش')->numeric()->nullable()->suffix(' تومان'),
                    Forms\Components\TextInput::make('preparation_min_days')->label('حداقل روز آماده‌سازی')->numeric()->default(0),
                    Forms\Components\TextInput::make('preparation_max_days')->label('حداکثر روز آماده‌سازی')->numeric()->default(0),
                    Forms\Components\TextInput::make('daily_order_limit')->label('ظرفیت روزانه')->numeric()->nullable(),
                ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('province')->label('استان')->searchable()->placeholder('همه'),
                Tables\Columns\TextColumn::make('city')->label('شهر')->searchable()->placeholder('همه'),
                Tables\Columns\IconColumn::make('standard_enabled')->label('عادی')->boolean(),
                Tables\Columns\IconColumn::make('chilled_enabled')->label('سرد')->boolean(),
                Tables\Columns\IconColumn::make('pickup_enabled')->label('حضوری')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
                Tables\Columns\TextColumn::make('priority')->label('اولویت')->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('فعال'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort('priority');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDeliveryZones::route('/'),
        ];
    }
}
