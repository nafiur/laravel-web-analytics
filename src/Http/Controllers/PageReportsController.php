<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Services\PageReportsService;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class PageReportsController extends Controller
{
    public function __construct(private PageReportsService $reports)
    {
    }

    public function index(Request $request): View
    {
        if (! config('laravel-web-analytics.page_reports.enabled', false)) {
            abort(404);
        }

        $range = (string) $request->input('range', '30d');
        if (! in_array($range, ['7d', '30d', '90d', 'all'], true)) {
            $range = '30d';
        }
        $path = trim((string) $request->input('path', ''));
        $path = $path === '' ? null : mb_substr($path, 0, 500);

        $result = $this->reports->report(
            app(WebsiteResolver::class)->currentId(),
            TenantResolver::resolveTrusted(),
            $range,
            $path,
            $request->boolean('compare'),
        );

        return view('web-analytics::pages.page-reports', [
            'section' => 'page-reports',
            'result'  => $result,
            'range'   => $range,
            'path'    => $path,
        ]);
    }
}
