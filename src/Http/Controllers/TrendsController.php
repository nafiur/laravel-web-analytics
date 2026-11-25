<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Data\InsightQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Insight;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\AnalyticsWindow;
use Nafiur\WebAnalytics\Services\DatabaseReadiness;
use Nafiur\WebAnalytics\Services\InsightService;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class TrendsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
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

        $websiteId = app(WebsiteResolver::class)->currentId()
            ?? config('laravel-web-analytics.analytics.website_id')
            ?? 0;
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        [$from, $to, $range] = AnalyticsWindow::resolve($filters);
        $filters['range'] = $range;
        $ready = DatabaseReadiness::advancedReady();

        $names = $ready
            ? AnalyticEvent::query()->distinct()->orderBy('name')->pluck('name')->all()
            : [];
        $saved = Schema::hasTable('lwa_insights')
            ? Insight::query()->forWebsite((int) $websiteId)->latest()->limit(50)->get()
            : collect();

        $trend = $ready
            ? app(InsightService::class)->trend(InsightQuery::fromArray([
                'website_id'     => $websiteId,
                'metric'         => $filters['metric'] ?? 'events',
                'name'           => $filters['event'] ?? null,
                'breakdown'      => $filters['breakdown'] ?? null,
                'granularity'    => $filters['granularity'] ?? 'day',
                'from'           => $from,
                'to'             => $to,
                'property_key'   => $filters['property_key'] ?? null,
                'property_value' => $filters['property_value'] ?? null,
            ]))
            : [
                'labels' => [], 'series' => [], 'totals' => [], 'global_total' => 0,
                'granularity' => $filters['granularity'] ?? 'day', 'sample_size' => 0,
                'capped' => false, 'definitions' => [], 'range' => ['from' => $from, 'to' => $to],
            ];

        return view('web-analytics::pages.trends', [
            'section'       => 'trends',
            'filters'       => $filters,
            'trend'         => $trend,
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

    /**
     * Saved reports are DB-backed, never stored in localStorage, so they are
     * shared by everyone with access to the dashboard and the active website.
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'name'   => 'required|string|max:150',
            'config' => 'required|string|max:2000',
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

        return back()->with('status', 'Report saved.');
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

        return back()->with('status', 'Report deleted.');
    }

    /** @param array<string, mixed> $config */
    private function normalizeConfig(array $config): array
    {
        $allowed = [
            'event', 'metric', 'breakdown', 'granularity',
            'range', 'start_date', 'end_date', 'property_key', 'property_value',
        ];

        return array_intersect_key($config, array_flip($allowed));
    }
}
