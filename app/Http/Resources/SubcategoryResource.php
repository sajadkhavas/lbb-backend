<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubcategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->slug,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'fullNameEn'  => $this->full_name_en,
            'description' => $this->description,
            'image'       => $this->image,
            'seo' => [
                'title'           => $this->meta_title,
                'description'     => $this->meta_description,
                'keywords'        => $this->meta_keywords,
                'longDescription' => $this->long_description,
                'faqSchema'       => $this->faq_schema,
            ],
        ];
    }
}
