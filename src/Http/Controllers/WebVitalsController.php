<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;
use Nafiur\WebAnalytics\Services\WebVitalsService;

class WebVitalsController extends Controller
{
    public function __construct(private WebVitalsService $vitals)
    {
    }

    public function index(Request $request): View
    {
        if (! config('laravel-web-analytics.web_vitals.enabled', false)) {
            abort(404);
        }

        $websiteId = app(WebsiteResolver::class)->currentId();
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        $range = (string) $request->input('range', '30d');
        if (! in_array($range, ['7d', '30d', '90d', 'all'], true)) {
            $range = '30d';
        }
        $path = trim((string) $request->input('path', ''));
        $path = $path === '' ? null : mb_substr($path, 0, 500);

        $result = $this->vitals->summarize($websiteId, $range, $path);
        $paths = array_values(array_unique(array_column($this->vitals->summarize($websiteId, $range)['paths'], 'path')));

        return view('web-analytics::pages.web-vitals', [
            'section' => 'web-vitals',
            'result'  => $result,
            'range'   => $range,
            'path'    => $path,
            'paths'   => $paths,
            'tenant'  => TenantResolver::resolveTrusted(),
        ]);
    }
}
