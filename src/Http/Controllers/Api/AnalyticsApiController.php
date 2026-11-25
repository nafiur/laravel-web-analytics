<?php

namespace Nafiur\WebAnalytics\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\FunnelQuery;
use Nafiur\WebAnalytics\Data\InsightQuery;
use Nafiur\WebAnalytics\Data\PathQuery;
use Nafiur\WebAnalytics\Data\RetentionQuery;
use Nafiur\WebAnalytics\Facades\Analytics;
use Nafiur\WebAnalytics\Models\Analytics\Dashboard;
use Nafiur\WebAnalytics\Models\Analytics\Insight;
use Nafiur\WebAnalytics\Models\Analytics\UserProfile;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\AnalyticsWindow;
use Nafiur\WebAnalytics\Services\DashboardWidgets;
use Nafiur\WebAnalytics\Services\FunnelService;
use Nafiur\WebAnalytics\Services\IdentityService;
use Nafiur\WebAnalytics\Services\InsightService;
use Nafiur\WebAnalytics\Services\PathService;
use Nafiur\WebAnalytics\Services\RetentionService;
use Nafiur\WebAnalytics\Services\UnifiedEventMapper;

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

    /**
     * Batch ingestion endpoint (v3.2): accept up to N normalized events in a
     * single request, validate, dedupe on uuid and persist through the
     * EventStore. Malformed payloads are rejected with 422 before anything is
     * written.
     */
    public function storeBatch(Request $request): JsonResponse
    {
        $data = $request->validate([
            'events'              => 'required|array|min:1|max:500',
            'events.*.uuid'       => 'sometimes|string|max:64',
            'events.*.name'       => 'required|string|max:100',
            'events.*.properties' => 'sometimes|array',
            'events.*.value'      => 'sometimes|numeric|nullable',
            'events.*.timestamp'  => 'sometimes|date',
            'events.*.distinct_id' => 'sometimes|string|max:100|nullable',
            'events.*.anonymous_id' => 'sometimes|string|max:100|nullable',
            'events.*.session_id' => 'sometimes|string|max:100|nullable',
            'events.*.context'    => 'sometimes|array',
        ]);

        $websiteId = $this->websiteId($request) ?? 0;
        $events = [];
        $mapper = app(UnifiedEventMapper::class);

        foreach ($data['events'] as $raw) {
            $events[] = $mapper->mapPayload($raw, $websiteId);
        }

        app(EventStore::class)->insert($events);

        return response()->json(['success' => true, 'inserted' => count($events)], 202);
    }

    public function websites(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => Website::select('id', 'name', 'domain', 'tracking_id')->get(),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $request->validate(['search' => 'sometimes|string|max:100']);

        $query = UserProfile::query();
        $websiteId = $this->websiteId($request);
        if ($websiteId !== null) {
            $query->where('website_id', $websiteId);
        }
        if ($request->filled('search')) {
            $query->where('distinct_id', 'like', '%' . $request->string('search') . '%');
        }

        return response()->json([
            'success' => true,
            'data'    => $query->latest('last_seen_at')->limit(100)->get(),
        ]);
    }

    public function userShow(Request $request, string $distinctId): JsonResponse
    {
        $websiteId = $this->websiteId($request) ?? 0;
        $profile = app(IdentityService::class)->profileFor($websiteId, $distinctId);

        if ($profile === null) {
            return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'profile'  => $profile,
                'aliases'  => $profile->aliases()->pluck('anonymous_id'),
                'timeline' => app(IdentityService::class)->timeline($websiteId, $profile->distinct_id),
            ],
        ]);
    }

    public function dashboards(Request $request): JsonResponse
    {
        $query = Dashboard::query();
        $websiteId = $this->websiteId($request);
        if ($websiteId !== null) {
            $query->where('website_id', $websiteId);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->latest()->get(),
        ]);
    }

    public function dashboardStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'template' => 'sometimes|nullable|string|max:50',
            'widgets'  => 'sometimes|array|max:50',
            'settings' => 'sometimes|array',
        ]);

        $template = (string) ($data['template'] ?? '');
        $widgets = $data['widgets'] ?? [];
        if ($template !== '' && empty($widgets) && isset(DashboardWidgets::templates()[$template])) {
            $widgets = array_map(
                fn (string $id): array => ['widget' => $id, 'w' => 6, 'h' => 1, 'title' => ''],
                DashboardWidgets::templates()[$template]['widgets']
            );
        }

        $dashboard = Dashboard::create([
            'name'      => $data['name'],
            'template'  => $template !== '' ? $template : null,
            'widgets'   => array_values($widgets),
            'settings'  => $data['settings'] ?? [],
            'website_id'=> $this->websiteId($request),
        ]);

        return response()->json(['success' => true, 'data' => $dashboard], 201);
    }

    public function dashboardShow(Request $request, int $dashboard): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->findDashboard($request, $dashboard),
        ]);
    }

    public function dashboardUpdate(Request $request, int $dashboard): JsonResponse
    {
        $model = $this->findDashboard($request, $dashboard);
        $data = $request->validate([
            'name'     => 'sometimes|required|string|max:150',
            'widgets'  => 'sometimes|array|max:50',
            'settings' => 'sometimes|array',
        ]);
        $model->fill($data);
        $model->save();

        return response()->json(['success' => true, 'data' => $model->fresh()]);
    }

    public function dashboardDestroy(Request $request, int $dashboard): JsonResponse
    {
        $this->findDashboard($request, $dashboard)->delete();

        return response()->json(['success' => true]);
    }

    /**
     * v3.3 Trends: one documented query path shared with the Explorer UI.
     */
    public function trend(Request $request): JsonResponse
    {
        $filters = $this->validateInsightFilters($request);
        [$from, $to] = AnalyticsWindow::resolve($filters);

        $query = InsightQuery::fromArray([
            'website_id'     => $this->websiteId($request) ?? 0,
            'metric'         => $filters['metric'] ?? 'events',
            'name'           => $filters['event'] ?? null,
            'breakdown'      => $filters['breakdown'] ?? null,
            'granularity'    => $filters['granularity'] ?? 'day',
            'from'           => $from,
            'to'             => $to,
            'property_key'   => $filters['property_key'] ?? null,
            'property_value' => $filters['property_value'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => app(InsightService::class)->trend($query),
            'filters' => $filters,
        ]);
    }

    /**
     * v3.3 Funnels: ordered per-user steps with a documented conversion
     * window. Uses the same EventStore read path as Trends.
     */
    public function funnel(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'steps'          => 'required|array|min:2|max:10',
            'steps.*'        => 'required|string|max:100',
            'window'         => 'sometimes|string|in:' . implode(',', FunnelQuery::WINDOWS),
            'range'          => 'sometimes|string|in:' . implode(',', AnalyticsWindow::RANGES),
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date',
            'property_key'   => 'sometimes|string|max:100',
            'property_value' => 'sometimes|string|max:255',
        ]);
        [$from, $to] = AnalyticsWindow::resolve($filters);

        $query = FunnelQuery::fromArray([
            'website_id'     => $this->websiteId($request) ?? 0,
            'steps'          => $filters['steps'],
            'window'         => $filters['window'] ?? '7d',
            'from'           => $from,
            'to'             => $to,
            'property_key'   => $filters['property_key'] ?? null,
            'property_value' => $filters['property_value'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => app(FunnelService::class)->funnel($query),
            'filters' => $filters,
        ]);
    }

    /**
     * v3.3 Retention: cohort retention plus lifecycle stages, read through the
     * same EventStore path as Trends and Funnels.
     */
    public function retention(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'event'          => 'sometimes|string|max:100',
            'granularity'    => 'sometimes|string|in:' . implode(',', RetentionQuery::GRANULARITIES),
            'periods'        => 'sometimes|integer|min:2|max:12',
            'range'          => 'sometimes|string|in:' . implode(',', AnalyticsWindow::RANGES),
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date',
            'property_key'   => 'sometimes|string|max:100',
            'property_value' => 'sometimes|string|max:255',
        ]);

        $filters['range'] = $filters['range'] ?? '30d';
        [$from, $to] = AnalyticsWindow::resolve($filters);

        $query = RetentionQuery::fromArray([
            'website_id'     => $this->websiteId($request) ?? 0,
            'event'          => $filters['event'] ?? null,
            'granularity'    => $filters['granularity'] ?? 'day',
            'periods'        => $filters['periods'] ?? 7,
            'from'           => $from,
            'to'             => $to,
            'property_key'   => $filters['property_key'] ?? null,
            'property_value' => $filters['property_value'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => app(RetentionService::class)->retention($query),
            'filters' => $filters,
        ]);
    }

    /**
     * v3.3 User paths: one per-user event sequence, read through the same
     * EventStore path as Trends, Funnels and Retention.
     */
    public function paths(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'start_event'    => 'sometimes|string|max:100',
            'end_event'      => 'sometimes|string|max:100',
            'depth'          => 'sometimes|integer|min:2|max:8',
            'limit'          => 'sometimes|integer|min:5|max:50',
            'range'          => 'sometimes|string|in:' . implode(',', AnalyticsWindow::RANGES),
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date',
            'property_key'   => 'sometimes|string|max:100',
            'property_value' => 'sometimes|string|max:255',
        ]);

        [$from, $to] = AnalyticsWindow::resolve($filters);

        $query = PathQuery::fromArray([
            'website_id'     => $this->websiteId($request) ?? 0,
            'start_event'    => $filters['start_event'] ?? null,
            'end_event'      => $filters['end_event'] ?? null,
            'depth'          => $filters['depth'] ?? 5,
            'limit'          => $filters['limit'] ?? 20,
            'from'           => $from,
            'to'             => $to,
            'property_key'   => $filters['property_key'] ?? null,
            'property_value' => $filters['property_value'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => app(PathService::class)->paths($query),
            'filters' => $filters,
        ]);
    }

    public function insights(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->insightQuery($request)->latest()->limit(100)->get(),
        ]);
    }

    public function insightStore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'   => 'required|string|max:150',
            'config' => 'required|array',
        ]);

        $insight = Insight::create([
            'name'       => $data['name'],
            'config'     => $this->normalizeInsightConfig($data['config']),
            'website_id' => $this->websiteId($request),
            'tenant_id'  => $request->input('tenant_id'),
        ]);

        return response()->json(['success' => true, 'data' => $insight], 201);
    }

    public function insightShow(Request $request, int $insight): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->findInsight($request, $insight),
        ]);
    }

    public function insightUpdate(Request $request, int $insight): JsonResponse
    {
        $model = $this->findInsight($request, $insight);
        $data = $request->validate([
            'name'   => 'sometimes|required|string|max:150',
            'config' => 'sometimes|array',
        ]);

        $model->fill([
            'name'   => $data['name'] ?? $model->name,
            'config' => isset($data['config']) ? $this->normalizeInsightConfig($data['config']) : $model->config,
        ])->save();

        return response()->json(['success' => true, 'data' => $model->fresh()]);
    }

    public function insightDestroy(Request $request, int $insight): JsonResponse
    {
        $this->findInsight($request, $insight)->delete();

        return response()->json(['success' => true]);
    }

    /** @return array<string, mixed> */
    private function validateInsightFilters(Request $request): array
    {
        return $request->validate([
            'event'          => 'sometimes|string|max:100',
            'metric'         => 'sometimes|string|in:' . implode(',', InsightQuery::METRICS),
            'breakdown'      => 'sometimes|string|max:100',
            'granularity'    => 'sometimes|string|in:' . implode(',', InsightQuery::GRANULARITIES),
            'range'          => 'sometimes|string|in:' . implode(',', AnalyticsWindow::RANGES),
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date',
            'property_key'   => 'sometimes|string|max:100',
            'property_value' => 'sometimes|string|max:255',
        ]);
    }

    /** @param array<string, mixed> $config */
    private function normalizeInsightConfig(array $config): array
    {
        $allowed = [
            'type', 'event', 'metric', 'breakdown', 'granularity',
            'steps', 'window',
            'granularity', 'periods',
            'start_event', 'end_event', 'depth', 'limit',
            'range', 'start_date', 'end_date', 'property_key', 'property_value',
        ];

        return array_intersect_key($config, array_flip($allowed));
    }

    private function insightQuery(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $query = Insight::query()->forWebsite($this->websiteId($request));
        $tenantId = $request->input('tenant_id');

        return $query->forTenant(is_string($tenantId) && $tenantId !== '' ? $tenantId : null);
    }

    private function findInsight(Request $request, int $id): Insight
    {
        return $this->insightQuery($request)->findOrFail($id);
    }

    private function findDashboard(Request $request, int $id): Dashboard
    {
        $query = Dashboard::query();
        $websiteId = $this->websiteId($request);
        if ($websiteId !== null) {
            $query->where('website_id', $websiteId);
        }

        return $query->findOrFail($id);
    }

    private function websiteId(Request $request): ?int
    {
        $token = $request->attributes->get('lwa_api_token');
        if ($token) {
            return (int) $token->website_id;
        }

        $websiteId = $request->attributes->get('lwa_website_id');

        return $websiteId !== null ? (int) $websiteId : null;
    }
}
