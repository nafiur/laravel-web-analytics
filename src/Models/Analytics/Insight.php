<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    protected $table = 'lwa_insights';

    protected $guarded = ['id'];

    protected $casts = [
        'config' => 'array',
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
