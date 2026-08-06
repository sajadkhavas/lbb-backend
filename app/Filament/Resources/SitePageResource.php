<?php
namespace App\Filament\Resources;

use App\Filament\Resources\SitePageResource\Pages;
use App\Models\SitePage;
use FilamentTiptapEditor\TiptapEditor;
use FilamentTiptapEditor\Enums\TiptapOutput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SitePageResource extends Resource
{
    protected static ?string $model = SitePage::class;
    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'صفحات سایت';
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?int $navigationSort     = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('اطلاعات اصلی')->schema([
                TextInput::make('slug')
                    ->label('شناسه صفحه (slug)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(100)
                    ->helperText('مثال: about، projects، faq'),

                TextInput::make('title')
                    ->label('عنوان صفحه')
                    ->required()
                    ->maxLength(255),

                Select::make('status')
                    ->label('وضعیت')
                    ->options([
                        'published' => 'منتشر شده',
                        'draft'     => 'پیش‌نویس',
                    ])
                    ->default('published')
                    ->required(),
            ])->columns(2),

            Section::make('محتوای Hero')->schema([
                TextInput::make('hero_title')
                    ->label('تیتر اصلی')
                    ->maxLength(255),
                Textarea::make('hero_description')
                    ->label('توضیحات Hero')
                    ->rows(3),
            ]),

            Section::make('محتوای صفحه')->schema([
                TiptapEditor::make('content')
                    ->label('محتوا')
                    ->output(TiptapOutput::Html)
                    ->columnSpanFull()
                    ->extraInputAttributes(['style' => 'min-height: 500px']),
            ]),

            Section::make('SEO پیشرفته')->schema([
                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->maxLength(60)
                    ->helperText('حداکثر ۶۰ کاراکتر')
                    ->live()
                    ->suffix(fn($state) => strlen($state ?? '') . '/60'),

                Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->maxLength(160)
                    ->rows(3)
                    ->helperText('حداکثر ۱۶۰ کاراکتر')
                    ->live(),

                TextInput::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->helperText('کلمات کلیدی جدا شده با کاما'),

                Placeholder::make('google_preview')
                    ->label('پیش‌نمایش گوگل')
                    ->content(function ($record) {
                        if (!$record) return 'بعد از ذخیره قابل مشاهده است';
                        $title = $record->meta_title ?? $record->title ?? 'عنوان صفحه';
                        $desc  = $record->meta_description ?? 'توضیحات صفحه';
                        $url   = 'toolmaster.com/' . ($record->slug ?? '');
                        return "<div style='font-family:arial;max-width:600px;padding:12px;border:1px solid #ddd;border-radius:8px;background:#fff'>
                            <div style='color:#1a0dab;font-size:18px;margin-bottom:4px'>{$title}</div>
                            <div style='color:#006621;font-size:13px;margin-bottom:4px'>{$url}</div>
                            <div style='color:#545454;font-size:13px'>{$desc}</div>
                        </div>";
                    })->extraAttributes(['class' => 'col-span-full']),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn(string $state): string => match($state) {
                        'published' => 'success',
                        'draft'     => 'warning',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match($state) {
                        'published' => 'منتشر شده',
                        'draft'     => 'پیش‌نویس',
                        default     => $state,
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخرین ویرایش')
                    ->dateTime('Y/m/d')
                    ->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSitePages::route('/'),
            'create' => Pages\CreateSitePage::route('/create'),
            'edit'   => Pages\EditSitePage::route('/{record}/edit'),
        ];
    }
}
