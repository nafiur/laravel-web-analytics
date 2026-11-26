<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Nafiur\WebAnalytics\Services\PerformanceService;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class PerformanceController extends Controller
{
    public function __construct(private PerformanceService $performance)
    {
    }

    public function index(Request $request): View
    {
        if (! config('laravel-web-analytics.observability.enabled', false)) {
            abort(404);
        }
        Gate::authorize((string) config('laravel-web-analytics.observability.gate', 'viewLwaObservability'));

        $websiteId = app(WebsiteResolver::class)->currentId();
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);
        $tenantId = TenantResolver::resolveTrusted();

        $range = (string) $request->input('range', '30d');
        if (! in_array($range, ['7d', '30d', '90d', 'all'], true)) {
            $range = '30d';
        }
        $traceId = trim((string) $request->input('trace', ''));
        $traceId = $traceId === '' ? null : mb_substr($traceId, 0, 64);

        return view('web-analytics::pages.performance-traces', [
            'section'    => 'performance-traces',
            'result'     => $this->performance->summarize($websiteId, $tenantId, $range),
            'range'      => $range,
            'trace'      => $traceId,
            'traceData'  => $traceId !== null ? $this->performance->trace($traceId, $websiteId, $tenantId) : null,
        ]);
    }
}
