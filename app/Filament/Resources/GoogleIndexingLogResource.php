<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GoogleIndexingLogResource\Pages;
use App\Models\GoogleIndexingLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GoogleIndexingLogResource extends Resource
{
    protected static ?string $model = GoogleIndexingLog::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'سئو';
    protected static ?string $label = 'Google Indexing';
    protected static ?string $pluralLabel = 'Google Indexing';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('url')
                ->label('آدرس صفحه')
                ->required()
                ->url()
                ->columnSpanFull(),
            Forms\Components\Select::make('type')
                ->label('نوع')
                ->options([
                    'URL_UPDATED' => 'ایندکس / آپدیت',
                    'URL_DELETED' => 'حذف از ایندکس',
                ])
                ->required()
                ->default('URL_UPDATED'),
            Forms\Components\Select::make('status')
                ->label('وضعیت')
                ->options([
                    'pending' => 'در انتظار',
                    'success' => 'موفق',
                    'failed'  => 'ناموفق',
                ])
                ->default('pending'),
            Forms\Components\Textarea::make('response')
                ->label('پاسخ API')
                ->columnSpanFull(),
            Forms\Components\DateTimePicker::make('submitted_at')
                ->label('زمان ارسال'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'URL_UPDATED' => 'success',
                        'URL_DELETED' => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn($state) => $state === 'URL_UPDATED' ? 'ایندکس' : 'حذف'),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending' => 'warning',
                        'success' => 'success',
                        'failed'  => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'در انتظار',
                        'success' => 'موفق',
                        'failed'  => 'ناموفق',
                        default   => $state,
                    }),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('زمان ارسال')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('ایجاد شده')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('submit_url')
                    ->label('ارسال URL به Google')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('url')
                            ->label('آدرس صفحه')
                            ->required()
                            ->url(),
                        Forms\Components\Select::make('type')
                            ->label('نوع')
                            ->options([
                                'URL_UPDATED' => 'ایندکس / آپدیت',
                                'URL_DELETED' => 'حذف از ایندکس',
                            ])
                            ->default('URL_UPDATED')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // ثبت در لاگ — اتصال واقعی به Google API روی سرور
                        GoogleIndexingLog::create([
                            'url'          => $data['url'],
                            'type'         => $data['type'],
                            'status'       => 'pending',
                            'submitted_at' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('ثبت شد')
                            ->body('URL در صف ارسال قرار گرفت. پس از تنظیم Google API credentials روی سرور ارسال می‌شود.')
                            ->send();
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(['pending'=>'در انتظار','success'=>'موفق','failed'=>'ناموفق']),
                Tables\Filters\SelectFilter::make('type')
                    ->label('نوع')
                    ->options(['URL_UPDATED'=>'ایندکس','URL_DELETED'=>'حذف']),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGoogleIndexingLogs::route('/'),
            'create' => Pages\CreateGoogleIndexingLog::route('/create'),
            'edit'   => Pages\EditGoogleIndexingLog::route('/{record}/edit'),
        ];
    }
}
