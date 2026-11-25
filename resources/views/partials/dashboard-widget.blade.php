@php
    $widgetId = $widgetId ?? 'w' . uniqid();
    $widgetType = $widget['type'] ?? 'kpi';
    $widgetKey = $widget['key'] ?? '';
    $widgetTitle = \Nafiur\WebAnalytics\Services\DashboardWidgets::label($widget);
    $widgetWidth = max(3, min(12, (int) ($widget['w'] ?? 6)));
    $widgetSuffix = $widget['suffix'] ?? '';
@endphp
<div class="lwa-card p-4 lwa-dashboard-widget @if($widgetType === 'kpi') lwa-kpi @endif"
     data-dashboard-widget data-widget-id="{{ $widgetId }}" style="grid-column: span {{ $widgetWidth }}">
    <div class="flex items-start justify-between gap-2 mb-3">
        <div class="lwa-card-title">{{ $widgetTitle }}</div>
        @if(!empty($editing))
            <div class="flex items-center gap-1 shrink-0">
                <button type="button" data-widget-move="{{ $widgetId }}" data-direction="-1" class="p-1 rounded text-slate-400 hover:text-slate-700" aria-label="Move left"><i data-lucide="arrow-left" class="text-xs"></i></button>
                <button type="button" data-widget-move="{{ $widgetId }}" data-direction="1" class="p-1 rounded text-slate-400 hover:text-slate-700" aria-label="Move right"><i data-lucide="arrow-right" class="text-xs"></i></button>
                <button type="button" data-widget-size="{{ $widgetId }}" class="p-1 rounded text-slate-400 hover:text-slate-700" aria-label="Resize"><i data-lucide="maximize-2" class="text-xs"></i></button>
                <button type="button" data-widget-remove="{{ $widgetId }}" class="p-1 rounded text-slate-400 hover:text-rose-500" aria-label="Remove"><i data-lucide="x" class="text-xs"></i></button>
            </div>
        @endif
    </div>

    @if($widgetType === 'kpi')
        <div class="lwa-kpi-value">
            {{ $analytics['summary'][$widgetKey] ?? 0 }}{{ $widgetSuffix }}
        </div>
        <div class="lwa-subtitle mt-1">{{ str_replace('_', ' ', $widgetKey) }}</div>

    @elseif($widgetType === 'chart')
        @php
            $timeSeries = $analytics['time_series'] ?? [];
            $chartSeries = [];
            $chartLabels = $timeSeries['labels'] ?? [];
            if ($widgetKey === 'requests') {
                $chartSeries = [['name' => 'Requests', 'data' => $timeSeries['requests'] ?? []]];
            } elseif ($widgetKey === 'bandwidth_mb') {
                $chartSeries = [['name' => 'Data Transfer (MB)', 'data' => $timeSeries['bandwidth_mb'] ?? []]];
            } elseif ($widgetKey === 'status') {
                $chartSeries = [
                    ['name' => '2xx', 'data' => $timeSeries['status_2xx'] ?? []],
                    ['name' => '3xx', 'data' => $timeSeries['status_3xx'] ?? []],
                    ['name' => '4xx', 'data' => $timeSeries['status_4xx'] ?? []],
                    ['name' => '5xx', 'data' => $timeSeries['status_5xx'] ?? []],
                ];
            } elseif ($widgetKey === 'devices') {
                $chartSeries = $timeSeries['device_over_time']['series'] ?? [];
                $chartLabels = $timeSeries['device_over_time']['labels'] ?? $chartLabels;
            } elseif ($widgetKey === 'outcomes') {
                $chartTrend = $analytics['breakdown_trends']['top_cache_statuses'] ?? [];
                $chartSeries = $chartTrend['series'] ?? [];
                $chartLabels = $chartTrend['labels'] ?? $chartLabels;
            }
        @endphp
        <div class="h-48" data-widget-chart="{{ $widgetId }}"></div>
        <script type="application/json" id="lwaWidgetChartData-{{ $widgetId }}">{!! json_encode(['labels' => $chartLabels, 'series' => $chartSeries, 'stacked' => in_array($widgetKey, ['status', 'devices'], true)], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    @elseif($widgetType === 'breakdown')
        @include('web-analytics::partials.breakdown-list', [
            'items' => $analytics['breakdowns'][$widgetKey] ?? [],
            'key'   => $widgetKey,
            'title' => $widgetTitle,
        ])

    @elseif($widgetType === 'logs')
        <div class="space-y-1">
            @forelse(array_slice($analytics['recent_logs'] ?? [], 0, 8) as $log)
                <div class="flex items-center justify-between gap-2 text-[11px] border-b border-slate-100 dark:border-slate-800 py-1 last:border-0">
                    <span class="font-mono text-slate-700 dark:text-slate-200 truncate" title="{{ $log['path'] }}">{{ $log['path'] }}</span>
                    <span class="font-mono text-slate-400 shrink-0">{{ $log['status_code'] }}</span>
                    <span class="text-slate-400 shrink-0">{{ $log['browser'] ?? '-' }}</span>
                </div>
            @empty
                <div class="text-xs text-slate-400 py-3 text-center">No requests recorded</div>
            @endforelse
        </div>

    @elseif($widgetType === 'map')
        @include('web-analytics::partials.visitors-map', [
            'mapId'    => 'lwaWidgetMap-' . $widgetId,
            'title'    => '',
            'subtitle' => 'Approximate visitor locations',
            'bare'     => true,
        ])

    @else
        <div class="text-xs text-slate-400">Unknown widget type</div>
    @endif
</div>
