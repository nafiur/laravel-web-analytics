<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Throwable;

/**
 * Renders the opt-in recorder bootstrap for host application layouts via the
 * @lwaReplay Blade directive. Nothing is emitted unless replay is enabled,
 * APP_KEY exists, a website can be resolved and an eligible viewer is present.
 */
class ReplayEmbed
{
    public function __construct(private ReplaySigner $signer)
    {
    }

    public function render(): string
    {
        if (! config('laravel-web-analytics.replay.enabled', false)) {
            return '';
        }

        try {
            $key = $this->signer->make([], 0); // fail fast if APP_KEY is missing
            unset($key);

            $websiteId = (int) (config('laravel-web-analytics.analytics.website_id') ?: Website::orderBy('domain')->value('id'));
            if ($websiteId <= 0) {
                return '';
            }

            $user = auth()->user();
            $userId = $user?->getAuthIdentifier();
            $userId = $userId === null ? null : (string) $userId;

            if ($userId === null && ! config('laravel-web-analytics.replay.record_guests', false)) {
                return '';
            }

            $sessionKey = session()->get('lwa_replay_session_key');
            if (! is_string($sessionKey) || $sessionKey === '') {
                $sessionKey = Str::random(32);
                session()->put('lwa_replay_session_key', $sessionKey);
            }

            $payload = [
                'website_id'      => $websiteId,
                'tenant_id'       => TenantResolver::resolveTrusted(),
                'user_id'         => $userId,
                'session_key'     => $sessionKey,
                'path'            => request()->path(),
                'sample_rate'     => (float) config('laravel-web-analytics.replay.sample_rate', 0),
                'mask_all_inputs' => (bool) config('laravel-web-analytics.replay.mask_all_inputs', true),
                'masked_selectors' => (array) config('laravel-web-analytics.replay.masked_selectors', []),
                'excluded_paths'  => (array) config('laravel-web-analytics.replay.excluded_paths', []),
            ];

            $token = $this->signer->make($payload);
            $bundle = Route::has('lwa_replay_bundle') ? route('lwa_replay_bundle') : null;
            if ($bundle === null) {
                return '';
            }

            $consent = (bool) config('laravel-web-analytics.replay.consent.replay', false);
            $settings = [
                'session_key'     => $sessionKey,
                'path'            => request()->path(),
                'sample_rate'     => (float) config('laravel-web-analytics.replay.sample_rate', 0),
                'bucket'          => hexdec(substr(hash('sha256', $sessionKey), 0, 8)) % 10000,
                'mask_all_inputs' => (bool) config('laravel-web-analytics.replay.mask_all_inputs', true),
                'masked_selectors' => (array) config('laravel-web-analytics.replay.masked_selectors', []),
                'excluded_paths'  => (array) config('laravel-web-analytics.replay.excluded_paths', []),
                'endpoint'        => (string) config('laravel-web-analytics.replay.endpoint', '/web-analytics/api/v1/replay/chunks'),
            ];

            return '<script>window.LWA_REPLAY_CONFIG=' . json_encode([
                'token'    => $token,
                'consent'  => $consent,
                'settings' => $settings,
            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) . ';</script>'
                . '<script defer src="' . e($bundle) . '"></script>';
        } catch (Throwable $e) {
            return '';
        }
    }
}
