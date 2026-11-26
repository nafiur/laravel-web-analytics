<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Nafiur\WebAnalytics\Services\ErrorTrackingService;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class ObservabilityController extends Controller
{
    public function __construct(private ErrorTrackingService $errors)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize();
        [$websiteId, $tenantId] = $this->scope();
        $status = (string) $request->input('status', '');
        if (! in_array($status, ['open', 'resolved', 'ignored', ''], true)) {
            $status = '';
        }

        return view('web-analytics::pages.observability', [
            'section'     => 'observability',
            'groups'      => $this->errors->summarize($websiteId, $tenantId, $status === '' ? null : $status),
            'status'      => $status,
            'gate'        => (string) config('laravel-web-analytics.observability.gate', 'viewLwaObservability'),
        ]);
    }

    public function show(int $errorGroup): View
    {
        $this->authorize();
        [$websiteId, $tenantId] = $this->scope();
        $detail = $this->errors->detail($errorGroup, $websiteId, $tenantId);
        if ($detail === null) {
            abort(404);
        }

        return view('web-analytics::pages.observability-show', [
            'section' => 'observability',
            'detail'  => $detail,
        ]);
    }

    public function status(Request $request, int $errorGroup): RedirectResponse
    {
        $this->authorize();
        [$websiteId, $tenantId] = $this->scope();
        $data = $request->validate(['status' => 'required|in:open,resolved,ignored']);
        if (! $this->errors->setStatus($errorGroup, $data['status'], $websiteId, $tenantId)) {
            abort(404);
        }

        return redirect()->route('lwa_observability')->with('lwa_success', 'Error group status updated.');
    }

    private function authorize(): void
    {
        if (! config('laravel-web-analytics.observability.enabled', false)) {
            abort(404);
        }
        Gate::authorize((string) config('laravel-web-analytics.observability.gate', 'viewLwaObservability'));
    }

    /** @return array{0: ?int, 1: ?string} */
    private function scope(): array
    {
        $websiteId = app(WebsiteResolver::class)->currentId();
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        return [$websiteId, TenantResolver::resolveTrusted()];
    }
}
