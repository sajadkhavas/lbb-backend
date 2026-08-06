<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->slug,
            'slug'            => $this->slug,
            'label'           => $this->name,
            'name'            => $this->name,
            'description'     => $this->description,
            'longDescription' => $this->long_description,
            'image'           => $this->image,
            'icon'            => $this->icon,
            'productCount'    => $this->products_count ?? 0,
            'subcategories'   => SubcategoryResource::collection($this->whenLoaded('subcategories')),
            'seo' => [
                'title'        => $this->seo_title,
                'description'  => $this->seo_description,
                'keywords'     => $this->meta_keywords,
                'heroTitle'    => $this->hero_title ?: "خرید {$this->name}",
                'heroSubtitle' => $this->hero_subtitle,
                'schema'       => $this->breadcrumb_schema,
                'faqSchema'    => $this->faq_schema,
            ],
        ];
    }
}
