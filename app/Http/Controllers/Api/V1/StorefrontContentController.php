<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Post;
use App\Models\StoreSetting;
use Illuminate\Http\JsonResponse;

class StorefrontContentController extends Controller
{
    public const CONTRACT_VERSION = '2026-09-06-p3-storefront-v1';

    public function bootstrap(): JsonResponse
    {
        $settings = StoreSetting::query()
            ->public()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group')
            ->map(fn ($items) => $items->mapWithKeys(
                fn (StoreSetting $setting): array => [$setting->key => $setting->typedValue()]
            ));

        return response()->json([
            'data' => [
                'contractVersion' => self::CONTRACT_VERSION,
                'settings' => $settings,
            ],
        ]);
    }

    public function page(string $slug): JsonResponse
    {
        $page = ContentPage::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'slug' => $page->slug,
                'type' => $page->type,
                'title' => $page->title,
                'excerpt' => $page->excerpt,
                'content' => $page->content,
                'metaTitle' => $page->meta_title,
                'metaDescription' => $page->meta_description,
                'publishedAt' => $page->published_at?->toIso8601String(),
            ],
        ]);
    }

    public function faqs(): JsonResponse
    {
        return response()->json([
            'data' => Faq::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['category', 'question', 'answer', 'sort_order'])
                ->map(fn (Faq $faq): array => [
                    'category' => $faq->category,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                    'sortOrder' => $faq->sort_order,
                ]),
        ]);
    }

    public function lookbook(): JsonResponse
    {
        return response()->json([
            'data' => GalleryItem::query()
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
                ]),
        ]);
    }

    public function journal(): JsonResponse
    {
        return response()->json([
            'data' => Post::query()
                ->published()
                ->latest('published_at')
                ->get(['public_id', 'slug', 'title', 'excerpt', 'category', 'tags', 'cover_url', 'author', 'published_at'])
                ->map(fn (Post $post): array => $this->postSummary($post)),
        ]);
    }

    public function journalPost(string $slug): JsonResponse
    {
        $post = Post::query()->published()->where('slug', $slug)->firstOrFail();

        return response()->json([
            'data' => [
                ...$this->postSummary($post),
                'content' => $post->content,
            ],
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
