<?php

namespace Nafiur\WebAnalytics\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Services\FeatureFlagService;
use Nafiur\WebAnalytics\Services\TenantResolver;

class FlagEvaluateController extends Controller
{
    public function __construct(private FeatureFlagService $flags)
    {
    }

    public function evaluate(Request $request): JsonResponse
    {
        if (! config('laravel-web-analytics.flags.enabled', false)) {
            abort(404);
        }

        $data = $request->validate([
            'key'        => 'required|string|max:100',
            'subject'    => 'sometimes|string|max:150',
            'attributes' => 'sometimes|string|max:4000',
        ]);

        $websiteId = (int) ($request->attributes->get('lwa_website_id') ?: config('laravel-web-analytics.analytics.website_id') ?: 0);
        $attributes = [];
        if (! empty($data['attributes'])) {
            $decoded = json_decode((string) $data['attributes'], true);
            $attributes = is_array($decoded) ? $decoded : [];
        }

        $result = $this->flags->evaluate(
            (string) $data['key'],
            isset($data['subject']) ? (string) $data['subject'] : null,
            $attributes,
            $websiteId > 0 ? $websiteId : null,
            TenantResolver::resolveTrusted(),
        );

        return response()->json([
            'success' => true,
            'flag'    => $result,
            'source'  => 'lwa_feature_flags; evaluation logged to lwa_events as feature_flag_evaluated',
        ])->header('Cache-Control', 'no-store');
    }
}
