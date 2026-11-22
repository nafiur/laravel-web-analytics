<?php

namespace Nafiur\WebAnalytics\Services;

use Nafiur\WebAnalytics\Models\LwaLogs;

class PruneService
{
    /**
     * Delete telemetry rows older than the retention window.
     *
     * @return int Number of deleted rows.
     */
    public function prune(int $days, ?string $tenantId = null): int
    {
        $days = max(1, $days);

        $query = LwaLogs::where('created_at', '<', now()->subDays($days));

        if ($tenantId !== null && $tenantId !== '') {
            $query->where('tenant_id', $tenantId);
        }

        return (int) $query->delete();
    }
}
