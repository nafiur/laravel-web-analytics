<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Serves the build-free recorder bundle. The signed per-page config is
 * injected separately by @lwaReplay; these are only static browser defaults.
 */
class ReplayBundleController extends Controller
{
    public function __invoke(): SymfonyResponse
    {
        $path = __DIR__ . '/../../../resources/js/replay.js';

        if (! config('laravel-web-analytics.replay.enabled', false) || ! is_file($path)) {
            abort(404);
        }

        $defaults = [
            'endpoint'        => (string) config('laravel-web-analytics.replay.endpoint', '/web-analytics/api/v1/replay/chunks'),
            'flush_ms'        => (int) config('laravel-web-analytics.replay.flush_interval_ms', 5000),
            'max_chunk_bytes' => (int) config('laravel-web-analytics.replay.max_chunk_bytes', 1000000),
            'max_markers'     => (int) config('laravel-web-analytics.replay.max_markers', 25),
        ];

        $bootstrap = 'window.LWA_REPLAY_DEFAULTS=' . json_encode(
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
