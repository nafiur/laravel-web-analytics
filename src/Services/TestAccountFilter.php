<?php

namespace Nafiur\WebAnalytics\Services;

/**
 * Resolves configured internal/test accounts for the optional "Filter test
 * accounts" view toggle. Scope is always derived from configuration, never
 * from request-supplied identifiers.
 */
class TestAccountFilter
{
    public function enabled(): bool
    {
        return (bool) config('laravel-web-analytics.privacy.filter_test_accounts', false)
            && $this->hasConfiguredAccounts();
    }

    public function hasConfiguredAccounts(): bool
    {
        return $this->keys() !== [] || $this->ips() !== [] || $this->sessions() !== [];
    }

    /** @return array<int, string> */
    public function keys(): array
    {
        return $this->values('test_account_keys');
    }

    /** @return array<int, string> */
    public function ips(): array
    {
        return $this->values('test_account_ips');
    }

    /** @return array<int, string> */
    public function sessions(): array
    {
        return $this->values('test_account_sessions');
    }

    /**
     * Visitor hashes stored on lwa_events. Accepts raw keys, `u_<id>` distinct
     * ids and sha256 fingerprints so the same identity is excluded regardless
     * of how the collector first saw it.
     *
     * @return array<int, string>
     */
    public function visitorHashes(): array
    {
        $hashes = [];
        foreach ($this->keys() as $key) {
            $digest = hash('sha256', $key);
            $hashes[] = $key;
            $hashes[] = 'u_' . $key;
            $hashes[] = $digest;
            $hashes[] = 'u_' . $digest;
        }

        return array_values(array_unique($hashes));
    }

    public static function activeFromRequest(): bool
    {
        if (! app()->bound('request')) {
            return false;
        }

        $filter = app(self::class);

        return $filter->enabled() && request()->boolean('exclude_test');
    }

    /** @return array<int, string> */
    private function values(string $key): array
    {
        $value = config('laravel-web-analytics.privacy.' . $key, []);
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        return array_values(array_filter(array_map('trim', is_array($value) ? $value : []), fn ($item) => $item !== ''));
    }
}
