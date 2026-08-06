<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Support\Facades\Cache;

class SliderController extends Controller
{
    public function __invoke()
    {
        $sliders = Cache::remember('sliders.active', 600, fn () =>
            Slider::active()->orderBy('sort_order')->get()
        );

        return response()->json([
            'data' => $sliders->map(fn ($s) => [
                'id'          => $s->id,
                'title'       => $s->title,
                'subtitle'    => $s->subtitle,
                'description' => $s->description,
                'image'       => $s->image,
                'link'        => $s->link,
                'buttonText'  => $s->button_text,
                'badge'       => $s->badge,
            ]),
        ]);
    }
}
