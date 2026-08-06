<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SettingResource extends Resource
{
    protected static ?string $model = \App\Models\Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'تنظیمات سایت';
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('group')
                ->label('گروه')
                ->options([
                    'company' => 'اطلاعات شرکت',
                    'contact' => 'اطلاعات تماس',
                    'social'  => 'شبکه‌های اجتماعی',
                    'seo'     => 'SEO',
                    'general' => 'عمومی',
                ])
                ->required(),
            Forms\Components\TextInput::make('key')
                ->label('کلید')
                ->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('label')
                ->label('عنوان'),
            Forms\Components\Textarea::make('value')
                ->label('مقدار')
                ->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->label('گروه')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'company' => 'شرکت',
                        'contact' => 'تماس',
                        'social'  => 'شبکه اجتماعی',
                        'seo'     => 'SEO',
                        default   => 'عمومی',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->label('عنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('مقدار')
                    ->limit(50)
                    ->searchable(),
            ])
            ->defaultSort('group')
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->label('گروه')
                    ->options([
                        'company' => 'اطلاعات شرکت',
                        'contact' => 'اطلاعات تماس',
                        'social'  => 'شبکه‌های اجتماعی',
                        'seo'     => 'SEO',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('ویرایش'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit'   => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
