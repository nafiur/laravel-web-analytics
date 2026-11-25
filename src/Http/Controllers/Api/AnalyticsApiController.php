<?php

namespace Nafiur\WebAnalytics\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Facades\Analytics;
use Nafiur\WebAnalytics\Models\Analytics\Dashboard;
use Nafiur\WebAnalytics\Models\Analytics\UserProfile;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\DashboardWidgets;
use Nafiur\WebAnalytics\Services\IdentityService;
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
            'widgets'  => 'sometimes|array',
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
            'widgets'  => 'sometimes|array',
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
