<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IpBlacklistResource\Pages;
use App\Models\IpBlacklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IpBlacklistResource extends Resource
{
    protected static ?string $model = IpBlacklist::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-exclamation';
    protected static ?string $navigationGroup = 'امنیت';
    protected static ?string $label = 'IP مسدود';
    protected static ?string $pluralLabel = 'IP های مسدود';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('ip_address')
                ->label('آدرس IP')
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('192.168.1.1')
                ->rules(['ip'])
                ->helperText('فقط آدرس IPv4 یا IPv6 معتبر'),
            Forms\Components\TextInput::make('reason')
                ->label('دلیل مسدودسازی'),
            Forms\Components\Toggle::make('is_active')
                ->label('فعال')
                ->default(true),
            Forms\Components\DateTimePicker::make('blocked_at')
                ->label('زمان مسدودسازی')
                ->default(now()),
            Forms\Components\DateTimePicker::make('expires_at')
                ->label('انقضا (اختیاری)')
                ->helperText('خالی = دائمی'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('آدرس IP')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('دلیل')
                    ->limit(40),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),
                Tables\Columns\TextColumn::make('blocked_at')
                    ->label('مسدود شده')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('انقضا')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('دائمی'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('block_current_ip')
                    ->label('مسدود کردن IP فعلی')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        $ip = request()->ip();
                        IpBlacklist::firstOrCreate(
                            ['ip_address' => $ip],
                            ['reason' => 'مسدود شده از پنل', 'blocked_at' => now(), 'is_active' => true]
                        );
                    }),
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
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIpBlacklists::route('/'),
            'create' => Pages\CreateIpBlacklist::route('/create'),
            'edit'   => Pages\EditIpBlacklist::route('/{record}/edit'),
        ];
    }
}
