<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeoMetaResource\Pages;
use App\Models\SeoMeta;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SeoMetaResource extends Resource
{
    protected static ?string $model = SeoMeta::class;
    protected static ?string $navigationIcon   = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationLabel  = 'SEO Manager';
    protected static ?string $navigationGroup  = 'سئو';
    protected static ?int    $navigationSort   = 2;
    protected static ?string $modelLabel       = 'تنظیمات SEO';
    protected static ?string $pluralModelLabel = 'تنظیمات SEO';

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('شناسه صفحه')->schema([
                Grid::make(2)->schema([
                    TextInput::make('page_key')
                        ->label('کلید صفحه (Page Key)')
                        ->placeholder('مثال: home  یا  /blog/my-post')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('یک کلید یکتا برای این صفحه — همین کلید رو توی React با API فراخوانی کن'),

                    TextInput::make('page_label')
                        ->label('نام نمایشی')
                        ->placeholder('مثال: صفحه اصلی')
                        ->required(),
                ]),
                Toggle::make('is_active')->label('فعال')->default(true)->inline(false),
            ]),

            Section::make('Meta Tags — تگ‌های اصلی SEO')->schema([
                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->placeholder('عنوان صفحه در گوگل (۵۰–۶۰ کاراکتر توصیه میشه)')
                    ->maxLength(120)
                    ->live()
                    ->suffix(fn ($state) => strlen($state ?? '') . ' کاراکتر'),

                Textarea::make('meta_description')
                    ->label('Meta Description')
                    ->placeholder('توضیح کوتاه صفحه در نتایج گوگل (۱۵۰–۱۶۰ کاراکتر)')
                    ->rows(3)
                    ->maxLength(500)
                    ->live()
                    ->hint(fn ($state) => strlen($state ?? '') . ' / 160 کاراکتر'),

                TextInput::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->placeholder('کلمه۱، کلمه۲، کلمه۳')
                    ->maxLength(500)
                    ->helperText('جدا شده با ویرگول — در گوگل تأثیر کمی دارن ولی برای سایر موتورهای جستجو مفیده'),

                Grid::make(2)->schema([
                    TextInput::make('canonical_url')
                        ->label('Canonical URL')
                        ->placeholder('https://example.com/page')
                        ->url()
                        ->helperText('اگه صفحه duplicate داری اینجا آدرس اصلی رو بذار'),

                    Select::make('robots')
                        ->label('Robots')
                        ->options([
                            'index,follow'    => 'index, follow — ایندکس + دنبال لینک (پیش‌فرض)',
                            'noindex,nofollow'=> 'noindex, nofollow — ایندکس نکن',
                            'noindex,follow'  => 'noindex, follow — ایندکس نکن ولی لینک دنبال کن',
                            'index,nofollow'  => 'index, nofollow — ایندکس کن ولی لینک دنبال نکن',
                        ])
                        ->default('index,follow')
                        ->required(),
                ]),
            ]),

            Section::make('Open Graph — اشتراک‌گذاری در شبکه‌های اجتماعی')->schema([
                Grid::make(2)->schema([
                    TextInput::make('og_title')
                        ->label('OG Title')
                        ->placeholder('عنوان نمایش در تلگرام، توییتر، لینکدین...')
                        ->maxLength(120)
                        ->helperText('اگه خالی باشه از Meta Title استفاده میشه'),

                    TextInput::make('og_image')
                        ->label('OG Image URL')
                        ->placeholder('https://example.com/og-image.jpg')
                        ->url()
                        ->helperText('اندازه پیشنهادی: ۱۲۰۰×۶۳۰ پیکسل'),
                ]),

                Textarea::make('og_description')
                    ->label('OG Description')
                    ->placeholder('توضیح نمایش در شبکه‌های اجتماعی')
                    ->rows(2)
                    ->maxLength(500)
                    ->helperText('اگه خالی باشه از Meta Description استفاده میشه'),
            ]),

            Section::make('Schema.org — داده‌های ساختاریافته (JSON-LD)')->schema([
                Placeholder::make('schema_help')
                    ->label('')
                    ->content('JSON-LD رو اینجا وارد کن. گوگل این داده‌ها رو برای Rich Snippets (ستاره‌ها، قیمت، سوالات متداول) استفاده می‌کنه.'),

                Textarea::make('schema_json')
                    ->label('Schema JSON-LD')
                    ->placeholder('{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "نام محصول",
  "description": "توضیحات",
  "offers": {
    "@type": "Offer",
    "price": "100"
  }
}')
                    ->rows(10)
                    ->helperText('باید JSON معتبر باشه')
                    ->dehydrateStateUsing(function ($state) {
                        if (empty($state)) return null;
                        $decoded = json_decode($state, true);
                        return $decoded ?? null;
                    })
                    ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ''),
            ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page_label')
                    ->label('صفحه')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('page_key')
                    ->label('کلید')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('meta_title')
                    ->label('Meta Title')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('robots')
                    ->label('Robots')
                    ->badge()
                    ->color(fn ($state) => str_contains($state, 'noindex') ? 'danger' : 'success'),

                Tables\Columns\IconColumn::make('og_image')
                    ->label('OG Image')
                    ->boolean()
                    ->trueIcon('heroicon-o-photo')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\IconColumn::make('schema_json')
                    ->label('Schema')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعال')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخرین تغییر')
                    ->dateTime('Y/m/d')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('وضعیت'),
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
            'index'  => Pages\ListSeoMeta::route('/'),
            'create' => Pages\CreateSeoMeta::route('/create'),
            'edit'   => Pages\EditSeoMeta::route('/{record}/edit'),
        ];
    }
}
