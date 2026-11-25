<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class IdentityAlias extends Model
{
    protected $table = 'lwa_identity_aliases';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'aliased_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WebsiteScope());
    }

    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'distinct_id', 'distinct_id');
    }
}
