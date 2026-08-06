<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerformanceMetric;
use Illuminate\Http\Request;

class PerformanceMetricController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'page_url'    => 'required|string|max:500',
            'lcp'         => 'nullable|numeric|min:0|max:60000',
            'fid'         => 'nullable|numeric|min:0|max:10000',
            'cls'         => 'nullable|numeric|min:0|max:10',
            'fcp'         => 'nullable|numeric|min:0|max:60000',
            'ttfb'        => 'nullable|numeric|min:0|max:60000',
            'device_type' => 'nullable|string|max:20',
            'browser'     => 'nullable|string|max:50',
        ]);

        $data['country'] = $request->header('CF-IPCountry') ?? null;
        $data['ip']      = $request->ip();

        PerformanceMetric::create($data);

        return response()->json(['success' => true], 201);
    }
}
