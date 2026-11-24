<?php

namespace Nafiur\WebAnalytics\Services;

/**
 * Canonical device categories shared by every analytics data source and the
 * "Device types over time" chart. Raw values recorded by the collector are
 * normalised here so SQLite, MySQL and PostgreSQL produce identical series.
 */
final class DeviceCategory
{
    /** @var list<string> */
    public const ORDER = ['Desktop', 'Mobile', 'Tablet', 'Bot'];

    public static function normalize(?string $device): ?string
    {
        return match (strtolower(trim((string) $device))) {
            'desktop' => 'Desktop',
            'mobile'  => 'Mobile',
            'tablet'  => 'Tablet',
            'bot'     => 'Bot',
            default   => null,
        };
    }
}
