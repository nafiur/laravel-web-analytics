<?php

namespace Nafiur\WebAnalytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nafiur\WebAnalytics\Http\Middleware\AuthenticateApiToken;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\WebsiteScope;

/**
 * Authentication for the browser-facing batch ingest endpoint.
 *
 * Accepts either a write-capable API token (server-to-server) or a public
 * project key — the website's tracking id — sent in the `X-LWA-Project-Key`
 * header or the request body. Public keys are intentionally low privilege:
 * they are scoped to this endpoint only and remain subject to the global
 * ingestion rate limiter.
 */
class AuthenticateIngest
{
    public function __construct(private AuthenticateApiToken $tokens)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $header = (string) $request->header('Authorization', '');

        if (str_starts_with(strtolower($header), 'bearer ')) {
            return $this->tokens->handle($request, $next, 'write');
        }

        $key = trim((string) ($request->header('X-LWA-Project-Key') ?: $request->input('key', '')));
        if ($key === '') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $website = Website::withoutGlobalScope(WebsiteScope::class)
            ->where('tracking_id', $key)
            ->first();

        if ($website === null) {
            return response()->json(['success' => false, 'message' => 'Invalid project key'], 401);
        }

        $request->attributes->set('lwa_website_id', $website->id);
        $request->attributes->set('lwa_public_key', $key);

        return $next($request);
    }
}
