<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AbTestResource\Pages;
use App\Models\AbTest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;

class AbTestResource extends Resource
{
    protected static ?string $model = AbTest::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'بازاریابی';
    protected static ?string $label = 'تست A/B';
    protected static ?string $pluralLabel = 'تست‌های A/B';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('اطلاعات تست')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('نام تست')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')
                    ->label('شناسه')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->label('توضیحات')
                    ->rows(3),
                Forms\Components\Select::make('status')
                    ->label('وضعیت')
                    ->options([
                        'draft' => 'پیش‌نویس',
                        'running' => 'در حال اجرا',
                        'paused' => 'متوقف',
                        'completed' => 'تکمیل شده',
                    ])->required()->default('draft'),
                Forms\Components\DateTimePicker::make('started_at')->label('شروع'),
                Forms\Components\DateTimePicker::make('ended_at')->label('پایان'),
            ])->columns(2),

            Forms\Components\Section::make('نسخه‌ها')->schema([
                Forms\Components\Repeater::make('variants')
                    ->label('نسخه‌های تست')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('نام نسخه')->required(),
                        Forms\Components\TextInput::make('slug')->label('شناسه')->required(),
                        Forms\Components\TextInput::make('weight')
                            ->label('درصد ترافیک')
                            ->numeric()->default(50)->suffix('%'),
                        Forms\Components\KeyValue::make('config')->label('تنظیمات'),
                    ])->columns(3)->minItems(2)->maxItems(5),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام تست')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->label('شناسه'),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'draft'     => 'gray',
                        'running'   => 'success',
                        'paused'    => 'warning',
                        'completed' => 'primary',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'draft'     => 'پیش‌نویس',
                        'running'   => 'در حال اجرا',
                        'paused'    => 'متوقف',
                        'completed' => 'تکمیل شده',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('variants_count')
                    ->label('تعداد نسخه')
                    ->counts('variants'),
                Tables\Columns\TextColumn::make('results_count')
                    ->label('نتایج')
                    ->counts('results'),
                Tables\Columns\TextColumn::make('started_at')->dateTime()->label('شروع')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('ساخته شده')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options([
                        'draft' => 'پیش‌نویس',
                        'running' => 'در حال اجرا',
                        'paused' => 'متوقف',
                        'completed' => 'تکمیل شده',
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
            'index' => Pages\ListAbTests::route('/'),
            'create' => Pages\CreateAbTest::route('/create'),
            'edit' => Pages\EditAbTest::route('/{record}/edit'),
        ];
    }
}
