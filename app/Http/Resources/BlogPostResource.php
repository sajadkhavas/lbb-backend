<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->slug,
            'slug'         => $this->slug,
            'title'        => $this->title,
            'excerpt'      => $this->excerpt,
            'content'      => $this->content,
            'author'       => $this->author,
            'category'     => $this->category,
            'tags'         => $this->tags ?? [],
            'image'        => $this->image,
            'readTime'     => $this->read_time,
            'publishedAt'  => $this->published_at?->toIso8601String(),
            'viewCount'    => $this->view_count,
            'seo' => [
                'title'       => $this->meta_title ?: $this->title,
                'description' => $this->meta_description ?: $this->excerpt,
                'keywords'    => $this->meta_keywords,
            ],
        ];
    }
}
