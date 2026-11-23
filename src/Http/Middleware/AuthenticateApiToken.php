<?php

namespace Nafiur\WebAnalytics\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next, string $ability = 'read')
    {
        $header = (string) $request->header('Authorization', '');

        if (!str_starts_with(strtolower($header), 'bearer ')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $plain = trim(substr($header, 7));
        $token = ApiToken::withoutGlobalScope(WebsiteScope::class)
            ->where('token_hash', ApiToken::hash($plain))
            ->first();

        if ($token === null || ($token->expires_at !== null && $token->expires_at->isPast())) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired token'], 401);
        }

        if (!$token->can($ability)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $token->last_used_at = now();
        $token->save();

        $request->attributes->set('lwa_api_token', $token);
        $request->attributes->set('lwa_website_id', $token->website_id);

        return $next($request);
    }
}
