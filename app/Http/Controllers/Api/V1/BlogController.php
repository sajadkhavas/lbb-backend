<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'tag', 'search', 'per_page', 'page']);
        $cacheKey = 'blog.' . md5(serialize($filters));

        // اگه search داره cache نکن
        if (!empty($filters['search'])) {
            $posts = $this->buildQuery($request)->paginate((int) $request->get('per_page', 12));
        } else {
            $posts = Cache::remember($cacheKey, 300, fn() =>
                $this->buildQuery($request)->paginate((int) $request->get('per_page', 12))
            );
        }

        return BlogPostResource::collection($posts);
    }

    public function show(string $slug)
    {
        $post = Cache::remember("blog.{$slug}", 600, fn() =>
            BlogPost::published()->where('slug', $slug)->firstOrFail()
        );

        BlogPost::withoutTimestamps(fn() =>
            BlogPost::where('id', $post->id)->increment('view_count')
        );

        return new BlogPostResource($post);
    }

    public function latest()
    {
        $posts = Cache::remember('blog.latest', 300, fn() =>
            BlogPost::published()->orderByDesc('published_at')->limit(3)->get()
        );

        return BlogPostResource::collection($posts);
    }

    private function buildQuery(Request $request)
    {
        $q = BlogPost::published();
        if ($cat = $request->get('category')) $q->where('category', $cat);
        if ($tag = $request->get('tag'))      $q->whereJsonContains('tags', $tag);
        if ($s   = $request->get('search'))   $q->where(fn($x) => $x
            ->where('title', 'like', "%{$s}%")
            ->orWhere('excerpt', 'like', "%{$s}%")
            ->orWhere('content', 'like', "%{$s}%")
        );
        return $q->orderByDesc('published_at');
    }
}
