<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class ReplayController extends Controller
{
    public function index(): View
    {
        if (! config('laravel-web-analytics.replay.enabled', false)) {
            abort(404);
        }

        $websiteId = app(WebsiteResolver::class)->currentId();
        $tenantId = TenantResolver::resolveTrusted();

        $sessions = ReplaySession::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId)
            ->withCount('chunks')
            ->latest('started_at')
            ->limit(50)
            ->get();

        return view('web-analytics::pages.replay', [
            'section'        => 'replay',
            'sessions'       => $sessions,
            'retentionDays'  => (int) config('laravel-web-analytics.replay.retention_days', 30),
            'replayGate'     => (string) config('laravel-web-analytics.replay.gate', 'viewLwaReplay'),
        ]);
    }
}
