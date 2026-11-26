<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Serves vendored, prebuilt open-source browser assets from the package.
 * Host applications still have no npm/build step.
 */
class VendorAssetController extends Controller
{
    public function rrwebJs(): SymfonyResponse
    {
        if (! config('laravel-web-analytics.replay.enabled', false)) {
            abort(404);
        }

        return $this->asset('rrweb.umd.cjs', 'application/javascript; charset=utf-8');
    }

    public function rrwebCss(): SymfonyResponse
    {
        if (! config('laravel-web-analytics.replay.enabled', false)) {
            abort(404);
        }

        return $this->asset('rrweb.css', 'text/css; charset=utf-8');
    }

    public function webVitalsJs(): SymfonyResponse
    {
        if (! config('laravel-web-analytics.sdk.enabled', false)
            || ! config('laravel-web-analytics.sdk.web_vitals', false)) {
            abort(404);
        }

        return $this->asset('web-vitals.iife.js', 'application/javascript; charset=utf-8');
    }

    private function asset(string $file, string $contentType): SymfonyResponse
    {
        $path = __DIR__ . '/../../../resources/js/vendor/' . $file;
        if (! is_file($path)) {
            abort(404);
        }

        return Response::make(
            (string) file_get_contents($path),
            SymfonyResponse::HTTP_OK,
            [
                'Content-Type'  => $contentType,
                'Cache-Control' => 'public, max-age=86400',
            ],
        );
    }
}
