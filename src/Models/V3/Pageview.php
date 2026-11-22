<?php

namespace Nafiur\WebAnalytics\Models\V3;

use Illuminate\Database\Eloquent\Model;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class Pageview extends Model
{
    protected $table = 'lwa_pageviews';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'created_at'       => 'datetime',
        'response_time_ms' => 'float',
        'bytes_sent'       => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WebsiteScope());
    }

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function session()
    {
        return $this->belongsTo(AnalyticsSession::class, 'session_id');
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }
}
