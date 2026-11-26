<?php

namespace Nafiur\WebAnalytics\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Services\PerformanceService;
use Nafiur\WebAnalytics\Services\TenantResolver;

class SpanIngestController extends Controller
{
    public function __construct(private PerformanceService $performance)
    {
    }

    public function store(Request $request): JsonResponse
    {
        if (! config('laravel-web-analytics.observability.enabled', false)) {
            abort(404);
        }

        $data = $request->validate([
            'spans'                   => 'required|array|min:1|max:100',
            'spans.*.trace_id'        => 'required|string|max:64',
            'spans.*.span_id'         => 'required|string|max:64',
            'spans.*.parent_id'       => 'sometimes|nullable|string|max:64',
            'spans.*.name'            => 'required|string|max:150',
            'spans.*.kind'            => 'sometimes|string|max:30',
            'spans.*.start_ms'        => 'sometimes|integer|min:0',
            'spans.*.duration_ms'     => 'sometimes|integer|min:0',
            'spans.*.status'          => 'sometimes|in:ok,error,fatal',
            'spans.*.has_error'       => 'sometimes|boolean',
            'spans.*.attributes'      => 'sometimes|array|max:20',
        ]);

        $websiteId = (int) ($request->attributes->get('lwa_website_id') ?: 0);
        if ($websiteId <= 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $result = $this->performance->ingest($data['spans'], $websiteId, TenantResolver::resolveTrusted());

        return response()->json(array_merge(['success' => true], $result), 202);
    }
}
