<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class ReplayController extends Controller
{
    public function index(Request $request): View
    {
        if (! config('laravel-web-analytics.replay.enabled', false)) {
            abort(404);
        }

        $websiteId = app(WebsiteResolver::class)->currentId();
        $tenantId = TenantResolver::resolveTrusted();
        $path = trim((string) $request->input('path', ''));
        $path = $path === '' ? null : mb_substr($path, 0, 500);

        $query = ReplaySession::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId);
        if ($path !== null) {
            $query->where('url', $path);
        }
        $sessions = $query->withCount('chunks')
            ->latest('started_at')
            ->limit(50)
            ->get();

        return view('web-analytics::pages.replay', [
            'section'        => 'replay',
            'sessions'       => $sessions,
            'path'           => $path,
            'retentionDays'  => (int) config('laravel-web-analytics.replay.retention_days', 30),
            'replayGate'     => (string) config('laravel-web-analytics.replay.gate', 'viewLwaReplay'),
        ]);
    }
}
