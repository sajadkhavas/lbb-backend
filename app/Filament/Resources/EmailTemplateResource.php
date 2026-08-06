<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TagsInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;
    protected static ?string $navigationIcon  = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Email Templates';
    protected static ?string $modelLabel      = 'قالب ایمیل';
    protected static ?string $pluralModelLabel = 'قالب‌های ایمیل';
    protected static ?string $navigationGroup = 'پیشرفته';
    protected static ?int    $navigationSort  = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('اطلاعات')->schema([
                TextInput::make('name')
                    ->label('نام قالب')
                    ->required(),

                TextInput::make('slug')
                    ->label('Slug (کد یکتا)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('مثال: rfq-confirmation'),

                TextInput::make('subject')
                    ->label('موضوع ایمیل')
                    ->required()
                    ->columnSpanFull(),

                TagsInput::make('variables')
                    ->label('متغیرها')
                    ->helperText('متغیرهایی که در قالب استفاده میشن. مثال: name, email, order_id')
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ])->columns(2),

            Section::make('محتوای ایمیل')->schema([
                RichEditor::make('body')
                    ->label('متن ایمیل')
                    ->helperText('برای استفاده از متغیرها: {{name}}, {{email}}, {{order_id}}')
                    ->required()
                    ->toolbarButtons([
                        'bold', 'italic', 'underline',
                        'bulletList', 'orderedList',
                        'link', 'h2', 'h3',
                        'blockquote',
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->badge()->color('gray'),
                Tables\Columns\TextColumn::make('subject')->label('موضوع')->limit(40),
                Tables\Columns\IconColumn::make('is_active')->label('فعال')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('بروزرسانی')->dateTime('Y/m/d')->sortable(),
            ])
            ->defaultSort('id', 'desc')
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit'   => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
