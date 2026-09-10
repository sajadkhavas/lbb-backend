<?php

namespace App\Http\Controllers\Api;

use App\Domain\Catalog\MannequinModel3d;
use App\Domain\Catalog\PublicCatalogQuery;
use App\Domain\Catalog\PublicCatalogTransformer;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use App\Support\PublicMediaUrl;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProductMannequinModel3dController extends Controller
{
    public function __construct(
        private readonly PublicCatalogQuery $catalog,
        private readonly PublicCatalogTransformer $transformer,
    ) {}

    public function __invoke(string $slug): JsonResponse
    {
        $product = $this->catalog->product($slug);
        $mannequin = $this->transformer->productSummary($product)['mannequin'] ?? null;

        // A 3D model is an enhancement to the existing 2D mannequin contract, not a
        // replacement. Keeping the 2D profile mandatory guarantees a lightweight
        // fallback on mobile, constrained hardware and renderer/load failures.
        if (! is_array($mannequin) || ($mannequin['enabled'] ?? false) !== true) {
            throw new NotFoundHttpException;
        }

        // PublicCatalogQuery intentionally eager-loads only mannequin-front media.
        // Clear that scoped relation so Media Library can resolve the 3D collection.
        $product->unsetRelation('media');
        $media = MannequinModel3d::media($product);

        if ($media === null) {
            throw new NotFoundHttpException;
        }

        $url = PublicMediaUrl::fromPathOrUrl($media->getUrl());

        if ($url === null) {
            throw new NotFoundHttpException;
        }

        return ApiResponse::success([
            'url' => $url,
            'format' => 'glb',
            'bytes' => (int) $media->size,
        ]);
    }
}
