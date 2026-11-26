<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Experiment extends Model
{
    protected $table = 'lwa_experiments';

    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function scopeForWebsite(Builder $query, ?int $websiteId): Builder
    {
        if ($websiteId !== null && $websiteId > 0) {
            return $query->where('website_id', $websiteId);
        }

        return $query->whereNull('website_id');
    }

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        if ($tenantId !== null && $tenantId !== '') {
            return $query->where('tenant_id', $tenantId);
        }

        return $query->whereNull('tenant_id');
    }
}
