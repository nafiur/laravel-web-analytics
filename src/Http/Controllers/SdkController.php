<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SdkController extends Controller
{
    /**
     * Serve the packaged JavaScript tracker. Zero build step: the SDK source
     * ships with the package and is served from here.
     */
    public function __invoke(): BinaryFileResponse
    {
        $path = __DIR__ . '/../../../resources/js/sdk.js';

        if (! config('laravel-web-analytics.sdk.enabled', false) || ! is_file($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
