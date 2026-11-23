<?php

namespace Nafiur\WebAnalytics\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Facades\Analytics;
use Nafiur\WebAnalytics\Models\Analytics\Website;

class AnalyticsApiController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'range'      => 'sometimes|string|in:1h,6h,24h,today,7d,30d,all,custom',
            'start_date' => 'sometimes|date',
            'end_date'   => 'sometimes|date',
        ]);

        return response()->json([
            'success' => true,
            'data'    => Analytics::summary(
                $request->input('range', '24h'),
                $this->websiteId($request),
                $request->input('start_date'),
                $request->input('end_date'),
            ),
        ]);
    }

    public function timeseries(Request $request): JsonResponse
    {
        $request->validate([
            'range'      => 'sometimes|string|in:1h,6h,24h,today,7d,30d,all,custom',
            'start_date' => 'sometimes|date',
            'end_date'   => 'sometimes|date',
        ]);

        return response()->json([
            'success' => true,
            'data'    => Analytics::timeseries(
                $request->input('range', '24h'),
                $this->websiteId($request),
                $request->input('start_date'),
                $request->input('end_date'),
            ),
        ]);
    }

    public function event(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'properties' => 'sometimes|array',
            'value'      => 'sometimes|numeric|nullable',
        ]);

        Analytics::event(
            $data['name'],
            $data['properties'] ?? [],
            isset($data['value']) ? (float) $data['value'] : null,
            ['website_id' => $this->websiteId($request)],
        );

        return response()->json(['success' => true, 'message' => 'Event accepted'], 202);
    }

    public function websites(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => Website::select('id', 'name', 'domain', 'tracking_id')->get(),
        ]);
    }

    private function websiteId(Request $request): ?int
    {
        $token = $request->attributes->get('lwa_api_token');

        return $token ? (int) $token->website_id : null;
    }
}
