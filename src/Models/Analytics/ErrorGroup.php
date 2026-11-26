<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ErrorGroup extends Model
{
    protected $table = 'lwa_error_groups';

    protected $guarded = ['id'];

    protected $casts = [
        'occurrences'   => 'integer',
        'line'          => 'integer',
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
        'resolved_at'   => 'datetime',
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

    public function events()
    {
        return $this->hasMany(ErrorEvent::class, 'error_group_id');
    }
}
