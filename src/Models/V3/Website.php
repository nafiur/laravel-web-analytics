<?php

namespace Nafiur\WebAnalytics\Models\V3;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    protected $table = 'lwa_websites';

    protected $guarded = ['id'];

    protected $casts = ['config' => 'array'];

    public function visitors()
    {
        return $this->hasMany(Visitor::class);
    }

    public function sessions()
    {
        return $this->hasMany(AnalyticsSession::class);
    }

    public function pageviews()
    {
        return $this->hasMany(Pageview::class);
    }

    public function events()
    {
        return $this->hasMany(AnalyticEvent::class);
    }
}
