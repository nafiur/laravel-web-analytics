<?php

namespace Nafiur\WebAnalytics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LwaLogs extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status_code'      => 'integer',
        'response_time_ms' => 'float',
        'bytes_sent'       => 'integer',
        'user_id'          => 'integer',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Get the table associated with the model dynamically from configuration.
     */
    public function getTable()
    {
        return config('laravel-web-analytics.table_name', 'lwa_logs');
    }

    /**
     * Scope query by tenant ID if multi-tenancy is active.
     */
    public function scopeForTenant($query, ?string $tenantId = null)
    {
        if ($tenantId !== null && $tenantId !== '') {
            return $query->where('tenant_id', $tenantId);
        }

        return $query;
    }
}
