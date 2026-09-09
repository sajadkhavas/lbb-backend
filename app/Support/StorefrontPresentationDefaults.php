<?php

namespace App\Support;

final class StorefrontPresentationDefaults
{
    /**
     * Public presentation defaults used only when a setting has not been saved yet.
     * Existing StoreSetting rows always win. Legal/commercial claims intentionally
     * default to disabled or pending states and are never invented here.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function settings(): array
    {
        return [
            'home' => [
                'home.ticker' => [
                    'ال‌بی‌بی',
                    'پوشاک خیابانی',
                    'تیشرت',
                    'شلوار',
                    'کتونی',
                    'جوراب',
                    'کرج',
                    'پاساژ مهستان',
                ],
                'home.trust' => [
                    [
                        'icon' => 'details',
                        'title' => 'جزئیات روشن محصول',
                        'description' => 'جنس، تن‌خور، رنگ و ویژگی‌های هر قطعه پیش از انتخاب در دسترس است',
                        'href' => '/shop',
                        'enabled' => true,
                    ],
                    [
                        'icon' => 'size',
                        'title' => 'انتخاب آگاهانه اندازه',
                        'description' => 'راهنمای اندازه و توضیح تن‌خور برای مقایسه و انتخاب دقیق‌تر',
                        'href' => '/size-guide',
                        'enabled' => true,
                    ],
                    [
                        'icon' => 'location',
                        'title' => 'حضور در کرج',
                        'description' => 'فروشگاه حضوری LBB در پاساژ مهستان کرج',
                        'href' => '/contact',
                        'enabled' => true,
                    ],
                ],
                'home.section_copy' => [
                    'hero' => [
                        'helperText' => 'قیمت، رنگ، سایزهای موجود و تن‌خور هر محصول را قبل از انتخاب بررسی کن.',
                        'categoryPrompt' => 'دسته موردنظرت را سریع پیدا کن:',
                        'categoryAnchorLabel' => 'دیدن دسته‌بندی‌ها',
                    ],
                    'categories' => [
                        'label' => 'دسته‌بندی محصولات',
                        'title' => 'دنبال چی می‌گردی؟',
                        'lede' => 'دسته‌های منتخب فروشگاه را مرور کن و مستقیم وارد مدل‌های همان بخش شو.',
                        'actionLabel' => 'همه محصولات',
                    ],
                    'products' => [
                        'label' => 'انتخاب‌های ال‌بی‌بی',
                        'title' => 'تازه‌های فروشگاه',
                        'lede' => 'محصولات تازه منتشرشده فروشگاه با قیمت و موجودی زنده.',
                        'actionLabel' => 'کاتالوگ کامل',
                    ],
                    'instagram' => [
                        'followCta' => 'دنبال ما در اینستاگرام',
                        'lookbookCta' => 'مشاهده لوک‌بوک',
                    ],
                ],
                'home.decision_support' => [
                    'enabled' => true,
                    'label' => 'راهنمای انتخاب',
                    'title' => 'قبل از انتخاب، جواب‌ها را داشته باش',
                    'lede' => 'اگر بین دو سایز یا مدل مرددی، این راهنماها انتخاب را سریع‌تر و مطمئن‌تر می‌کنند.',
                    'checkLabel' => 'قبل از خرید بررسی کن',
                    'checks' => [
                        'تن‌خور و جدول اندازه را پیش از انتخاب ببین',
                        'رنگ و سایزهای موجود هر محصول را همان لحظه بررسی کن',
                        'جنس پارچه و روش نگهداری را در صفحه محصول بخوان',
                        'برای پرو و خرید حضوری به فروشگاه مهستان سر بزن',
                    ],
                    'links' => [
                        [
                            'label' => 'راهنمای انتخاب سایز',
                            'latin' => 'SIZE / FIT',
                            'description' => 'قبل از خرید، فیت و اندازه مناسب را مقایسه کن.',
                            'href' => '/size-guide',
                        ],
                        [
                            'label' => 'ارسال و مرجوعی',
                            'latin' => 'DELIVERY / RETURNS',
                            'description' => 'پیش از سفارش، شرایط ارسال و امکان مرجوعی را بررسی کن.',
                            'href' => '/shipping-returns',
                        ],
                        [
                            'label' => 'پارچه و نگهداری',
                            'latin' => 'MATERIAL / CARE',
                            'description' => 'گرماژ، ترکیب پارچه و روش شست‌وشوی هر قطعه.',
                            'href' => '/journal/materials-101-parche-shenasi',
                        ],
                    ],
                ],
                'home.local_store' => [
                    'enabled' => true,
                    'eyebrow' => 'فروشگاه حضوری ال‌بی‌بی',
                    'title' => 'آنلاین ببین، در مهستان از نزدیک انتخاب کن.',
                    'body' => 'مدل‌ها را در سایت مقایسه کن و اگر دوست داشتی برای دیدن رنگ، جنس و تن‌خور از نزدیک به فروشگاه ال‌بی‌بی در پاساژ مهستان کرج سر بزن.',
                    'imageUrl' => null,
                    'addressTitle' => 'آدرس فروشگاه',
                    'instagramTitle' => 'مدل‌های تازه در اینستاگرام',
                    'contactCta' => 'اطلاعات تماس و مراجعه',
                    'instagramCta' => 'پیام در اینستاگرام',
                    'shopCta' => 'قبل از مراجعه محصولات را ببین',
                ],
                'home.featured_story' => [
                    'enabled' => false,
                    'collectionSlug' => '',
                    'eyebrow' => 'استایل پیشنهادی ال‌بی‌بی',
                    'storyPoints' => [],
                    'collectionCta' => 'دیدن کالکشن',
                    'lookbookCta' => 'ایده‌های بیشتر برای استایل',
                    'inventoryNote' => 'برای دیدن موجودی و سایزهای هر قطعه وارد صفحه همان محصول شو.',
                ],
            ],
            'policy' => [
                'policy.returns' => [
                    'enabled' => false,
                    'verification' => 'pending',
                    'exchangeEnabled' => false,
                    'returnWindowDays' => null,
                    'refundTimeLabel' => null,
                    'customerPaysReturnShipping' => null,
                    'quickIssueNoticeHours' => null,
                ],
            ],
            'trust' => [
                'trust.enamad' => [
                    'enabled' => false,
                    'verification' => 'missing',
                    'identifier' => null,
                    'verificationUrl' => null,
                    'badgeImageUrl' => null,
                    'altText' => 'نماد اعتماد الکترونیکی LBB',
                    'displayLocation' => 'footer',
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public static function contactDefaults(): array
    {
        return [
            'email' => null,
            'addressLine' => null,
            'mapUrl' => null,
            'openingHours' => [],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $settings
     * @return array<string, array<string, mixed>>
     */
    public static function merge(array $settings): array
    {
        foreach (self::settings() as $group => $items) {
            foreach ($items as $key => $default) {
                if (!array_key_exists($key, $settings[$group] ?? [])) {
                    $settings[$group][$key] = $default;
                }
            }
        }

        $contact = $settings['contact']['contact.public'] ?? null;
        if (is_array($contact)) {
            $settings['contact']['contact.public'] = array_replace(self::contactDefaults(), $contact);
        }

        return $settings;
    }
}
