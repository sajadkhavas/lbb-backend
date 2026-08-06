<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BakeryPostResource\Pages;
use App\Models\BakeryPost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BakeryPostResource extends Resource
{
    protected static ?string $model = BakeryPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationLabel = 'وبلاگ بیکری';

    protected static ?string $modelLabel = 'مقاله';

    protected static ?string $pluralModelLabel = 'وبلاگ بیکری';

    protected static ?string $navigationGroup = 'محتوا';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('مقاله')
                ->schema([
                    Forms\Components\TextInput::make('title')->label('عنوان')->required()->maxLength(260)->columnSpanFull(),
                    Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(180),
                    Forms\Components\TextInput::make('category')->label('دسته')->maxLength(120),
                    Forms\Components\TagsInput::make('tags')->label('برچسب‌ها')->columnSpanFull(),
                    Forms\Components\Textarea::make('excerpt')->label('خلاصه')->rows(3)->columnSpanFull(),
                    Forms\Components\RichEditor::make('content')->label('محتوا')->required()->columnSpanFull(),
                    Forms\Components\TextInput::make('cover_url')->label('آدرس تصویر شاخص')->url()->columnSpanFull(),
                    Forms\Components\TextInput::make('author')->label('نویسنده')->maxLength(160),
                ])->columns(2),
            Forms\Components\Section::make('انتشار')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->label('وضعیت')
                        ->options(['draft' => 'پیش‌نویس', 'published' => 'منتشرشده'])
                        ->default('draft')
                        ->required(),
                    Forms\Components\DateTimePicker::make('published_at')->label('زمان انتشار'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('عنوان')->searchable()->limit(60),
                Tables\Columns\TextColumn::make('category')->label('دسته')->badge()->searchable(),
                Tables\Columns\TextColumn::make('status')->label('وضعیت')->badge(),
                Tables\Columns\TextColumn::make('published_at')->label('انتشار')->dateTime('Y/m/d H:i')->sortable(),
                Tables\Columns\TextColumn::make('view_count')->label('بازدید')->numeric()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('وضعیت')
                    ->options(['draft' => 'پیش‌نویس', 'published' => 'منتشرشده']),
                Tables\Filters\SelectFilter::make('category')
                    ->label('دسته')
                    ->options(fn (): array => BakeryPost::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category', 'category')->all()),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ManageBakeryPosts::route('/')];
    }
}
