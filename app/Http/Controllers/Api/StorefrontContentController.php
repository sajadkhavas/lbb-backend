<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\StoreSetting;
use App\Support\ApiResponse;
use App\Support\StorefrontPresentationDefaults;
use Illuminate\Http\JsonResponse;

class StorefrontContentController extends Controller
{
    public const CONTRACT_VERSION = '2026-09-09-final-admin-storefront-v2';

    public function bootstrap(): JsonResponse
    {
        $settings = [];

        StoreSetting::query()
            ->public()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->each(function (StoreSetting $setting) use (&$settings): void {
                $settings[$setting->group][$setting->key] = $setting->typedValue();
            });

        $settings = StorefrontPresentationDefaults::merge($settings);
        $paymentProvider = trim((string) config('lbb.payment.provider', 'disabled'));
        $paymentEnabled = (bool) config('lbb.payment.enabled', false)
            && $paymentProvider !== ''
            && $paymentProvider !== 'disabled';

        return ApiResponse::success([
            'contractVersion' => self::CONTRACT_VERSION,
            'settings' => $settings,
            'runtime' => [
                'checkoutEnabled' => (bool) config('lbb.checkout.enabled', false),
                'payment' => [
                    'enabled' => $paymentEnabled,
                    'provider' => $paymentEnabled ? $paymentProvider : 'disabled',
                ],
            ],
        ]);
    }

    public function page(string $slug): JsonResponse
    {
        $page = ContentPage::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return ApiResponse::success([
            'slug' => $page->slug,
            'type' => $page->type,
            'title' => $page->title,
            'excerpt' => $page->excerpt,
            'content' => $page->content,
            'metaTitle' => $page->meta_title,
            'metaDescription' => $page->meta_description,
            'publishedAt' => $page->published_at?->toIso8601String(),
        ]);
    }

    public function faqs(): JsonResponse
    {
        return ApiResponse::success(
            Faq::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['category', 'question', 'answer', 'sort_order'])
                ->map(fn (Faq $faq): array => [
                    'category' => $faq->category,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'sortOrder' => $faq->sort_order,
                ])
                ->values(),
        );
    }

    public function lookbook(): JsonResponse
    {
        return ApiResponse::success(
            GalleryItem::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['title', 'caption', 'image_url', 'link_url', 'sort_order'])
                ->map(fn (GalleryItem $item): array => [
                    'title' => $item->title,
                    'caption' => $item->caption,
                    'imageUrl' => $item->image_url,
                    'linkUrl' => $item->link_url,
                    'sortOrder' => $item->sort_order,
                ])
                ->values(),
        );
    }

    public function journal(): JsonResponse
    {
        return ApiResponse::success(
            Post::query()
                ->published()
                ->latest('published_at')
                ->get(['public_id', 'slug', 'title', 'excerpt', 'category', 'tags', 'cover_url', 'author', 'published_at'])
                ->map(fn (Post $post): array => $this->postSummary($post))
                ->values(),
        );
    }

    public function journalPost(string $slug): JsonResponse
    {
        $post = Post::query()->published()->where('slug', $slug)->firstOrFail();

        return ApiResponse::success([
            ...$this->postSummary($post),
            'content' => $post->content,
        ]);
    }

    private function postSummary(Post $post): array
    {
        return [
            'publicId' => $post->public_id,
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'category' => $post->category,
            'tags' => $post->tags ?? [],
            'coverUrl' => $post->cover_url,
            'author' => $post->author,
            'publishedAt' => $post->published_at?->toIso8601String(),
        ];
    }
}
