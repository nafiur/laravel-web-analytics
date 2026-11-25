<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Data\PathQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Insight;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\AnalyticsWindow;
use Nafiur\WebAnalytics\Services\DatabaseReadiness;
use Nafiur\WebAnalytics\Services\PathService;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class PathsController extends Controller
{
    public function index(Request $request)
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

        $websiteId = app(WebsiteResolver::class)->currentId()
            ?? config('laravel-web-analytics.analytics.website_id')
            ?? 0;
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        [$from, $to, $range] = AnalyticsWindow::resolve($filters);
        $filters['range'] = $range;
        $filters['depth'] = (int) ($filters['depth'] ?? 5);
        $filters['limit'] = (int) ($filters['limit'] ?? 20);
        $ready = DatabaseReadiness::advancedReady();

        $names = $ready
            ? AnalyticEvent::query()->distinct()->orderBy('name')->pluck('name')->all()
            : [];
        $saved = Schema::hasTable('lwa_insights')
            ? Insight::query()->forWebsite((int) $websiteId)->latest()->limit(100)->get()
                ->filter(fn (Insight $report): bool => ($report->config['type'] ?? 'trends') === 'paths')
            : collect();

        $paths = $ready
            ? app(PathService::class)->paths(PathQuery::fromArray([
                'website_id'     => $websiteId,
                'start_event'    => $filters['start_event'] ?? null,
                'end_event'      => $filters['end_event'] ?? null,
                'depth'          => $filters['depth'],
                'limit'          => $filters['limit'],
                'from'           => $from,
                'to'             => $to,
                'property_key'   => $filters['property_key'] ?? null,
                'property_value' => $filters['property_value'] ?? null,
            ]))
            : [
                'paths' => [], 'positions' => [], 'definitions' => [],
                'summary' => [
                    'sequenced_users' => 0, 'single_step_users' => 0, 'excluded_users' => 0,
                    'paths_found' => 0, 'paths_shown' => 0, 'top_path_share' => null,
                ],
                'depth' => $filters['depth'], 'sample_size' => 0, 'capped' => false,
                'cutoff' => null, 'range' => ['from' => $from, 'to' => $to],
            ];

        return view('web-analytics::pages.paths', [
            'section'       => 'paths',
            'filters'       => $filters,
            'paths'         => $paths,
            'names'         => $names,
            'saved'         => $saved,
            'ready'         => $ready,
            'websites'      => Website::orderBy('domain')->get(),
            'activeWebsite' => Website::find($websiteId),
            'timeRange'     => $range,
            'source'        => 'database',
            'tenantId'      => $request->input('tenant_id'),
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:150',
            'config' => 'required|string|max:4000',
        ]);

        $config = json_decode($data['config'], true);
        if (! is_array($config)) {
            return back()->withErrors(['config' => 'Invalid report configuration.']);
        }

        $websiteId = app(WebsiteResolver::class)->currentId()
            ?? config('laravel-web-analytics.analytics.website_id')
            ?? 0;

        Insight::create([
            'name'       => $data['name'],
            'config'     => $this->normalizeConfig($config),
            'website_id' => (int) $websiteId ?: null,
            'tenant_id'  => is_string($request->input('tenant_id')) ? $request->input('tenant_id') : null,
        ]);

        return back()->with('status', 'Path report saved.');
    }

    public function destroy(Request $request, int $insight)
    {
        $websiteId = app(WebsiteResolver::class)->currentId()
            ?? config('laravel-web-analytics.analytics.website_id')
            ?? 0;

        Insight::query()
            ->forWebsite((int) $websiteId)
            ->whereKey($insight)
            ->delete();

        return back()->with('status', 'Path report deleted.');
    }

    /** @param array<string, mixed> $config */
    private function normalizeConfig(array $config): array
    {
        $allowed = [
            'type', 'start_event', 'end_event', 'depth', 'limit',
            'range', 'start_date', 'end_date', 'property_key', 'property_value',
        ];

        return array_intersect_key($config, array_flip($allowed));
    }
}
