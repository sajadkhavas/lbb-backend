<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceMetric extends Model
{
    protected $fillable = [
        'page_url', 'lcp', 'fid', 'cls', 'fcp', 'ttfb',
        'device_type', 'browser', 'country', 'ip',
    ];

    protected $casts = [
        'lcp'  => 'float',
        'fid'  => 'float',
        'cls'  => 'float',
        'fcp'  => 'float',
        'ttfb' => 'float',
    ];
}
