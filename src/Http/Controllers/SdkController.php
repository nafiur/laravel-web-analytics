<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class SdkController extends Controller
{
    /**
     * Serve the packaged JavaScript tracker. Zero build step: the SDK source
     * ships with the package and is served from here. Config defaults are
     * injected ahead of the bundle so env-driven settings are honoured;
     * per-page overrides can still be passed to LwaWebAnalytics.init().
     */
    public function __invoke(): SymfonyResponse
    {
        $path = __DIR__ . '/../../../resources/js/sdk.js';

        if (! config('laravel-web-analytics.sdk.enabled', false) || ! is_file($path)) {
            abort(404);
        }

        $defaults = [
            'endpoint'        => (string) config('laravel-web-analytics.sdk.endpoint', '/web-analytics/api/v1/events/batch'),
            'autocapture'     => (bool) config('laravel-web-analytics.sdk.autocapture', false),
            'web_vitals'      => (bool) config('laravel-web-analytics.sdk.web_vitals', false),
            'consent'         => [
                'analytics' => (bool) config('laravel-web-analytics.sdk.consent.analytics', true),
            ],
            'max_properties'  => (int) config('laravel-web-analytics.sdk.max_properties', 20),
            'max_batch_bytes' => (int) config('laravel-web-analytics.sdk.max_batch_bytes', 65536),
        ];

        $bootstrap = 'window.LWA_SDK_DEFAULTS = ' . json_encode(
            $defaults,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES,
        ) . ';' . PHP_EOL;

        return Response::make(
            $bootstrap . (string) file_get_contents($path),
            SymfonyResponse::HTTP_OK,
            [
                'Content-Type'  => 'application/javascript; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
            ],
        );
    }
}
