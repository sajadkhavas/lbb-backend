<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeoScanResource\Pages;
use App\Models\SeoScan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class SeoScanResource extends Resource
{
    protected static ?string $model = SeoScan::class;
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationGroup = 'سئو';
    protected static ?string $label = 'اسکن SEO';
    protected static ?string $pluralLabel = 'اسکن‌های SEO';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('url')
                ->label('آدرس صفحه')
                ->required()
                ->url()
                ->columnSpanFull(),
            Forms\Components\TextInput::make('title')
                ->label('عنوان صفحه'),
            Forms\Components\TextInput::make('title_length')
                ->label('طول عنوان')
                ->numeric(),
            Forms\Components\Textarea::make('meta_description')
                ->label('توضیحات متا')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('meta_description_length')
                ->label('طول توضیحات')
                ->numeric(),
            Forms\Components\Toggle::make('has_h1')->label('دارای H1'),
            Forms\Components\Toggle::make('has_canonical')->label('دارای Canonical'),
            Forms\Components\Toggle::make('has_og_tags')->label('دارای OG Tags'),
            Forms\Components\Toggle::make('has_schema')->label('دارای Schema'),
            Forms\Components\TextInput::make('images_without_alt')
                ->label('تصاویر بدون Alt')
                ->numeric(),
            Forms\Components\TextInput::make('word_count')
                ->label('تعداد کلمات')
                ->numeric(),
            Forms\Components\TextInput::make('score')
                ->label('امتیاز (0-100)')
                ->numeric(),
            Forms\Components\KeyValue::make('issues')
                ->label('مشکلات')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->limit(40)
                    ->url(fn($record) => $record->url, true),
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->limit(30)
                    ->searchable(),
                Tables\Columns\TextColumn::make('score')
                    ->label('امتیاز')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match(true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default      => 'danger',
                    }),
                Tables\Columns\IconColumn::make('has_h1')
                    ->label('H1')
                    ->boolean(),
                Tables\Columns\IconColumn::make('has_og_tags')
                    ->label('OG Tags')
                    ->boolean(),
                Tables\Columns\IconColumn::make('has_schema')
                    ->label('Schema')
                    ->boolean(),
                Tables\Columns\TextColumn::make('word_count')
                    ->label('کلمات')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('زمان اسکن')
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('scan_url')
                    ->label('اسکن URL جدید')
                    ->icon('heroicon-o-magnifying-glass')
                    ->form([
                        Forms\Components\TextInput::make('url')
                            ->label('آدرس صفحه')
                            ->required()
                            ->url()
                            ->rules(['url', 'regex:/^https?:\/\//'])
                            ->helperText('فقط آدرس‌های عمومی با http یا https مجاز هستند'),
                    ])
                    ->action(function (array $data) {
                        // SSRF protection — block private IPs
                        $host = parse_url($data['url'], PHP_URL_HOST);
                        $ip = gethostbyname($host);
                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('آدرس مجاز نیست')
                                ->body('آدرس‌های داخلی شبکه مجاز نیستند')
                                ->send();
                            return;
                        }

                        try {
                            $response = Http::timeout(15)
                                ->withHeaders(['User-Agent' => 'ToolMaster SEO Scanner/1.0'])
                                ->get($data['url']);
                            $html = $response->body();

                            // Parse with DOMDocument — more reliable than regex
                            $dom = new \DOMDocument();
                            @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
                            $xpath = new \DOMXPath($dom);

                            // Title
                            $titleNode = $dom->getElementsByTagName('title')->item(0);
                            $title = $titleNode ? trim($titleNode->textContent) : null;

                            // Meta description
                            $desc = null;
                            foreach ($xpath->query('//meta[@name="description"]') as $node) {
                                $desc = $node->getAttribute('content');
                            }

                            // H1
                            $h1Count = $dom->getElementsByTagName('h1')->length;

                            // Canonical
                            $hasCanonical = (bool) $xpath->query('//link[@rel="canonical"]')->length;

                            // OG tags
                            $hasOg = (bool) $xpath->query('//meta[@property="og:title"]')->length;

                            // Schema
                            $hasSchema = str_contains($html, 'application/ld+json');

                            // Images without alt
                            $imgsWithoutAlt = 0;
                            foreach ($dom->getElementsByTagName('img') as $img) {
                                if (!$img->hasAttribute('alt') || trim($img->getAttribute('alt')) === '') {
                                    $imgsWithoutAlt++;
                                }
                            }

                            // Word count — supports Persian/Arabic
                            $text = strip_tags($html);
                            $wordCount = count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY));

                            // Calculate score
                            $score = 0;
                            if ($title) {
                                $score += 20;
                                $titleLen = mb_strlen($title);
                                if ($titleLen >= 30 && $titleLen <= 60) $score += 10;
                            }
                            if ($desc) {
                                $score += 20;
                                $descLen = mb_strlen($desc);
                                if ($descLen >= 120 && $descLen <= 160) $score += 10;
                            }
                            if ($h1Count === 1) $score += 15;
                            if ($hasCanonical) $score += 10;
                            if ($hasOg) $score += 10;
                            if ($hasSchema) $score += 5;

                            $issues = [];
                            if (!$title) $issues['title'] = 'عنوان صفحه وجود ندارد';
                            if ($title && mb_strlen($title) < 30) $issues['title_short'] = 'عنوان خیلی کوتاه است';
                            if ($title && mb_strlen($title) > 60) $issues['title_long'] = 'عنوان خیلی بلند است';
                            if (!$desc) $issues['desc'] = 'توضیحات متا وجود ندارد';
                            if ($desc && mb_strlen($desc) < 120) $issues['desc_short'] = 'توضیحات متا خیلی کوتاه است';
                            if ($desc && mb_strlen($desc) > 160) $issues['desc_long'] = 'توضیحات متا خیلی بلند است';
                            if ($h1Count === 0) $issues['h1_missing'] = 'تگ H1 وجود ندارد';
                            if ($h1Count > 1) $issues['h1_multiple'] = 'بیش از یک H1 وجود دارد';
                            if ($imgsWithoutAlt > 0) $issues['alt'] = "{$imgsWithoutAlt} تصویر بدون Alt";
                            if (!$hasOg) $issues['og'] = 'OG Tags وجود ندارد';
                            if (!$hasSchema) $issues['schema'] = 'Schema JSON-LD وجود ندارد';

                            SeoScan::create([
                                'url'                      => $data['url'],
                                'title'                    => $title,
                                'title_length'             => $title ? mb_strlen($title) : 0,
                                'meta_description'         => $desc,
                                'meta_description_length'  => $desc ? mb_strlen($desc) : 0,
                                'has_h1'                   => $h1Count > 0,
                                'h1_count'                 => $h1Count,
                                'has_canonical'            => $hasCanonical,
                                'has_og_tags'              => $hasOg,
                                'has_schema'               => $hasSchema,
                                'images_without_alt'       => $imgsWithoutAlt,
                                'word_count'               => $wordCount,
                                'page_size_kb'             => round(strlen($html) / 1024, 2),
                                'score'                    => min(100, $score),
                                'issues'                   => $issues,
                                'scanned_at'               => now(),
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('اسکن انجام شد')
                                ->body("امتیاز: " . min(100, $score) . " از ۱۰۰")
                                ->send();

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('خطا در اسکن')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSeoScans::route('/'),
            'create' => Pages\CreateSeoScan::route('/create'),
            'edit'   => Pages\EditSeoScan::route('/{record}/edit'),
        ];
    }
}
