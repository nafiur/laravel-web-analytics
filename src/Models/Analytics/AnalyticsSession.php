<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class AnalyticsSession extends Model
{
    protected $table = 'lwa_sessions';

    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'is_bounce'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WebsiteScope());
    }

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function visitor()
    {
        return $this->belongsTo(Visitor::class);
    }

    public function pageviews()
    {
        return $this->hasMany(Pageview::class, 'session_id');
    }

    public function events()
    {
        return $this->hasMany(AnalyticEvent::class, 'session_id');
    }
}
