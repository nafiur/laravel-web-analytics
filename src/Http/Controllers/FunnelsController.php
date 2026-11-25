<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Data\FunnelQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Insight;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\AnalyticsWindow;
use Nafiur\WebAnalytics\Services\DatabaseReadiness;
use Nafiur\WebAnalytics\Services\FunnelService;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class FunnelsController extends Controller
{
    /** @return array<string, string> */
    private const DEFAULT_STEPS = ['view_item', 'add_to_cart', 'purchase'];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'steps'          => 'sometimes|array|min:2|max:10',
            'steps.*'        => 'sometimes|string|max:100',
            'window'         => 'sometimes|string|in:' . implode(',', FunnelQuery::WINDOWS),
            'range'          => 'sometimes|string|in:' . implode(',', AnalyticsWindow::RANGES),
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date',
            'property_key'   => 'sometimes|string|max:100',
            'property_value' => 'sometimes|string|max:255',
        ]);

        $steps = array_values(array_filter(
            $filters['steps'] ?? self::DEFAULT_STEPS,
            fn ($step): bool => is_string($step) && $step !== '',
        ));
        $filters['steps'] = $steps;

        $websiteId = app(WebsiteResolver::class)->currentId()
            ?? config('laravel-web-analytics.analytics.website_id')
            ?? 0;
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        [$from, $to, $range] = AnalyticsWindow::resolve($filters);
        $filters['range'] = $range;
        $filters['window'] = $filters['window'] ?? '7d';
        $ready = DatabaseReadiness::advancedReady();

        $names = $ready
            ? AnalyticEvent::query()->distinct()->orderBy('name')->pluck('name')->all()
            : [];
        $saved = Schema::hasTable('lwa_insights')
            ? Insight::query()->forWebsite((int) $websiteId)->latest()->limit(100)->get()
                ->filter(fn (Insight $report): bool => ($report->config['type'] ?? 'trends') === 'funnel')
            : collect();

        $funnel = $ready
            ? app(FunnelService::class)->funnel(FunnelQuery::fromArray([
                'website_id'     => $websiteId,
                'steps'          => $steps,
                'window'         => $filters['window'],
                'from'           => $from,
                'to'             => $to,
                'property_key'   => $filters['property_key'] ?? null,
                'property_value' => $filters['property_value'] ?? null,
            ]))
            : [
                'steps' => [], 'entered_users' => 0, 'completed_users' => 0,
                'overall_conversion_rate' => 0.0, 'median_seconds' => null,
                'sample_size' => 0, 'capped' => false, 'window' => $filters['window'],
                'definitions' => [],
            ];

        return view('web-analytics::pages.funnels', [
            'section'       => 'funnels',
            'filters'       => $filters,
            'funnel'        => $funnel,
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

        return back()->with('status', 'Funnel saved.');
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

        return back()->with('status', 'Funnel deleted.');
    }

    /** @param array<string, mixed> $config */
    private function normalizeConfig(array $config): array
    {
        $allowed = [
            'type', 'steps', 'window',
            'range', 'start_date', 'end_date', 'property_key', 'property_value',
        ];

        return array_intersect_key($config, array_flip($allowed));
    }
}
