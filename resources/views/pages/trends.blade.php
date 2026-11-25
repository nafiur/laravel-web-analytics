@extends('web-analytics::layouts.app')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="lwa-page-title">Trends</h1>
            <p class="lwa-subtitle mt-1">Explore events, users and revenue over time. Every series is computed from <code class="font-mono">lwa_events</code>.</p>
        </div>
        <span class="lwa-pill">v3.3</span>
    </div>

    @unless($ready)
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Event store unavailable</div>
            <p class="lwa-subtitle mt-1">Enable the advanced schema and run the migrations to build trends.</p>
        </div>
    @endunless

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_trends') }}" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 items-end">
            <div>
                <label class="lwa-field-label" for="trendEvent">Event</label>
                <select id="trendEvent" name="event" class="lwa-select">
                    <option value="">All events</option>
                    @foreach($names as $name)
                        <option value="{{ $name }}" {{ ($filters['event'] ?? '') === $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="lwa-field-label" for="trendMetric">Metric</label>
                <select id="trendMetric" name="metric" class="lwa-select">
                    @foreach(['events' => 'Events', 'users' => 'Unique users', 'value' => 'Value'] as $value => $label)
                        <option value="{{ $value }}" {{ ($filters['metric'] ?? 'events') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="lwa-field-label" for="trendBreakdown">Breakdown property</label>
                <input id="trendBreakdown" name="breakdown" class="lwa-input" placeholder="plan" value="{{ $filters['breakdown'] ?? '' }}">
            </div>
            <div>
                <label class="lwa-field-label" for="trendGranularity">Granularity</label>
                <select id="trendGranularity" name="granularity" class="lwa-select">
                    @foreach(['hour' => 'Hourly', 'day' => 'Daily', 'week' => 'Weekly', 'month' => 'Monthly'] as $value => $label)
                        <option value="{{ $value }}" {{ ($filters['granularity'] ?? 'day') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="lwa-field-label" for="trendPropertyKey">Property key</label>
                <input id="trendPropertyKey" name="property_key" class="lwa-input" placeholder="source" value="{{ $filters['property_key'] ?? '' }}">
            </div>
            <div>
                <label class="lwa-field-label" for="trendPropertyValue">Property value</label>
                <input id="trendPropertyValue" name="property_value" class="lwa-input" placeholder="newsletter" value="{{ $filters['property_value'] ?? '' }}">
            </div>
            <div>
                <label class="lwa-field-label" for="trendRange">Time window</label>
                <select id="trendRange" name="range" class="lwa-select">
                    @foreach(['1h' => 'Last hour', '24h' => 'Last 24 hours', '7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'all' => 'All time', 'custom' => 'Custom'] as $value => $label)
                        <option value="{{ $value }}" {{ ($filters['range'] ?? '7d') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="lwa-btn-primary"><i data-lucide="chart-no-axes-combined" class="text-[13px]"></i> Run query</button>
                <a href="{{ route('lwa_trends') }}" class="lwa-btn-ghost">Clear</a>
            </div>
        </form>

        @if(($filters['range'] ?? '') === 'custom')
            <div class="grid grid-cols-2 gap-3 mt-3 pt-3 border-t lwa-border">
                <div>
                    <label class="lwa-field-label" for="trendStart">Start date</label>
                    <input id="trendStart" type="date" name="start_date" class="lwa-input" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="trendEnd">End date</label>
                    <input id="trendEnd" type="date" name="end_date" class="lwa-input" value="{{ $filters['end_date'] ?? '' }}">
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="lwa-card lwa-kpi" title="Distinct total across all charted series">
            <div class="lwa-kpi-top">
                <span class="lwa-kpi-label">Total {{ $filters['metric'] ?? 'events' }}</span>
                <span class="lwa-icon-tile"><i data-lucide="sigma" class="text-[13px]"></i></span>
            </div>
            <div class="lwa-kpi-value">{{ number_format((float) $trend['global_total'], 2) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Number of lwa_events rows scanned for this query">
            <div class="lwa-kpi-top">
                <span class="lwa-kpi-label">Events scanned</span>
                <span class="lwa-icon-tile"><i data-lucide="database" class="text-[13px]"></i></span>
            </div>
            <div class="lwa-kpi-value">{{ number_format($trend['sample_size']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Unique breakdown groups charted (top 6 by total)">
            <div class="lwa-kpi-top">
                <span class="lwa-kpi-label">Series</span>
                <span class="lwa-icon-tile"><i data-lucide="layers-3" class="text-[13px]"></i></span>
            </div>
            <div class="lwa-kpi-value">{{ number_format(count($trend['series'])) }}</div>
        </div>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="px-4 py-3 border-b lwa-border flex flex-wrap items-center justify-between gap-2">
            <div>
                <div class="lwa-card-title">Trend over time</div>
                <p class="lwa-subtitle mt-0.5">{{ count($trend['labels']) }} {{ $trend['granularity'] }} buckets · {{ $trend['sample_size'] }} events scanned @if($trend['capped']) · <span class="text-amber-500">sample capped</span>@endif</p>
            </div>
            @if($trend['granularity'] !== ($trend['granularity_requested'] ?? $trend['granularity']))
                <span class="lwa-chip">Auto-coarsened to {{ $trend['granularity'] }}</span>
            @endif
        </div>
        <div class="p-4">
            <div id="chartTrends" class="w-full" style="min-height:280px"></div>
            @if(empty($trend['series']))
                <div class="py-8 text-center">
                    <div class="mx-auto lwa-icon-tile mb-3" style="width:40px;height:40px"><i data-lucide="chart-no-axes-combined" class="text-lg"></i></div>
                    <div class="lwa-card-title">No matching events</div>
                    <p class="lwa-subtitle mt-1">Try a wider time window or clear the event/property filters.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Series totals</div>
                <p class="lwa-subtitle mt-0.5">Top groups by total value for the current metric and filters</p>
            </div>
            @if(empty($trend['totals']))
                <div class="px-4 py-8 text-center lwa-subtitle">No series to summarise.</div>
            @else
                <table class="w-full text-xs text-left lwa-table">
                    <thead><tr><th class="py-2 px-4 font-semibold">Series</th><th class="py-2 px-4 font-semibold text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($trend['totals'] as $total)
                            <tr><td class="py-2 px-4 font-medium">{{ $total['name'] }}</td><td class="py-2 px-4 text-right font-mono">{{ number_format((float) $total['value'], 2) }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Metric definitions</div>
                <p class="lwa-subtitle mt-0.5">These definitions ship with the query — no inferred or fabricated numbers</p>
            </div>
            <ul class="px-4 py-3 space-y-2 text-xs text-slate-600 dark:text-slate-300">
                @foreach($trend['definitions'] as $definition)
                    <li class="flex gap-2"><span class="text-slate-400">·</span><span>{!! e($definition) !!}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Saved reports</div>
                <p class="lwa-subtitle mt-0.5">Stored in <code class="font-mono">lwa_insights</code>, scoped to the active website</p>
            </div>
            @if($saved->isEmpty())
                <div class="px-4 py-8 text-center lwa-subtitle">No saved reports yet.</div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($saved as $report)
                        <div class="px-4 py-2.5 flex items-center justify-between gap-3">
                            <a href="{{ route('lwa_trends') . '?' . http_build_query($report->config ?? []) }}" class="text-xs font-medium hover:underline">{{ $report->name }}</a>
                            <form method="POST" action="{{ route('lwa_trends_destroy', $report->id) }}">
                                @csrf
                                <button type="submit" class="lwa-btn-ghost" title="Delete report"><i data-lucide="trash-2" class="text-[12px]"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Save current query</div>
                <p class="lwa-subtitle mt-0.5">Give this configuration a name to reuse it later</p>
            </div>
            <form method="POST" action="{{ route('lwa_trends_save') }}" class="p-4 flex flex-wrap items-end gap-2">
                @csrf
                <input type="hidden" name="config" value="{{ json_encode($filters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}">
                <div class="flex-1 min-w-48">
                    <label class="lwa-field-label" for="reportName">Report name</label>
                    <input id="reportName" name="name" class="lwa-input" required maxlength="150" placeholder="Weekly signups by plan">
                </div>
                <button type="submit" class="lwa-btn-primary"><i data-lucide="bookmark-plus" class="text-[13px]"></i> Save report</button>
            </form>
            @if($errors->any())
                <div class="px-4 pb-3 text-xs text-red-500">{{ $errors->first() }}</div>
            @endif
        </div>
    </div>

    <script>
        const lwaTrendData = {!! json_encode($trend, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) !!};
        document.addEventListener('DOMContentLoaded', function () {
            const el = document.querySelector('#chartTrends');
            if (!el || !window.ApexCharts || !lwaTrendData.series.length) return;
            const colors = typeof lwaChartColors === 'function' ? lwaChartColors() : ['#5b5bd6', '#14b8a6', '#e8a33d'];
            const options = {
                series: lwaTrendData.series,
                chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
                colors: colors,
                fill: { type: 'solid', opacity: 0.07 },
                stroke: { curve: 'smooth', width: 2 },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: lwaTrendData.labels,
                    labels: { style: { fontSize: '10px' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: { labels: { style: { fontSize: '10px' } } },
                legend: { position: 'top', horizontalAlign: 'left', fontSize: '11px' },
                grid: typeof lwaGrid === 'function' ? lwaGrid() : { borderColor: '#ededf1' }
            };
            if (typeof lwaThinAxis === 'function') lwaThinAxis(options);
            if (typeof lwaChartTooltip === 'function') options.tooltip = lwaChartTooltip('');
            new ApexCharts(el, options).render();
        });
    </script>
@endsection
