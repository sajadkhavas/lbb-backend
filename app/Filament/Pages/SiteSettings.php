<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\{FileUpload, RichEditor, Tabs, Textarea, TextInput};
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'تنظیمات سایت';
    protected static ?string $title           = 'تنظیمات سایت';
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.site-settings';

    public array $data = [];

    public function mount(): void
    {
        $this->data = SiteSetting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make()->tabs([
                Tabs\Tab::make('صفحه اصلی')->icon('heroicon-o-home')->schema([
                    TextInput::make('home_hero_title')->label('عنوان اصلی هیرو'),
                    Textarea::make('home_hero_subtitle')->label('زیرعنوان هیرو')->rows(2),
                    TextInput::make('home_hero_badge')->label('بج هیرو'),
                    TextInput::make('home_hero_button_primary')->label('متن دکمه اصلی'),
                    TextInput::make('home_hero_button_secondary')->label('متن دکمه ثانویه'),
                    TextInput::make('home_stats_products')->label('آمار: تعداد محصولات'),
                    TextInput::make('home_stats_brands')->label('آمار: تعداد برندها'),
                    TextInput::make('home_stats_customers')->label('آمار: تعداد مشتریان'),
                    RichEditor::make('home_seo_content')->label('محتوای سئو صفحه اصلی'),
                ]),
                Tabs\Tab::make('شرکت')->icon('heroicon-o-building-office')->schema([
                    TextInput::make('site_name')->label('نام سایت'),
                    TextInput::make('site_phone')->label('تلفن'),
                    TextInput::make('site_email')->label('ایمیل')->email(),
                    Textarea::make('site_address')->label('آدرس')->rows(2),
                    FileUpload::make('site_logo')->label('لوگو')->image()->directory('settings'),
                    FileUpload::make('site_favicon')->label('Favicon')->image()->directory('settings'),
                ]),
                Tabs\Tab::make('شبکه‌های اجتماعی')->icon('heroicon-o-share')->schema([
                    TextInput::make('social_instagram')->label('اینستاگرام')->url(),
                    TextInput::make('social_linkedin')->label('لینکدین')->url(),
                    TextInput::make('social_telegram')->label('تلگرام')->url(),
                    TextInput::make('social_whatsapp')->label('واتساپ'),
                    TextInput::make('social_aparat')->label('آپارات')->url(),
                ]),
                Tabs\Tab::make('سئو سراسری')->icon('heroicon-o-magnifying-glass')->schema([
                    TextInput::make('seo_site_title')->label('عنوان کلی (60)')->maxLength(60),
                    Textarea::make('seo_site_description')->label('توضیحات کلی (160)')->maxLength(160)->rows(2),
                    TextInput::make('seo_google_analytics')->label('Google Analytics ID'),
                    TextInput::make('seo_google_search_console')->label('Google Search Console کد'),
                    Textarea::make('seo_custom_head_scripts')->label('اسکریپت‌های هد سفارشی')->rows(4),
                ]),
            ])->columnSpanFull()->persistTabInQueryString(),
        ])->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->data as $key => $value) {
            // اگه array بود (مثل FileUpload) به string تبدیل کن
            if (is_array($value)) {
                $value = !empty($value) ? (is_string(reset($value)) ? reset($value) : json_encode($value)) : null;
            }
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('site_settings');
        Cache::forget('site_settings_kv');
        Notification::make()->title('تنظیمات با موفقیت ذخیره شد')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')->label('ذخیره تنظیمات')->submit('save'),
        ];
    }
}
