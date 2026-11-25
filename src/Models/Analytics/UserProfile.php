<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class UserProfile extends Model
{
    protected $table = 'lwa_profiles';

    protected $guarded = ['id'];

    protected $casts = [
        'traits'        => 'array',
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

    public function aliases()
    {
        return $this->hasMany(IdentityAlias::class, 'distinct_id', 'distinct_id')
            ->where('website_id', $this->website_id);
    }
}
