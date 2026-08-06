<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebhookResource\Pages;
use App\Models\Webhook;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebhookResource extends Resource
{
    protected static ?string $model = Webhook::class;
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-path-rounded-square';
    protected static ?string $navigationLabel = 'Webhook Manager';
    protected static ?string $modelLabel      = 'Webhook';
    protected static ?string $pluralModelLabel = 'Webhooks';
    protected static ?string $navigationGroup = 'پیشرفته';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('اطلاعات')->schema([
                TextInput::make('name')
                    ->label('نام')
                    ->required(),

                TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->required()
                    ->placeholder('https://example.com/webhook'),

                TextInput::make('secret')
                    ->label('Secret Key')
                    ->password()
                    ->placeholder('برای امنیت بیشتر'),

                Select::make('retry_count')
                    ->label('تعداد تلاش مجدد')
                    ->options([1 => '1', 3 => '3', 5 => '5'])
                    ->default(3),

                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ])->columns(2),

            Section::make('رویدادها')->schema([
                CheckboxList::make('events')
                    ->label('رویدادهایی که این webhook رو فعال می‌کنن')
                    ->options([
                        'product.created'  => 'محصول جدید',
                        'product.updated'  => 'محصول ویرایش شد',
                        'product.deleted'  => 'محصول حذف شد',
                        'rfq.created'      => 'RFQ جدید',
                        'contact.created'  => 'تماس جدید',
                        'blog.published'   => 'بلاگ منتشر شد',
                        'order.created'    => 'سفارش جدید',
                    ])
                    ->columns(2)
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('url')->label('URL')->limit(40),
                Tables\Columns\TextColumn::make('last_status')->label('آخرین وضعیت')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'success' => 'success',
                        'failed'  => 'danger',
                        'error'   => 'warning',
                        default   => 'gray',
                    }),
                Tables\Columns\TextColumn::make('last_triggered_at')->label('آخرین اجرا')
                    ->dateTime('Y/m/d H:i')->default('—'),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                                Tables\Actions\Action::make('test')
                    ->label('تست')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->action(function (Webhook $record) {
                        $result = $record->trigger('test.ping', ['message' => 'Test from ToolMaster']);
                        \Filament\Notifications\Notification::make()
                            ->title($result ? 'موفق ✅' : 'ناموفق ❌')
                            ->color($result ? 'success' : 'danger')
                            ->send();
                    }),
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
            'index'  => Pages\ListWebhooks::route('/'),
            'create' => Pages\CreateWebhook::route('/create'),
            'edit'   => Pages\EditWebhook::route('/{record}/edit'),
        ];
    }
}
