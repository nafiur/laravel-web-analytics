<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Data\RetentionQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Insight;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\AnalyticsWindow;
use Nafiur\WebAnalytics\Services\DatabaseReadiness;
use Nafiur\WebAnalytics\Services\RetentionService;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class RetentionController extends Controller
{
    public function index(Request $request)
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

        $websiteId = app(WebsiteResolver::class)->currentId()
            ?? config('laravel-web-analytics.analytics.website_id')
            ?? 0;
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        // Retention needs enough history to show more than one full bucket.
        $filters['range'] = $filters['range'] ?? '30d';
        [$from, $to, $range] = AnalyticsWindow::resolve($filters);
        $filters['range'] = $range;
        $filters['granularity'] = $filters['granularity'] ?? 'day';
        $filters['periods'] = (int) ($filters['periods'] ?? 7);
        $ready = DatabaseReadiness::advancedReady();

        $names = $ready
            ? AnalyticEvent::query()->distinct()->orderBy('name')->pluck('name')->all()
            : [];
        $saved = Schema::hasTable('lwa_insights')
            ? Insight::query()->forWebsite((int) $websiteId)->latest()->limit(100)->get()
                ->filter(fn (Insight $report): bool => ($report->config['type'] ?? 'trends') === 'retention')
            : collect();

        $retention = $ready
            ? app(RetentionService::class)->retention(RetentionQuery::fromArray([
                'website_id'     => $websiteId,
                'event'          => $filters['event'] ?? null,
                'granularity'    => $filters['granularity'],
                'periods'        => $filters['periods'],
                'from'           => $from,
                'to'             => $to,
                'property_key'   => $filters['property_key'] ?? null,
                'property_value' => $filters['property_value'] ?? null,
            ]))
            : [
                'granularity' => $filters['granularity'],
                'periods'     => range(0, $filters['periods'] - 1),
                'cohorts'     => [], 'lifecycle' => [], 'definitions' => [],
                'summary'     => [
                    'cohorts' => 0, 'cohorted_users' => 0, 'period_one_users' => 0,
                    'period_one_rate' => null, 'period_one_cohorts' => 0,
                ],
                'sample_size' => 0, 'capped' => false, 'cutoff' => null,
                'range' => ['from' => $from, 'to' => $to],
            ];

        return view('web-analytics::pages.retention', [
            'section'       => 'retention',
            'filters'       => $filters,
            'retention'     => $retention,
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

        return back()->with('status', 'Retention report saved.');
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

        return back()->with('status', 'Retention report deleted.');
    }

    /** @param array<string, mixed> $config */
    private function normalizeConfig(array $config): array
    {
        $allowed = [
            'type', 'event', 'granularity', 'periods',
            'range', 'start_date', 'end_date', 'property_key', 'property_value',
        ];

        return array_intersect_key($config, array_flip($allowed));
    }
}
