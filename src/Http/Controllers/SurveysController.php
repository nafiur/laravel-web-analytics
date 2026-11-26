<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Nafiur\WebAnalytics\Models\Analytics\Survey;
use Nafiur\WebAnalytics\Services\SurveyService;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;

class SurveysController extends Controller
{
    public function __construct(private SurveyService $surveys)
    {
    }

    public function index(Request $request, ?Survey $survey = null): View
    {
        $this->authorize();
        [$websiteId, $tenantId] = $this->scope();

        $range = (string) $request->input('range', '30d');
        if (! in_array($range, ['7d', '30d', '90d', 'all'], true)) {
            $range = '30d';
        }
        if ($survey !== null) {
            $this->assertScoped($survey, $websiteId, $tenantId);
        }

        return view('web-analytics::pages.surveys', [
            'section'  => 'surveys',
            'surveys'  => $this->surveys->all($websiteId, $tenantId),
            'editing'  => $survey,
            'result'   => $survey !== null ? $this->surveys->results($survey, $range) : null,
            'range'    => $range,
            'gate'     => (string) config('laravel-web-analytics.surveys.gate', 'viewLwaSurveys'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize();
        [$websiteId, $tenantId] = $this->scope();
        $result = $this->surveys->save($this->data($request, $websiteId, $tenantId));

        return redirect()->route('lwa_surveys')
            ->with($result['ok'] ? 'lwa_success' : 'lwa_error', $result['ok'] ? 'Survey saved.' : $result['message']);
    }

    public function update(Request $request, Survey $survey): RedirectResponse
    {
        $this->authorize();
        [$websiteId, $tenantId] = $this->scope();
        $this->assertScoped($survey, $websiteId, $tenantId);
        $result = $this->surveys->save($this->data($request, $websiteId, $tenantId), $survey);

        return redirect()->route('lwa_surveys')
            ->with($result['ok'] ? 'lwa_success' : 'lwa_error', $result['ok'] ? 'Survey updated.' : $result['message']);
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $this->authorize();
        [$websiteId, $tenantId] = $this->scope();
        $this->assertScoped($survey, $websiteId, $tenantId);
        $survey->responses()->delete();
        $survey->delete();

        return redirect()->route('lwa_surveys')->with('lwa_success', 'Survey deleted.');
    }

    private function authorize(): void
    {
        if (! config('laravel-web-analytics.surveys.enabled', false)) {
            abort(404);
        }
        Gate::authorize((string) config('laravel-web-analytics.surveys.gate', 'viewLwaSurveys'));
    }

    /** @return array{0: ?int, 1: ?string} */
    private function scope(): array
    {
        $websiteId = app(WebsiteResolver::class)->currentId();
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        return [$websiteId, TenantResolver::resolveTrusted()];
    }

    private function assertScoped(Survey $survey, ?int $websiteId, ?string $tenantId): void
    {
        if ((int) $survey->website_id !== (int) $websiteId || ($survey->tenant_id ?: null) !== $tenantId) {
            abort(404);
        }
    }

    /** @return array<string, mixed> */
    private function data(Request $request, ?int $websiteId, ?string $tenantId): array
    {
        $data = $request->validate([
            'key'         => 'required|string|max:100',
            'name'        => 'required|string|max:150',
            'type'        => 'required|in:nps,text',
            'question'    => 'required|string|max:1000',
            'target_path' => 'sometimes|nullable|string|max:500',
            'status'      => 'sometimes|in:draft,running,stopped',
        ]);

        return [
            'website_id'  => $websiteId,
            'tenant_id'   => $tenantId,
            'key'         => trim((string) $data['key']),
            'name'        => trim((string) $data['name']),
            'type'        => $data['type'],
            'question'    => trim((string) $data['question']),
            'target_path' => $data['target_path'] ?? null,
            'status'      => $data['status'] ?? 'draft',
        ];
    }
}
