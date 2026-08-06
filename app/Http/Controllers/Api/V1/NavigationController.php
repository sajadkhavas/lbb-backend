<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\NavigationItem;
use Illuminate\Http\JsonResponse;

class NavigationController extends Controller
{
    public function index(): JsonResponse
    {
        $items = NavigationItem::with('children')
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($items);
    }
}
