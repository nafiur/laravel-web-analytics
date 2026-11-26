<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;
use Nafiur\WebAnalytics\Services\HeatmapService;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class HeatmapsController extends Controller
{
    public function __construct(private HeatmapService $heatmaps)
    {
    }

    public function index(Request $request): View
    {
        if (! config('laravel-web-analytics.replay.enabled', false)) {
            abort(404);
        }

        Gate::authorize((string) config('laravel-web-analytics.replay.gate', 'viewLwaReplay'));

        $ready = Schema::hasTable('lwa_replay_markers') && Schema::hasTable('lwa_replay_sessions');
        $websiteId = app(WebsiteResolver::class)->currentId();
        $tenantId = TenantResolver::resolveTrusted();

        $range = (string) $request->input('range', '30d');
        if (! in_array($range, ['7d', '30d', '90d', 'all'], true)) {
            $range = '30d';
        }
        $path = trim((string) $request->input('path', ''));
        $path = $path === '' ? null : mb_substr($path, 0, 500);

        $result = $ready
            ? $this->heatmaps->aggregate($websiteId, $tenantId, $path, $range)
            : [
                'range' => $range, 'path' => $path, 'sample_size' => 0, 'capped' => false,
                'clicks' => 0, 'scrolls' => 0, 'sessions' => 0, 'points' => [], 'targets' => [],
                'scroll' => array_fill(0, 11, 0), 'rage' => [], 'dead' => [], 'definitions' => [],
            ];

        $paths = $ready
            ? ReplaySession::query()
                ->forWebsite($websiteId)
                ->forTenant($tenantId)
                ->whereNotNull('url')
                ->distinct()
                ->orderBy('url')
                ->limit(100)
                ->pluck('url')
                ->all()
            : [];

        return view('web-analytics::pages.heatmaps', [
            'section'  => 'heatmaps',
            'ready'    => $ready,
            'result'   => $result,
            'range'    => $range,
            'path'     => $path,
            'paths'    => $paths,
        ]);
    }
}
