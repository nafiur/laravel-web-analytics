<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use Nafiur\WebAnalytics\Models\Analytics\FeatureFlag;
use Nafiur\WebAnalytics\Services\FeatureFlagService;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class FeatureFlagsController extends Controller
{
    public function __construct(private FeatureFlagService $flags)
    {
    }

    public function index(Request $request, ?FeatureFlag $featureFlag = null): View
    {
        if (! config('laravel-web-analytics.flags.enabled', false)) {
            abort(404);
        }

        [$websiteId, $tenantId] = $this->scope();
        $ready = Schema::hasTable('lwa_feature_flags') && Schema::hasTable('lwa_flag_overrides');

        return view('web-analytics::pages.flags', [
            'section'  => 'flags',
            'ready'    => $ready,
            'flags'    => $ready ? $this->flags->all($websiteId, $tenantId) : [],
            'editing'  => $featureFlag,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$websiteId, $tenantId] = $this->scope();
        $data = $this->normalise($request, $websiteId, $tenantId);
        $result = $this->flags->save($data);

        return redirect()->route('lwa_flags')
            ->with($result['ok'] ? 'lwa_success' : 'lwa_error', $result['ok'] ? 'Flag saved.' : $result['message']);
    }

    public function update(Request $request, FeatureFlag $featureFlag): RedirectResponse
    {
        [$websiteId, $tenantId] = $this->scope();
        $this->assertScoped($featureFlag, $websiteId, $tenantId);
        $data = $this->normalise($request, $websiteId, $tenantId);
        $result = $this->flags->save($data, $featureFlag);

        return redirect()->route('lwa_flags')
            ->with($result['ok'] ? 'lwa_success' : 'lwa_error', $result['ok'] ? 'Flag updated.' : $result['message']);
    }

    public function destroy(FeatureFlag $featureFlag): RedirectResponse
    {
        [$websiteId, $tenantId] = $this->scope();
        $this->assertScoped($featureFlag, $websiteId, $tenantId);
        $featureFlag->overrides()->delete();
        $featureFlag->delete();

        return redirect()->route('lwa_flags')->with('lwa_success', 'Flag deleted.');
    }

    /** @return array{0: ?int, 1: ?string} */
    private function scope(): array
    {
        $websiteId = app(WebsiteResolver::class)->currentId();
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        return [$websiteId, TenantResolver::resolveTrusted()];
    }

    private function assertScoped(FeatureFlag $flag, ?int $websiteId, ?string $tenantId): void
    {
        if ((int) $flag->website_id !== (int) $websiteId || ($flag->tenant_id ?: null) !== $tenantId) {
            abort(404);
        }
    }

    /** @return array<string, mixed> */
    private function normalise(Request $request, ?int $websiteId, ?string $tenantId): array
    {
        $data = $request->validate([
            'key'         => 'required|string|max:100',
            'name'        => 'sometimes|string|max:150',
            'description' => 'sometimes|nullable|string|max:1000',
            'type'        => 'required|in:boolean,multivariate',
            'enabled'     => 'sometimes|boolean',
            'rollout'     => 'sometimes|integer|min:0|max:100',
            'variants'    => 'sometimes|nullable|string|max:4000',
            'targeting'   => 'sometimes|nullable|string|max:4000',
        ]);

        $variants = $this->decodeJson($data['variants'] ?? '', []);
        $targeting = $this->decodeJson($data['targeting'] ?? '', []);

        return [
            'website_id'  => $websiteId,
            'tenant_id'   => $tenantId,
            'key'         => trim((string) $data['key']),
            'name'        => (string) ($data['name'] ?? $data['key']),
            'description' => $data['description'] ?? null,
            'type'        => $data['type'],
            'enabled'     => (bool) ($data['enabled'] ?? false),
            'rollout'     => (int) ($data['rollout'] ?? 0),
            'variants'    => $variants,
            'targeting'   => $targeting,
        ];
    }

    /** @return array<int, mixed> */
    private function decodeJson(string $raw, array $fallback): array
    {
        if (trim($raw) === '') {
            return $fallback;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? array_values($decoded) : $fallback;
    }
}
