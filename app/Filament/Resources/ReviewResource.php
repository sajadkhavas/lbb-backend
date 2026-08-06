<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationGroup = 'بازاریابی';
    protected static ?string $label = 'نظر';
    protected static ?string $pluralLabel = 'نظرات';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('reviewer_name')
                ->label('نام')
                ->required(),
            Forms\Components\TextInput::make('reviewer_email')
                ->label('ایمیل')
                ->email(),
            Forms\Components\Select::make('rating')
                ->label('امتیاز')
                ->options([1=>'⭐',2=>'⭐⭐',3=>'⭐⭐⭐',4=>'⭐⭐⭐⭐',5=>'⭐⭐⭐⭐⭐'])
                ->required(),
            Forms\Components\Select::make('status')
                ->label('وضعیت')
                ->options(['pending'=>'در انتظار','approved'=>'تایید شده','rejected'=>'رد شده'])
                ->required(),
            Forms\Components\TextInput::make('title')
                ->label('عنوان')
                ->columnSpanFull(),
            Forms\Components\Textarea::make('body')
                ->label('متن نظر')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reviewer_name')
                    ->label('نام')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reviewable_type')
                    ->label('نوع')
                    ->formatStateUsing(fn($state) => class_basename($state)),
                Tables\Columns\TextColumn::make('rating')
                    ->label('امتیاز')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->limit(40),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending'  => 'در انتظار',
                        'approved' => 'تایید شده',
                        'rejected' => 'رد شده',
                        default    => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(['pending'=>'در انتظار','approved'=>'تایید شده','rejected'=>'رد شده']),
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
            'index'  => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'edit'   => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
