<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReplaySession extends Model
{
    protected $table = 'lwa_replay_sessions';

    protected $guarded = ['id'];

    protected $casts = [
        'started_at'  => 'datetime',
        'ended_at'    => 'datetime',
        'sample_rate' => 'float',
        'duration_ms' => 'integer',
        'event_count' => 'integer',
        'bytes'       => 'integer',
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

    public function chunks()
    {
        return $this->hasMany(ReplayChunk::class, 'replay_session_id');
    }

    public function markers()
    {
        return $this->hasMany(ReplayMarker::class, 'replay_session_id');
    }
}
