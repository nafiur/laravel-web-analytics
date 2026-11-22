<?php

namespace Nafiur\WebAnalytics\Models\V3;

use Illuminate\Database\Eloquent\Model;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class DailyStat extends Model
{
    protected $table = 'lwa_daily_stats';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'bucket_at'       => 'datetime',
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
