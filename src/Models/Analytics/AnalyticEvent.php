<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class AnalyticEvent extends Model
{
    protected $table = 'lwa_events';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'properties' => 'array',
        'value'      => 'float',
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
