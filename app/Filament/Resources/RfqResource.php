<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RfqResource\Pages;
use App\Models\RfqRequest;
use Filament\Forms\Components\{Repeater, Select, Textarea, TextInput};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

class RfqResource extends Resource
{
    protected static ?string $model = RfqRequest::class;
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'استعلام‌های قیمت';
    protected static ?string $modelLabel = 'استعلام';
    protected static ?string $pluralModelLabel = 'استعلام‌ها';
    protected static ?string $navigationGroup = 'فروش';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pending')->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('reference_number')->label('شماره پیگیری')->disabled(),
            TextInput::make('name')->label('نام')->required(),
            TextInput::make('email')->label('ایمیل')->required()->email(),
            TextInput::make('phone')->label('تلفن'),
            TextInput::make('company')->label('شرکت'),
            TextInput::make('position')->label('سمت'),
            Select::make('status')->options([
                'pending'    => 'در انتظار',
                'processing' => 'در حال پردازش',
                'quoted'     => 'استعلام داده شد',
                'closed'     => 'بسته شد',
            ])->required()->label('وضعیت'),
            Textarea::make('notes')->label('یادداشت‌های مشتری')->rows(3)->columnSpanFull(),
            Repeater::make('items')->relationship()->label('اقلام درخواستی')->schema([
                TextInput::make('product_name')->label('نام محصول')->required(),
                TextInput::make('product_model')->label('مدل'),
                TextInput::make('quantity')->numeric()->required()->label('تعداد'),
                Textarea::make('notes')->rows(2)->label('یادداشت'),
            ])->columnSpanFull()->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')->label('شماره پیگیری')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('ایمیل')->copyable(),
                Tables\Columns\TextColumn::make('company')->label('شرکت'),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('اقلام'),
                Tables\Columns\TextColumn::make('status')->badge()->colors([
                    'gray' => 'closed', 'warning' => 'pending', 'info' => 'processing', 'success' => 'quoted',
                ])->label('وضعیت'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('Y/m/d H:i')->label('ثبت')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'در انتظار', 'processing' => 'پردازش', 'quoted' => 'استعلام داده شد', 'closed' => 'بسته شد',
                ]),
            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make('table')->fromTable()
                        ->withFilename('استعلام‌ها-' . date('Y-m-d'))
                        ->withColumns([
                            Column::make('reference_number')->heading('شماره پیگیری'),
                            Column::make('name')->heading('نام'),
                            Column::make('email')->heading('ایمیل'),
                            Column::make('phone')->heading('تلفن'),
                            Column::make('company')->heading('شرکت'),
                            Column::make('status')->heading('وضعیت'),
                            Column::make('created_at')->heading('تاریخ ثبت'),
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
                            ->withFilename('استعلام‌های-انتخابی-' . date('Y-m-d'))
                            ->withColumns([
                                Column::make('reference_number')->heading('شماره پیگیری'),
                                Column::make('name')->heading('نام'),
                                Column::make('email')->heading('ایمیل'),
                                Column::make('phone')->heading('تلفن'),
                                Column::make('company')->heading('شرکت'),
                                Column::make('status')->heading('وضعیت'),
                                Column::make('created_at')->heading('تاریخ ثبت'),
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
            'index'  => Pages\ListRfqs::route('/'),
            'view'   => Pages\ViewRfq::route('/{record}'),
            'edit'   => Pages\EditRfq::route('/{record}/edit'),
        ];
    }
}
