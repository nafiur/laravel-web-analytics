<?php

namespace Nafiur\WebAnalytics\Services;

/**
 * Catalog of dashboard widgets. Every entry maps to data that already exists
 * in the analytics payload — no new metrics are invented here.
 */
final class DashboardWidgets
{
    /** @return array<string, array{label: string, group: string, type: string, key?: string}> */
    public static function catalog(): array
    {
        $catalog = [
            'kpi_requests'   => ['label' => 'Total requests', 'group' => 'KPI', 'type' => 'kpi', 'key' => 'total_requests_fmt'],
            'kpi_visitors'   => ['label' => 'Unique visitors', 'group' => 'KPI', 'type' => 'kpi', 'key' => 'unique_visitors_fmt'],
            'kpi_pageviews'  => ['label' => 'Pageviews', 'group' => 'KPI', 'type' => 'kpi', 'key' => 'pageviews_fmt'],
            'kpi_sessions'   => ['label' => 'Sessions', 'group' => 'KPI', 'type' => 'kpi', 'key' => 'sessions_fmt'],
            'kpi_bounce'     => ['label' => 'Bounce rate', 'group' => 'KPI', 'type' => 'kpi', 'key' => 'bounce_rate', 'suffix' => '%'],
            'kpi_live'       => ['label' => 'Live now', 'group' => 'KPI', 'type' => 'kpi', 'key' => 'live_visitors_fmt'],

            'chart_traffic'  => ['label' => 'Requests over time', 'group' => 'Chart', 'type' => 'chart', 'key' => 'requests'],
            'chart_bandwidth'=> ['label' => 'Bandwidth over time', 'group' => 'Chart', 'type' => 'chart', 'key' => 'bandwidth_mb'],
            'chart_status'   => ['label' => 'Status codes', 'group' => 'Chart', 'type' => 'chart', 'key' => 'status'],
            'chart_devices'  => ['label' => 'Device types over time', 'group' => 'Chart', 'type' => 'chart', 'key' => 'devices'],
            'chart_outcomes' => ['label' => 'Cache outcomes', 'group' => 'Chart', 'type' => 'chart', 'key' => 'outcomes'],

            'list_paths'     => ['label' => 'Top paths', 'group' => 'Breakdown', 'type' => 'breakdown', 'key' => 'top_paths'],
            'list_countries' => ['label' => 'Top countries', 'group' => 'Breakdown', 'type' => 'breakdown', 'key' => 'top_countries'],
            'list_cities'    => ['label' => 'Top cities', 'group' => 'Breakdown', 'type' => 'breakdown', 'key' => 'top_cities'],
            'list_browsers'  => ['label' => 'Top browsers', 'group' => 'Breakdown', 'type' => 'breakdown', 'key' => 'top_browsers'],
            'list_os'        => ['label' => 'Operating systems', 'group' => 'Breakdown', 'type' => 'breakdown', 'key' => 'top_os'],
            'list_devices'   => ['label' => 'Device types', 'group' => 'Breakdown', 'type' => 'breakdown', 'key' => 'top_devices'],
            'list_referrers' => ['label' => 'Referrers', 'group' => 'Breakdown', 'type' => 'breakdown', 'key' => 'top_referrers'],
            'list_hosts'     => ['label' => 'Hosts', 'group' => 'Breakdown', 'type' => 'breakdown', 'key' => 'top_hosts'],
            'list_cache'     => ['label' => 'Cache status', 'group' => 'Breakdown', 'type' => 'breakdown', 'key' => 'top_cache_statuses'],

            'map_visitors'   => ['label' => 'Visitors map', 'group' => 'Other', 'type' => 'map'],
            'table_logs'     => ['label' => 'Recent requests', 'group' => 'Other', 'type' => 'logs'],
        ];

        foreach ($catalog as $key => &$definition) {
            $definition['id'] = $key;
        }

        return $catalog;
    }

    /** @return array<string, array{label: string, description: string, widgets: list<string>}> */
    public static function templates(): array
    {
        return [
            'traffic' => [
                'label'       => 'Traffic overview',
                'description' => 'Requests, visitors, top pages and device breakdown',
                'widgets'     => ['kpi_requests', 'kpi_visitors', 'kpi_pageviews', 'chart_traffic', 'list_paths', 'list_devices'],
            ],
            'cache' => [
                'label'       => 'Cache performance',
                'description' => 'Delivery outcomes, cache status and hosts',
                'widgets'     => ['chart_outcomes', 'list_cache', 'list_hosts', 'table_logs'],
            ],
            'errors' => [
                'label'       => 'Error monitoring',
                'description' => 'Status codes and recent error requests',
                'widgets'     => ['chart_status', 'list_paths', 'table_logs'],
            ],
            'visitors' => [
                'label'       => 'Visitors & devices',
                'description' => 'Visitor map, browsers, operating systems and devices',
                'widgets'     => ['map_visitors', 'kpi_visitors', 'list_browsers', 'list_os', 'list_devices'],
            ],
        ];
    }

    /** @param array<string, mixed> $widget */
    public static function label(array $widget): string
    {
        $id = (string) ($widget['widget'] ?? '');
        $catalog = self::catalog();

        return !empty($widget['title']) ? (string) $widget['title']
            : ($catalog[$id]['label'] ?? ucfirst(str_replace('_', ' ', $id)));
    }
}
