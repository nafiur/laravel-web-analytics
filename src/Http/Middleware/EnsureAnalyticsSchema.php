<?php

namespace Nafiur\WebAnalytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nafiur\WebAnalytics\Services\DatabaseReadiness;

class EnsureAnalyticsSchema
{
    public function handle(Request $request, Closure $next)
    {
        if (!DatabaseReadiness::advancedReady()) {
            return response()->json([
                'success' => false,
                'message' => 'Advanced analytics schema is not available: '
                    . implode(' ', DatabaseReadiness::setupInstructions()),
            ], 503);
        }

        return $next($request);
    }
}
