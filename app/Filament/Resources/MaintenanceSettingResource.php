<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceSettingResource\Pages;
use App\Models\MaintenanceSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class MaintenanceSettingResource extends Resource
{
    protected static ?string $model = MaintenanceSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'امنیت';
    protected static ?string $label = 'حالت تعمیر';
    protected static ?string $pluralLabel = 'حالت تعمیر';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('وضعیت')->schema([
                Forms\Components\Toggle::make('is_enabled')
                    ->label('فعال کردن حالت تعمیر')
                    ->helperText('با فعال کردن این گزینه، سایت برای بازدیدکنندگان غیرقابل دسترس میشه')
                    ->reactive(),
            ]),
            Forms\Components\Section::make('محتوای صفحه')->schema([
                Forms\Components\TextInput::make('title')
                    ->label('عنوان')
                    ->required()
                    ->default('سایت در حال بروزرسانی است'),
                Forms\Components\Textarea::make('message')
                    ->label('پیام')
                    ->rows(3)
                    ->default('به زودی برمیگردیم. با تشکر از صبر شما.'),
                Forms\Components\DateTimePicker::make('scheduled_end')
                    ->label('زمان پایان تعمیرات'),
            ]),
            Forms\Components\Section::make('دسترسی')->schema([
                Forms\Components\Textarea::make('allowed_ips')
                    ->label('IPهای مجاز')
                    ->helperText('آدرس‌های IP که دسترسی دارند، با کاما جدا کنید')
                    ->placeholder('192.168.1.1, 10.0.0.1')
                    ->rows(2),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('وضعیت')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->trueIcon('heroicon-o-wrench-screwdriver')
                    ->falseIcon('heroicon-o-check-circle'),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان'),
                Tables\Columns\TextColumn::make('scheduled_end')
                    ->label('پایان تعمیرات')
                    ->dateTime()
                    ->placeholder('نامشخص'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخرین تغییر')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('toggle_maintenance')
                    ->label(fn() => MaintenanceSetting::current()->is_enabled ? 'غیرفعال کردن' : 'فعال کردن')
                    ->icon(fn() => MaintenanceSetting::current()->is_enabled ? 'heroicon-o-check-circle' : 'heroicon-o-wrench-screwdriver')
                    ->color(fn() => MaintenanceSetting::current()->is_enabled ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        $setting = MaintenanceSetting::current();
                        $setting->update(['is_enabled' => !$setting->is_enabled]);
                        Notification::make()
                            ->success()
                            ->title($setting->is_enabled ? 'حالت تعمیر فعال شد' : 'حالت تعمیر غیرفعال شد')
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMaintenanceSettings::route('/'),
            'create' => Pages\CreateMaintenanceSetting::route('/create'),
            'edit'   => Pages\EditMaintenanceSetting::route('/{record}/edit'),
        ];
    }
}
