<?php

namespace App\Filament\Pages;

use App\Models\StoreSetting;
use Filament\Actions\Action;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'تنظیمات سایت';

    protected static ?string $title = 'تنظیمات عمومی و صفحه اصلی';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.site-settings';

    public array $data = [];

    public function mount(): void
    {
        $brand = $this->jsonSetting('brand', 'brand.identity');
        $copy = $this->jsonSetting('brand', 'brand.copy');
        $contact = $this->jsonSetting('contact', 'contact.public');
        $intro = $this->jsonSetting('home', 'home.brand_intro');
        $home = $this->jsonSetting('home', 'home.presentation');
        $seo = $this->jsonSetting('seo', 'seo.defaults');

        $this->data = [
            'brand_name' => $brand['name'] ?? 'LBB',
            'brand_name_fa' => $brand['nameFa'] ?? 'LBB',
            'brand_category' => $brand['category'] ?? '',
            'brand_city' => $brand['city'] ?? 'کرج',
            'brand_province' => $brand['province'] ?? 'البرز',
            'brand_location' => $brand['physicalLocation'] ?? '',
            'brand_location_short' => $brand['physicalLocationShort'] ?? '',
            'brand_instagram_handle' => $brand['instagramHandle'] ?? '',
            'brand_instagram_url' => $brand['instagramUrl'] ?? '',
            'brand_slogan' => $brand['slogan'] ?? '',
            'brand_story_title' => $brand['storyTitle'] ?? '',
            'brand_descriptor' => $brand['descriptor'] ?? '',
            'brand_short_introduction' => $brand['shortIntroduction'] ?? '',
            'homepage_title' => $copy['homepageTitle'] ?? '',
            'homepage_description' => $copy['homepageDescription'] ?? '',
            'hero_eyebrow' => $copy['heroEyebrow'] ?? '',
            'hero_title' => $copy['heroTitle'] ?? '',
            'hero_body' => $copy['heroBody'] ?? '',
            'primary_cta' => $copy['primaryCta'] ?? '',
            'secondary_cta' => $copy['secondaryCta'] ?? '',
            'store_location_label' => $copy['storeLocationLabel'] ?? '',
            'contact_phone' => $contact['phone'] ?? '',
            'contact_whatsapp' => $contact['whatsapp'] ?? '',
            'intro_eyebrow' => $intro['eyebrow'] ?? '',
            'intro_title' => $intro['title'] ?? '',
            'intro_body' => $intro['body'] ?? '',
            'intro_story_cta' => $intro['storyCta'] ?? '',
            'intro_store_cta' => $intro['storeCta'] ?? '',
            'hero_product_slug' => $home['heroProductSlug'] ?? '',
            'category_order' => $home['categoryOrder'] ?? [],
            'home_sections' => $home['sections'] ?? [],
            'seo_site_name' => $seo['siteName'] ?? 'LBB',
            'seo_locale' => $seo['locale'] ?? 'fa_IR',
            'seo_description' => $seo['organizationDescription'] ?? '',
            'seo_instagram_url' => $seo['instagramUrl'] ?? '',
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('تنظیمات')->tabs([
                    Tabs\Tab::make('هویت برند')->schema([
                        TextInput::make('brand_name')->label('نام لاتین برند')->required(),
                        TextInput::make('brand_name_fa')->label('نام نمایشی برند')->required(),
                        TextInput::make('brand_category')->label('دسته/جایگاه برند'),
                        TextInput::make('brand_city')->label('شهر'),
                        TextInput::make('brand_province')->label('استان'),
                        TextInput::make('brand_location')->label('آدرس/موقعیت کامل')->columnSpanFull(),
                        TextInput::make('brand_location_short')->label('موقعیت کوتاه'),
                        TextInput::make('brand_instagram_handle')->label('نام کاربری اینستاگرام'),
                        TextInput::make('brand_instagram_url')->label('لینک اینستاگرام')->url(),
                        TextInput::make('brand_slogan')->label('شعار برند')->columnSpanFull(),
                        TextInput::make('brand_story_title')->label('عنوان داستان برند'),
                        TextInput::make('brand_descriptor')->label('Descriptor'),
                        Textarea::make('brand_short_introduction')->label('معرفی کوتاه')->rows(3)->columnSpanFull(),
                    ])->columns(2),
                    Tabs\Tab::make('Hero و صفحه اصلی')->schema([
                        TextInput::make('homepage_title')->label('Title صفحه اصلی')->columnSpanFull(),
                        Textarea::make('homepage_description')->label('Description صفحه اصلی')->rows(2)->columnSpanFull(),
                        TextInput::make('hero_eyebrow')->label('Eyebrow هیرو'),
                        TextInput::make('hero_title')->label('عنوان هیرو'),
                        Textarea::make('hero_body')->label('متن هیرو')->rows(3)->columnSpanFull(),
                        TextInput::make('primary_cta')->label('CTA اصلی'),
                        TextInput::make('secondary_cta')->label('CTA دوم'),
                        TextInput::make('store_location_label')->label('برچسب موقعیت فروشگاه'),
                        TextInput::make('hero_product_slug')->label('Slug محصول Hero')->helperText('فقط محصول واقعی منتشرشده را وارد کنید.'),
                        TagsInput::make('category_order')->label('ترتیب Slug دسته‌ها')->columnSpanFull(),
                        TagsInput::make('home_sections')->label('ترتیب سکشن‌های خانه')->columnSpanFull(),
                    ])->columns(2),
                    Tabs\Tab::make('معرفی برند')->schema([
                        TextInput::make('intro_eyebrow')->label('Eyebrow'),
                        TextInput::make('intro_title')->label('عنوان'),
                        Textarea::make('intro_body')->label('متن')->rows(3)->columnSpanFull(),
                        TextInput::make('intro_story_cta')->label('CTA داستان'),
                        TextInput::make('intro_store_cta')->label('CTA فروشگاه'),
                    ])->columns(2),
                    Tabs\Tab::make('ارتباط')->schema([
                        TextInput::make('contact_phone')->label('تلفن عمومی'),
                        TextInput::make('contact_whatsapp')->label('واتساپ عمومی'),
                    ])->columns(2),
                    Tabs\Tab::make('SEO')->schema([
                        TextInput::make('seo_site_name')->label('نام سایت'),
                        TextInput::make('seo_locale')->label('Locale'),
                        Textarea::make('seo_description')->label('توضیح سازمان')->rows(3)->columnSpanFull(),
                        TextInput::make('seo_instagram_url')->label('Instagram URL')->url()->columnSpanFull(),
                    ])->columns(2),
                ])->columnSpanFull()->persistTabInQueryString(),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $this->putJsonSetting('brand', 'brand.identity', 'هویت برند', [
            'name' => $state['brand_name'] ?? '',
            'nameFa' => $state['brand_name_fa'] ?? '',
            'category' => $state['brand_category'] ?? '',
            'city' => $state['brand_city'] ?? '',
            'province' => $state['brand_province'] ?? '',
            'physicalLocation' => $state['brand_location'] ?? '',
            'physicalLocationShort' => $state['brand_location_short'] ?? '',
            'instagramHandle' => $state['brand_instagram_handle'] ?? '',
            'instagramUrl' => $state['brand_instagram_url'] ?? '',
            'slogan' => $state['brand_slogan'] ?? '',
            'storyTitle' => $state['brand_story_title'] ?? '',
            'descriptor' => $state['brand_descriptor'] ?? '',
            'shortIntroduction' => $state['brand_short_introduction'] ?? '',
        ]);

        $this->putJsonSetting('brand', 'brand.copy', 'متن‌های اصلی برند', [
            'homepageTitle' => $state['homepage_title'] ?? '',
            'homepageDescription' => $state['homepage_description'] ?? '',
            'heroEyebrow' => $state['hero_eyebrow'] ?? '',
            'heroTitle' => $state['hero_title'] ?? '',
            'heroBody' => $state['hero_body'] ?? '',
            'primaryCta' => $state['primary_cta'] ?? '',
            'secondaryCta' => $state['secondary_cta'] ?? '',
            'storeLocationLabel' => $state['store_location_label'] ?? '',
        ]);

        $existingContact = $this->jsonSetting('contact', 'contact.public');
        $this->putJsonSetting('contact', 'contact.public', 'اطلاعات تماس عمومی', [
            ...$existingContact,
            'phone' => $state['contact_phone'] ?? '',
            'whatsapp' => $state['contact_whatsapp'] ?? '',
            'instagramHandle' => $state['brand_instagram_handle'] ?? '',
            'instagramUrl' => $state['brand_instagram_url'] ?? '',
            'locationLabel' => $state['brand_location_short'] ?? '',
            'city' => $state['brand_city'] ?? '',
            'province' => $state['brand_province'] ?? '',
        ]);

        $existingIntro = $this->jsonSetting('home', 'home.brand_intro');
        $this->putJsonSetting('home', 'home.brand_intro', 'معرفی برند در خانه', [
            ...$existingIntro,
            'enabled' => (bool) ($existingIntro['enabled'] ?? true),
            'version' => (string) ($existingIntro['version'] ?? 'v1'),
            'eyebrow' => $state['intro_eyebrow'] ?? '',
            'title' => $state['intro_title'] ?? '',
            'body' => $state['intro_body'] ?? '',
            'storyCta' => $state['intro_story_cta'] ?? '',
            'storeCta' => $state['intro_store_cta'] ?? '',
        ]);

        $this->putJsonSetting('home', 'home.presentation', 'چیدمان صفحه اصلی', [
            'heroProductSlug' => $state['hero_product_slug'] ?? '',
            'categoryOrder' => array_values($state['category_order'] ?? []),
            'sections' => array_values($state['home_sections'] ?? []),
        ]);

        $this->putJsonSetting('seo', 'seo.defaults', 'SEO پیش‌فرض', [
            'siteName' => $state['seo_site_name'] ?? '',
            'locale' => $state['seo_locale'] ?? 'fa_IR',
            'organizationDescription' => $state['seo_description'] ?? '',
            'instagramUrl' => $state['seo_instagram_url'] ?? '',
        ]);

        Notification::make()->title('تنظیمات عمومی ذخیره شد')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [Action::make('save')->label('ذخیره تنظیمات')->submit('save')];
    }

    private function jsonSetting(string $group, string $key): array
    {
        $setting = StoreSetting::query()->where('group', $group)->where('key', $key)->first();
        $value = $setting?->typedValue();

        return is_array($value) ? $value : [];
    }

    private function putJsonSetting(string $group, string $key, string $label, array $value): void
    {
        StoreSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $group,
                'label' => $label,
                'type' => 'json',
                'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'is_public' => true,
            ],
        );
    }
}
