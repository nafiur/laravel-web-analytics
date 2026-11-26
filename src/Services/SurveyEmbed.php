<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Models\Analytics\Survey;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Throwable;

/** Renders the opt-in survey widget bootstrap via @lwaSurvey('key'). */
class SurveyEmbed
{
    public function render(string $key): string
    {
        if (! config('laravel-web-analytics.surveys.enabled', false)) {
            return '';
        }

        try {
            $websiteId = app(WebsiteResolver::class)->currentId();
            $tenantId = TenantResolver::resolveTrusted();
            $survey = Survey::query()
                ->forWebsite($websiteId)
                ->forTenant($tenantId)
                ->where('key', $key)
                ->where('status', 'running')
                ->first();
            if ($survey === null) {
                return '';
            }

            $path = request()->path();
            if ($survey->target_path !== null && $survey->target_path !== '' && $survey->target_path !== $path) {
                return '';
            }

            $subject = (string) (auth()->id() ?: session()->get('lwa_survey_subject') ?: Str::random(32));
            session()->put('lwa_survey_subject', $subject);

            $bundle = Route::has('lwa_survey_bundle') ? route('lwa_survey_bundle') : null;
            if ($bundle === null) {
                return '';
            }

            $config = [
                'key'         => $survey->key,
                'project_key' => (string) (Website::find($websiteId)?->tracking_id ?? ''),
                'type'        => $survey->type,
                'question' => $survey->question,
                'subject'  => hash('sha256', $subject),
                'path'     => $path,
                'endpoint' => (string) config('laravel-web-analytics.surveys.endpoint', '/web-analytics/api/v1/surveys/responses'),
            ];

            return '<script>window.LWA_SURVEY_CONFIG=' . json_encode(
                $config,
                JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES,
            ) . ';</script><script defer src="' . e($bundle) . '"></script>';
        } catch (Throwable $e) {
            return '';
        }
    }
}
