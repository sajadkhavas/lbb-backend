<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->slug,
            'slug'            => $this->slug,
            'name'            => $this->name,
            'country'         => $this->country,
            'description'     => $this->description,
            'longDescription' => $this->long_description,
            'logo'            => $this->logo,
            'website'         => $this->website,
            'isFeatured'      => (bool) $this->is_featured,
            'productCount'    => $this->products_count ?? 0,
            'seo' => [
                'title'       => $this->meta_title,
                'description' => $this->meta_description,
                'keywords'    => $this->meta_keywords,
            ],
        ];
    }
}
