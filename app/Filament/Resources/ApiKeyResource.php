<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApiKeyResource\Pages;
use App\Models\ApiKey;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ApiKeyResource extends Resource
{
    protected static ?string $model = ApiKey::class;
    protected static ?string $navigationIcon  = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'API Keys';
    protected static ?string $modelLabel      = 'API Key';
    protected static ?string $pluralModelLabel = 'API Keys';
    protected static ?string $navigationGroup = 'پیشرفته';
    protected static ?int    $navigationSort  = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('اطلاعات')->schema([
                TextInput::make('name')
                    ->label('نام')
                    ->required(),

                TextInput::make('key')
                    ->label('API Key')
                    ->default('tk_' . Str::random(40))
                    ->readOnly()
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('copy')
                            ->icon('heroicon-o-clipboard')
                            ->action(fn () => null)
                    ),

                TextInput::make('rate_limit')
                    ->label('محدودیت در دقیقه')
                    ->numeric()
                    ->default(60),

                DateTimePicker::make('expires_at')
                    ->label('تاریخ انقضا (اختیاری)')
                    ->nullable(),

                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ])->columns(2),

            Section::make('دسترسی‌ها')->schema([
                CheckboxList::make('permissions')
                    ->label('endpoints مجاز')
                    ->options([
                        'products'   => 'محصولات',
                        'categories' => 'دسته‌بندی‌ها',
                        'brands'     => 'برندها',
                        'blog'       => 'بلاگ',
                        'search'     => 'جستجو',
                        'settings'   => 'تنظیمات',
                        'rfq'        => 'RFQ',
                        'contact'    => 'تماس',
                        'seo'        => 'SEO',
                    ])
                    ->columns(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('key')->label('Key')
                    ->formatStateUsing(fn ($state) => substr($state, 0, 10) . '...')
                    ->copyable()
                    ->copyMessage('کپی شد!'),
                Tables\Columns\TextColumn::make('rate_limit')->label('Rate Limit')->suffix('/min'),
                Tables\Columns\TextColumn::make('usage_count')->label('استفاده')->sortable(),
                Tables\Columns\TextColumn::make('last_used_at')->label('آخرین استفاده')
                    ->dateTime('Y/m/d H:i')->default('—'),
                Tables\Columns\TextColumn::make('expires_at')->label('انقضا')
                    ->dateTime('Y/m/d')->default('بدون انقضا'),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
            ])
            ->defaultSort('id', 'desc')
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
            'index'  => Pages\ListApiKeys::route('/'),
            'create' => Pages\CreateApiKey::route('/create'),
            'edit'   => Pages\EditApiKey::route('/{record}/edit'),
        ];
    }
}
