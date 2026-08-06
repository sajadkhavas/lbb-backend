<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms\Components\{Select, Textarea, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;
    protected static ?string $navigationIcon  = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'پیام‌های تماس';
    protected static ?string $modelLabel = 'پیام';
    protected static ?string $pluralModelLabel = 'پیام‌ها';
    protected static ?string $navigationGroup = 'فروش';
    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'unread')->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('نام')->disabled(),
            TextInput::make('email')->label('ایمیل')->disabled(),
            TextInput::make('phone')->label('تلفن')->disabled(),
            TextInput::make('company')->label('شرکت')->disabled(),
            TextInput::make('subject')->label('موضوع')->disabled(),
            Textarea::make('message')->label('پیام')->disabled()->rows(6)->columnSpanFull(),
            Select::make('status')->label('وضعیت')->options([
                'unread'  => 'خوانده نشده',
                'read'    => 'خوانده شده',
                'replied' => 'پاسخ داده شد',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('ایمیل')->copyable(),
                Tables\Columns\TextColumn::make('subject')->label('موضوع')->limit(40),
                Tables\Columns\TextColumn::make('status')->badge()->colors([
                    'danger' => 'unread', 'warning' => 'read', 'success' => 'replied',
                ])->label('وضعیت'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('Y/m/d H:i')->label('ثبت'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'unread' => 'خوانده نشده', 'read' => 'خوانده شده', 'replied' => 'پاسخ داده شد',
                ]),
            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make('table')->fromTable()
                        ->withFilename('پیام‌های-تماس-' . date('Y-m-d'))
                        ->withColumns([
                            Column::make('name')->heading('نام'),
                            Column::make('email')->heading('ایمیل'),
                            Column::make('phone')->heading('تلفن'),
                            Column::make('company')->heading('شرکت'),
                            Column::make('subject')->heading('موضوع'),
                            Column::make('message')->heading('پیام'),
                            Column::make('status')->heading('وضعیت'),
                            Column::make('created_at')->heading('تاریخ'),
                        ]),
                ])->label('خروجی Excel'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()->exports([
                        ExcelExport::make('selected')->fromTable()
                            ->withFilename('پیام‌های-انتخابی-' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('name')->heading('نام'),
                                Column::make('email')->heading('ایمیل'),
                                Column::make('phone')->heading('تلفن'),
                                Column::make('subject')->heading('موضوع'),
                                Column::make('status')->heading('وضعیت'),
                                Column::make('created_at')->heading('تاریخ'),
                            ]),
                    ])->label('خروجی Excel (انتخابی)'),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'view'  => Pages\ViewContact::route('/{record}'),
            'edit'  => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
