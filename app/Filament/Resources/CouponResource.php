<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'بازاریابی';
    protected static ?string $label = 'کوپن تخفیف';
    protected static ?string $pluralLabel = 'کوپن‌های تخفیف';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('اطلاعات کوپن')->schema([
                Forms\Components\TextInput::make('code')
                    ->label('کد کوپن')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->rule("uppercase")
                    ->columnSpan(1),
                Forms\Components\TextInput::make('description')
                    ->label('توضیحات')
                    ->columnSpan(1),
                Forms\Components\Select::make('type')
                    ->label('نوع تخفیف')
                    ->options(['percentage' => 'درصدی', 'fixed' => 'مبلغ ثابت'])
                    ->required()
                    ->columnSpan(1),
                Forms\Components\TextInput::make('value')
                    ->label('مقدار')
                    ->numeric()
                    ->required()
                    ->columnSpan(1),
            ])->columns(2),

            Forms\Components\Section::make('محدودیت‌ها')->schema([
                Forms\Components\TextInput::make('min_order_amount')
                    ->label('حداقل مبلغ سفارش')
                    ->numeric(),
                Forms\Components\TextInput::make('max_discount_amount')
                    ->label('حداکثر مبلغ تخفیف')
                    ->numeric(),
                Forms\Components\TextInput::make('usage_limit')
                    ->label('محدودیت استفاده')
                    ->numeric(),
                Forms\Components\Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('شروع از'),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->label('انقضا در'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('کد')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->color(fn ($state) => $state === 'percentage' ? 'success' : 'info')
                    ->formatStateUsing(fn($state) => $state === 'percentage' ? 'درصدی' : 'ثابت'),
                Tables\Columns\TextColumn::make('value')
                    ->label('مقدار')
                    ->sortable(),
                Tables\Columns\TextColumn::make('used_count')
                    ->label('استفاده شده')
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
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
