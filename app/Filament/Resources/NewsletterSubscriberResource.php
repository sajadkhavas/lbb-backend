<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages;
use App\Models\NewsletterSubscriber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'بازاریابی';
    protected static ?string $label = 'اشتراک خبرنامه';
    protected static ?string $pluralLabel = 'مشترکین خبرنامه';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('email')
                ->label('ایمیل')
                ->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('name')
                ->label('نام'),
            Forms\Components\Select::make('status')
                ->label('وضعیت')
                ->options([
                    'active' => 'فعال',
                    'unsubscribed' => 'لغو اشتراک',
                    'bounced' => 'برگشت خورده',
                ])->required(),
            Forms\Components\TextInput::make('source')
                ->label('منبع'),
            Forms\Components\DateTimePicker::make('subscribed_at')
                ->label('تاریخ اشتراک'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->searchable()->sortable()->label('ایمیل'),
                Tables\Columns\TextColumn::make('name')->searchable()->label('نام'),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'active' => 'success',
                        'unsubscribed' => 'danger',
                        'bounced' => 'warning',
                        default => 'gray',
                    })
                    
                    ->formatStateUsing(fn($state) => match($state) {
                        'active' => 'فعال',
                        'unsubscribed' => 'لغو اشتراک',
                        'bounced' => 'برگشت خورده',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('source')->label('منبع'),
                Tables\Columns\TextColumn::make('subscribed_at')->dateTime()->sortable()->label('تاریخ اشتراک'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->label('تاریخ ثبت'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'active' => 'فعال',
                        'unsubscribed' => 'لغو اشتراک',
                        'bounced' => 'برگشت خورده',
                    ]),
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

    public static function getRelationManagers(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNewsletterSubscribers::route('/'),
            'create' => Pages\CreateNewsletterSubscriber::route('/create'),
            'edit' => Pages\EditNewsletterSubscriber::route('/{record}/edit'),
        ];
    }
}
