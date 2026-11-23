<?php

namespace Nafiur\WebAnalytics\Services;

use Nafiur\WebAnalytics\Models\Analytics\Website;

/**
 * Resolves the website currently selected in the dashboard. The session
 * choice wins over the static config value, which in turn falls back to the
 * first registered website.
 */
class WebsiteResolver
{
    public function currentId(): ?int
    {
        $sessionId = session('lwa_website_id');
        if ($sessionId && Website::find($sessionId)) {
            return (int) $sessionId;
        }

        $configId = config('laravel-web-analytics.analytics.website_id');
        if ($configId && Website::find($configId)) {
            return (int) $configId;
        }

        return Website::orderBy('domain')->value('id') ?: null;
    }

    public function switchTo(int $websiteId): bool
    {
        if (!Website::find($websiteId)) {
            return false;
        }

        session(['lwa_website_id' => $websiteId]);

        return true;
    }
}
