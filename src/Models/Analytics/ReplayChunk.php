<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReplayChunk extends Model
{
    protected $table = 'lwa_replay_chunks';

    protected $guarded = ['id'];

    protected $casts = [
        'sequence'    => 'integer',
        'bytes'       => 'integer',
        'event_count' => 'integer',
        'from_ms'     => 'integer',
        'to_ms'       => 'integer',
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

    public function session()
    {
        return $this->belongsTo(ReplaySession::class, 'replay_session_id');
    }
}
