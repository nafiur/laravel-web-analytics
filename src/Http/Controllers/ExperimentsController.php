<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Models\Analytics\Experiment;
use Nafiur\WebAnalytics\Models\Analytics\FeatureFlag;
use Nafiur\WebAnalytics\Services\ExperimentService;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class ExperimentsController extends Controller
{
    public function __construct(private ExperimentService $experiments)
    {
    }

    public function index(Request $request, ?Experiment $experiment = null): View
    {
        if (! config('laravel-web-analytics.flags.enabled', false)) {
            abort(404);
        }

        [$websiteId, $tenantId] = $this->scope();
        $ready = Schema::hasTable('lwa_experiments') && Schema::hasTable('lwa_events');
        $range = (string) $request->input('range', '30d');
        if (! in_array($range, ['7d', '30d', '90d', 'all'], true)) {
            $range = '30d';
        }

        $result = $ready
            ? $this->experiments->results($websiteId, $tenantId, $range, $experiment?->id)
            : ['range' => $range, 'experiments' => [], 'definitions' => []];

        return view('web-analytics::pages.experiments', [
            'section'     => 'experiments',
            'ready'       => $ready,
            'result'      => $result,
            'range'       => $range,
            'experiments' => $ready ? Experiment::query()->forWebsite($websiteId)->forTenant($tenantId)->orderBy('name')->get() : [],
            'flags'       => $ready ? FeatureFlag::query()->forWebsite($websiteId)->forTenant($tenantId)->orderBy('key')->get() : [],
            'editing'     => $experiment,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$websiteId, $tenantId] = $this->scope();
        $data = $this->normalise($request, $websiteId, $tenantId);
        if ($data === null) {
            return redirect()->route('lwa_experiments')->with('lwa_error', 'The selected flag does not exist in this scope.');
        }

        Experiment::create($data);

        return redirect()->route('lwa_experiments')->with('lwa_success', 'Experiment created.');
    }

    public function update(Request $request, Experiment $experiment): RedirectResponse
    {
        [$websiteId, $tenantId] = $this->scope();
        $this->assertScoped($experiment, $websiteId, $tenantId);
        $data = $this->normalise($request, $websiteId, $tenantId);
        if ($data === null) {
            return redirect()->route('lwa_experiments')->with('lwa_error', 'The selected flag does not exist in this scope.');
        }

        $experiment->fill($data);
        $experiment->save();

        return redirect()->route('lwa_experiments')->with('lwa_success', 'Experiment updated.');
    }

    public function destroy(Experiment $experiment): RedirectResponse
    {
        [$websiteId, $tenantId] = $this->scope();
        $this->assertScoped($experiment, $websiteId, $tenantId);
        $experiment->delete();

        return redirect()->route('lwa_experiments')->with('lwa_success', 'Experiment deleted.');
    }

    /** @return array{0: ?int, 1: ?string} */
    private function scope(): array
    {
        $websiteId = app(WebsiteResolver::class)->currentId();
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        return [$websiteId, TenantResolver::resolveTrusted()];
    }

    private function assertScoped(Experiment $experiment, ?int $websiteId, ?string $tenantId): void
    {
        if ((int) $experiment->website_id !== (int) $websiteId || ($experiment->tenant_id ?: null) !== $tenantId) {
            abort(404);
        }
    }

    /** @return array<string, mixed>|null */
    private function normalise(Request $request, ?int $websiteId, ?string $tenantId): ?array
    {
        $data = $request->validate([
            'name'             => 'required|string|max:150',
            'flag_key'         => 'required|string|max:100',
            'goal_event'       => 'required|string|max:100',
            'variant_property' => 'sometimes|string|max:100',
            'control_variant'  => 'sometimes|string|max:100',
            'status'           => 'required|in:draft,running,stopped',
        ]);

        $exists = FeatureFlag::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId)
            ->where('key', $data['flag_key'])
            ->exists();
        if (! $exists) {
            return null;
        }

        return [
            'website_id'       => $websiteId,
            'tenant_id'        => $tenantId,
            'name'             => $data['name'],
            'flag_key'         => $data['flag_key'],
            'goal_event'       => $data['goal_event'],
            'variant_property' => $data['variant_property'] ?? 'variant',
            'control_variant'  => $data['control_variant'] ?? 'control',
            'status'           => $data['status'],
            'started_at'       => $data['status'] === 'running' ? now() : null,
            'ended_at'         => $data['status'] === 'stopped' ? now() : null,
        ];
    }
}
