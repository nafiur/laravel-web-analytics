<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class Visitor extends Model
{
    protected $table = 'lwa_visitors';

    protected $guarded = ['id'];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WebsiteScope());
    }

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function sessions()
    {
        return $this->hasMany(AnalyticsSession::class);
    }

    public function pageviews()
    {
        return $this->hasMany(Pageview::class);
    }
}
