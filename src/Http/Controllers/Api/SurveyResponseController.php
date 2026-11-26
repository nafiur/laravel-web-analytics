<?php

namespace Nafiur\WebAnalytics\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Models\Analytics\Survey;
use Nafiur\WebAnalytics\Services\SurveyService;
use Nafiur\WebAnalytics\Services\TenantResolver;

class SurveyResponseController extends Controller
{
    public function __construct(private SurveyService $surveys)
    {
    }

    public function store(Request $request): JsonResponse
    {
        if (! config('laravel-web-analytics.surveys.enabled', false)) {
            abort(404);
        }

        $data = $request->validate([
            'key'         => 'required|string|max:100',
            'score'       => 'sometimes|nullable|integer|min:0|max:10',
            'answer'      => 'sometimes|nullable|string|max:20000',
            'subject_key' => 'sometimes|nullable|string|max:150',
            'path'        => 'sometimes|nullable|string|max:500',
        ]);

        $websiteId = (int) ($request->attributes->get('lwa_website_id') ?: 0);
        if ($websiteId <= 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $tenantId = TenantResolver::resolveTrusted();

        $survey = Survey::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId)
            ->where('key', $data['key'])
            ->first();
        if ($survey === null) {
            return response()->json(['success' => false, 'message' => 'Survey not found'], 404);
        }

        $result = $this->surveys->storeResponse($survey, $data, $websiteId, $tenantId);
        if (! $result['ok']) {
            return response()->json($result, (int) ($result['status'] ?? 422));
        }

        return response()->json(array_merge(['success' => true], $result), 202);
    }
}
