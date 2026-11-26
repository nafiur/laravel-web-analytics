<?php

namespace Nafiur\WebAnalytics\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Services\ErrorTrackingService;
use Nafiur\WebAnalytics\Services\TenantResolver;

class ErrorIngestController extends Controller
{
    public function __construct(private ErrorTrackingService $errors)
    {
    }

    public function store(Request $request): JsonResponse
    {
        if (! config('laravel-web-analytics.observability.enabled', false)) {
            abort(404);
        }

        $data = $request->validate([
            'type'     => 'sometimes|string|max:150',
            'message'  => 'required|string|max:5000',
            'file'     => 'sometimes|nullable|string|max:500',
            'line'     => 'sometimes|integer|min:0|max:1000000',
            'severity' => 'sometimes|in:debug,info,warning,error,fatal',
            'stack'    => 'sometimes|nullable|string|max:200000',
            'event_id' => 'sometimes|string|max:64',
            'context'  => 'sometimes|array|max:20',
        ]);

        $websiteId = (int) ($request->attributes->get('lwa_website_id') ?: 0);
        if ($websiteId <= 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $result = $this->errors->ingest($data, $websiteId, TenantResolver::resolveTrusted());

        return response()->json(array_merge(['success' => true], $result), 202);
    }
}
