<?php

namespace App\Http\Requests\Api\V1;

use App\Domain\Catalog\PublicCatalogQuery;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $max = (int) config('lbb.policies.pagination.catalog_max', 48);

        return [
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:400', 'regex:/^[\pL\pN_,-]+$/u'],
            'collection' => ['nullable', 'string', 'max:400', 'regex:/^[\pL\pN_,-]+$/u'],
            'color' => ['nullable', 'string', 'max:400', 'regex:/^[\pL\pN_,-]+$/u'],
            'size' => ['nullable', 'string', 'max:200', 'regex:/^[\pL\pN_. ,-]+$/u'],
            'availability' => ['nullable', Rule::in(PublicCatalogQuery::AVAILABILITY)],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0', 'gte:min_price'],
            'sort' => ['nullable', Rule::in(PublicCatalogQuery::SORTS)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.$max],
        ];
    }
}
