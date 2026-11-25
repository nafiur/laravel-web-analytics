<?php

namespace Nafiur\WebAnalytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nafiur\WebAnalytics\Services\ReplaySigner;

/**
 * Authenticates replay chunk uploads with the HMAC-signed config emitted by
 * the host page. No session, cookie, CSRF token or secret bearer token is
 * needed; the signature is the only authority and is scoped by its expiry.
 */
class AuthenticateReplayIngest
{
    public function __construct(private ReplaySigner $signer)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        if (! config('laravel-web-analytics.replay.enabled', false)) {
            abort(404);
        }

        $token = trim((string) $request->input('token', ''));
        $payload = $token === '' ? null : $this->signer->verify($token);

        if ($payload === null || empty($payload['session_key']) || empty($payload['website_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired replay token',
            ], 401);
        }

        $request->attributes->set('lwa_replay_payload', $payload);
        $request->attributes->set('lwa_website_id', (int) $payload['website_id']);

        return $next($request);
    }
}
