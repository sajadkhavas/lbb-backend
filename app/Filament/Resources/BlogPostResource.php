<?php
namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use FilamentTiptapEditor\TiptapEditor;
use FilamentTiptapEditor\Enums\TiptapOutput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;
    protected static ?string $navigationIcon  = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'مقالات بلاگ';
    protected static ?string $modelLabel      = 'مقاله';
    protected static ?string $pluralModelLabel = 'مقالات';
    protected static ?string $navigationGroup = 'محتوا';
    protected static ?int $navigationSort     = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('post')->tabs([

                Tabs\Tab::make('محتوا')->schema([
                    TextInput::make('title')
                        ->label('عنوان')
                        ->required()
                        ->maxLength(200)
                        ->live(onBlur: true),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->unique(ignoreRecord: true)
                        ->helperText('خودکار از عنوان ساخته می‌شود'),

                    Textarea::make('excerpt')
                        ->label('چکیده')
                        ->required()
                        ->rows(3)
                        ->maxLength(500),

                    TiptapEditor::make('content')
                        ->label('محتوای کامل')
                        ->required()
                        ->output(TiptapOutput::Html)
                        ->columnSpanFull()
                        ->extraInputAttributes(['style' => 'min-height: 400px']),

                    FileUpload::make('image')
                        ->image()
                        ->label('تصویر شاخص')
                        ->directory('blog')
                        ->imageEditor()
                        ->imageEditorAspectRatios(['16:9', '4:3', '1:1']),

                    Grid::make(3)->schema([
                        TextInput::make('author')->label('نویسنده'),
                        Select::make('category')
                            ->label('دسته')
                            ->options([
                                'راهنمای خرید'   => 'راهنمای خرید',
                                'مقاله تخصصی'    => 'مقاله تخصصی',
                                'اخبار صنعت'     => 'اخبار صنعت',
                            ])
                            ->required(),
                        TextInput::make('read_time')
                            ->label('زمان مطالعه')
                            ->placeholder('۵ دقیقه'),
                    ]),

                    TagsInput::make('tags')->label('تگ‌ها'),
                ]),

                Tabs\Tab::make('انتشار')->schema([
                    Select::make('status')
                        ->options([
                            'draft'     => 'پیش‌نویس',
                            'published' => 'منتشر شده',
                        ])
                        ->default('draft')
                        ->required()
                        ->label('وضعیت'),
                    DateTimePicker::make('published_at')
                        ->label('تاریخ انتشار')
                        ->default(now()),
                ]),

                Tabs\Tab::make('SEO پیشرفته')->schema([
                    Section::make('اطلاعات اصلی SEO')->schema([
                        TextInput::make('meta_title')
                            ->label('Meta Title')
                            ->maxLength(60)
                            ->helperText('حداکثر ۶۰ کاراکتر — عنوانی که در گوگل نشان داده می‌شود')
                            ->live()
                            ->suffix(fn($state) => strlen($state ?? '') . '/60'),

                        Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('حداکثر ۱۶۰ کاراکتر — توضیحی که در نتایج گوگل نشان داده می‌شود')
                            ->live(),

                        TextInput::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->helperText('کلمات کلیدی جدا شده با کاما'),
                    ]),

                    Section::make('پیش‌نمایش گوگل')->schema([
                        Placeholder::make('google_preview')
                            ->label('پیش‌نمایش گوگل')
                            ->content(function ($record) {
                                if (!$record) return new \Illuminate\Support\HtmlString('<p style="color:#888">بعد از ذخیره قابل مشاهده است</p>');
                                $title = e($record->meta_title ?? $record->title ?? 'عنوان صفحه');
                                $desc  = e($record->meta_description ?? $record->excerpt ?? 'توضیحات صفحه');
                                $url   = 'toolmaster.com/blog/' . ($record->slug ?? '');
                                return new \Illuminate\Support\HtmlString("
                                    <div style='font-family:arial;max-width:600px;padding:12px;border:1px solid #ddd;border-radius:8px;background:#fff'>
                                        <div style='color:#1a0dab;font-size:18px;margin-bottom:4px'>{$title}</div>
                                        <div style='color:#006621;font-size:13px;margin-bottom:4px'>{$url}</div>
                                        <div style='color:#545454;font-size:13px'>{$desc}</div>
                                    </div>");
                            }),
                    ]),
                ]),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->label('')->size(40),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('category')->label('دسته')->badge(),
                Tables\Columns\TextColumn::make('author')->label('نویسنده'),
                Tables\Columns\TextColumn::make('status')
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
                Tables\Columns\TextColumn::make('view_count')->label('بازدید')->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('Y/m/d')
                    ->label('انتشار')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['draft' => 'پیش‌نویس', 'published' => 'منتشر شده']),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'راهنمای خرید' => 'راهنمای خرید',
                        'مقاله تخصصی'  => 'مقاله تخصصی',
                        'اخبار صنعت'   => 'اخبار صنعت',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->exports([
                        ExcelExport::make()->withColumns([
                            \pxlrbt\FilamentExcel\Columns\Column::make('title')->heading('عنوان'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('author')->heading('نویسنده'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('category')->heading('دسته'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('status')->heading('وضعیت'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('view_count')->heading('بازدید'),
                            \pxlrbt\FilamentExcel\Columns\Column::make('published_at')->heading('تاریخ انتشار'),
                        ]),
                    ]),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit'   => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
