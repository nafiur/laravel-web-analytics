<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Services\PreflightService;

class HealthController extends Controller
{
    public function __construct(private PreflightService $preflight)
    {
    }

    public function index(): View
    {
        if (! config('laravel-web-analytics.health.enabled', false)) {
            abort(404);
        }

        $report = $this->preflight->run();

        return view('web-analytics::pages.health', [
            'section' => 'health',
            'report'  => $report,
        ]);
    }
}
