<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class HourlyStat extends Model
{
    protected $table = 'lwa_hourly_stats';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'bucket_at'     => 'datetime',
        'avg_response_ms' => 'float',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WebsiteScope());
    }

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
