<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/** Serves the deferred playback bundle only on gated replay player pages. */
class ReplayPlayerBundleController extends Controller
{
    public function __invoke(): SymfonyResponse
    {
        $path = __DIR__ . '/../../../resources/js/replay-player.js';

        if (! config('laravel-web-analytics.replay.enabled', false) || ! is_file($path)) {
            abort(404);
        }

        return Response::make(
            (string) file_get_contents($path),
            SymfonyResponse::HTTP_OK,
            [
                'Content-Type'  => 'application/javascript; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
            ],
        );
    }
}
