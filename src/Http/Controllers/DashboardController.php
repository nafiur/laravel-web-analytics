<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Models\Analytics\Dashboard;
use Nafiur\WebAnalytics\Services\DatabaseTrafficAnalytics;
use Nafiur\WebAnalytics\Services\DatabaseReadiness;
use Nafiur\WebAnalytics\Services\DashboardInsights;
use Nafiur\WebAnalytics\Services\DashboardWidgets;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (! DatabaseReadiness::anyReady()) {
            return view('web-analytics::setup', [
                'instructions' => DatabaseReadiness::setupInstructions(),
            ]);
        }

        $dashboards = $this->scopedQuery($request)->latest()->get();
        $templates = DashboardWidgets::templates();

        return view('web-analytics::pages.dashboards', [
            'dashboards' => $dashboards,
            'templates'  => $templates,
            'section'    => 'dashboards',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:150',
            'template' => 'sometimes|nullable|string|max:50',
            'widgets'  => 'sometimes|array|max:50',
        ]);

        $template = (string) ($data['template'] ?? '');
        $widgets = $data['widgets'] ?? [];

        if ($template !== '' && empty($widgets) && isset(DashboardWidgets::templates()[$template])) {
            $widgets = array_map(
                fn (string $id): array => ['widget' => $id, 'w' => 6, 'h' => 1, 'title' => ''],
                DashboardWidgets::templates()[$template]['widgets']
            );
        }

        $values = [
            'name'      => $data['name'],
            'template'  => $template !== '' ? $template : null,
            'widgets'   => array_values($widgets),
            'settings'  => ['range' => $request->input('range', '24h')],
            'created_by'=> $this->createdBy(),
        ];
        $this->applyScope($request, $values);

        $dashboard = Dashboard::create($values);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'id' => $dashboard->id], 201);
        }

        return redirect()->route('lwa_dashboard_view', $dashboard->id);
    }

    public function show(Request $request, int $dashboard)
    {
        return $this->render($request, $dashboard, false);
    }

    public function edit(Request $request, int $dashboard)
    {
        return $this->render($request, $dashboard, true);
    }

    public function update(Request $request, int $dashboard): JsonResponse
    {
        $model = $this->find($request, $dashboard);

        $data = $request->validate([
            'name'     => 'sometimes|required|string|max:150',
            'widgets'  => 'sometimes|array|max:50',
            'settings' => 'sometimes|array',
        ]);

        $model->fill($data);
        $model->save();

        return response()->json(['success' => true, 'dashboard' => $model->fresh()]);
    }

    public function destroy(Request $request, int $dashboard): JsonResponse
    {
        $this->find($request, $dashboard)->delete();

        return response()->json(['success' => true]);
    }

    protected function render(Request $request, int $id, bool $editing)
    {
        $dashboard = $this->find($request, $id);
        $timeRange = $request->input('range', $dashboard->settings['range'] ?? '24h');
        $tenantId = $this->tenantId();

        $analytics = (new DatabaseTrafficAnalytics())->getDashboardData(
            $timeRange,
            $request->input('start_date'),
            $request->input('end_date'),
            $tenantId
        );

        $catalog = DashboardWidgets::catalog();
        $templates = DashboardWidgets::templates();

        // Saved reports are resolved in the dashboard's own website/tenant
        // scope. Each widget result comes from the same engine as the full
        // report page; at most DashboardInsights::MAX_WIDGETS are computed.
        $insights = app(DashboardInsights::class);
        $websiteId = app(WebsiteResolver::class)->currentId();
        $insightReports = $insights->reports($websiteId, $tenantId);
        $insightResults = $insights->results(
            $websiteId,
            $tenantId,
            $dashboard->widgets ?? [],
            (string) $timeRange,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return view('web-analytics::pages.dashboard-view', [
            'dashboard'      => $dashboard,
            'analytics'      => $analytics,
            'timeRange'      => $timeRange,
            'editing'        => $editing,
            'catalog'        => $catalog,
            'templates'      => $templates,
            'insightReports' => $insightReports,
            'insightResults' => $insightResults,
            'section'        => 'dashboards',
        ]);
    }

    protected function scopedQuery(Request $request)
    {
        $query = Dashboard::query();
        $websiteId = app(WebsiteResolver::class)->currentId();
        $query->when($websiteId !== null, fn ($q) => $q->where('website_id', (int) $websiteId),
            fn ($q) => $q->whereNull('website_id'));

        $tenantId = $this->tenantId();
        $query->when($tenantId !== null, fn ($q) => $q->where('tenant_id', $tenantId),
            fn ($q) => $q->whereNull('tenant_id'));

        return $query;
    }

    protected function applyScope(Request $request, array &$values): void
    {
        $websiteId = app(WebsiteResolver::class)->currentId();
        $values['website_id'] = $websiteId !== null ? (int) $websiteId : null;
        $values['tenant_id'] = $this->tenantId();
    }

    protected function find(Request $request, int $id): Dashboard
    {
        return $this->scopedQuery($request)->findOrFail($id);
    }

    protected function tenantId(): ?string
    {
        return TenantResolver::isEnabled() ? TenantResolver::resolveTrusted() : null;
    }

    protected function createdBy(): ?string
    {
        try {
            $id = auth()->id();
        } catch (\Throwable $e) {
            return null;
        }

        return $id === null ? null : substr((string) $id, 0, 64);
    }
}
