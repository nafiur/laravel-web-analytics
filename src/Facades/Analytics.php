<?php

namespace Nafiur\WebAnalytics\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void pageview(string $path, array $options = [])
 * @method static void event(string $name, array $properties = [], ?float $value = null, array $options = [])
 * @method static void track(string $name, array $properties = [], ?float $value = null, array $options = [])
 * @method static void goal(string $name, array $options = [])
 * @method static array summary(string $range = '24h', ?int $websiteId = null, ?string $start = null, ?string $end = null)
 * @method static array timeseries(string $range = '24h', ?int $websiteId = null, ?string $start = null, ?string $end = null)
 *
 * @see \Nafiur\WebAnalytics\Services\AnalyticsManager
 */
class Analytics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lwa.analytics';
    }
}
