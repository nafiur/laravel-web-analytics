@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Heatmaps</h1>
        <p class="lwa-subtitle mt-1">Aggregated click and scroll signals from recorded replay markers. Only masked, structural interaction data is used — never input text.</p>
    </div>

    @unless($ready)
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Replay markers unavailable</div>
            <p class="lwa-subtitle mt-1">Enable session replay and run migrations to build heatmaps.</p>
        </div>
    @endunless

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_heatmaps') }}" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[220px] flex-1">
                <label class="lwa-field-label" for="heatmapPath">Path</label>
                <input id="heatmapPath" list="lwaHeatmapPaths" name="path" class="lwa-input" placeholder="any recorded path" value="{{ $path ?? '' }}">
                <datalist id="lwaHeatmapPaths">
                    @foreach($paths as $candidate)<option value="{{ $candidate }}">@endforeach
                </datalist>
            </div>
            <div>
                <label class="lwa-field-label" for="heatmapRange">Window</label>
                <select id="heatmapRange" name="range" class="lwa-select">
                    @foreach(['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'all' => 'All time'] as $value => $label)
                        <option value="{{ $value }}" {{ $range === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="lwa-btn-primary">Apply</button>
            <a href="{{ route('lwa_heatmaps') }}" class="lwa-btn-ghost">Clear</a>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Clicks</div><div class="lwa-kpi-value">{{ number_format($result['clicks']) }}</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Scroll events</div><div class="lwa-kpi-value">{{ number_format($result['scrolls']) }}</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Sessions</div><div class="lwa-kpi-value">{{ number_format($result['sessions']) }}</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Markers scanned</div><div class="lwa-kpi-value">{{ number_format($result['sample_size']) }}</div></div>
    </div>

    @if($result['capped'])
        <div class="lwa-card p-4 text-xs" style="color: var(--lwa-warning)">Sample cap reached: only the 20,000 most recent matching markers were read. Counts are lower bounds.</div>
    @endif

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border">
            <div class="lwa-card-title">Click concentration</div>
            <p class="lwa-subtitle mt-1">Each dot is a 2.5% viewport bucket; size and opacity reflect clicks in that bucket.</p>
        </div>
        <div class="p-4">
            @if(empty($result['points']))
                <div class="lwa-empty"><div class="lwa-empty-title">No click positions yet</div><p>Recorded clicks with viewport data will appear here.</p></div>
            @else
                <div class="lwa-heat-canvas">
                    @foreach($result['points'] as $point)
                        @php($size = min(34, 8 + sqrt($point['count']) * 3))
                        <span class="lwa-heat-dot"
                              style="left: {{ $point['x'] }}%; top: {{ $point['y'] }}%; width: {{ $size }}px; height: {{ $size }}px; opacity: {{ min(0.85, 0.18 + $point['count'] * 0.05) }}"
                              title="{{ $point['x'] }}%, {{ $point['y'] }}% · {{ $point['count'] }} clicks"></span>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
        <div class="lwa-card overflow-hidden">
            <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Top targets</div></div>
            <div class="p-4">
                @forelse($result['targets'] as $target)
                    <div class="flex justify-between gap-3 py-1 text-xs border-b lwa-border">
                        <span class="truncate font-mono">{{ $target['label'] }}</span><span>{{ number_format($target['count']) }}</span>
                    </div>
                @empty
                    <div class="lwa-subtitle">No click targets recorded.</div>
                @endforelse
            </div>
        </div>

        <div class="lwa-card overflow-hidden">
            <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Scroll depth</div></div>
            <div class="p-4 space-y-1">
                @foreach($result['scroll'] as $bucket => $count)
                    @php($percent = ($bucket * 10) . '–' . ($bucket * 10 + 10) . '%')
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-12 shrink-0" style="color: var(--lwa-muted)">{{ $percent }}</span>
                        <span class="flex-1 h-2 rounded" style="background: var(--lwa-border)">
                            <span class="block h-2 rounded" style="width: {{ min(100, $count * 4) }}%; background: var(--lwa-accent)"></span>
                        </span>
                        <span class="w-12 text-right">{{ number_format($count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-3">
        <div class="lwa-card overflow-hidden">
            <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Rage clicks</div></div>
            <div class="p-4">
                @forelse($result['rage'] as $item)
                    <div class="flex justify-between gap-3 py-1 text-xs border-b lwa-border"><span class="truncate font-mono">{{ $item['label'] }}</span><span>{{ $item['count'] }}</span></div>
                @empty
                    <div class="lwa-subtitle">No 3-click bursts within 1 second.</div>
                @endforelse
            </div>
        </div>
        <div class="lwa-card overflow-hidden">
            <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Dead clicks</div></div>
            <div class="p-4">
                @forelse($result['dead'] as $item)
                    <div class="flex justify-between gap-3 py-1 text-xs border-b lwa-border"><span class="truncate font-mono">{{ $item['label'] }}</span><span>{{ $item['count'] }}</span></div>
                @empty
                    <div class="lwa-subtitle">No clicks without a follow-up marker.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="lwa-card p-4">
        <div class="lwa-card-title">Definitions</div>
        <ul class="mt-2 space-y-1 text-xs" style="color: var(--lwa-muted)">
            @foreach($result['definitions'] as $key => $definition)
                <li><span class="font-mono">{{ $key }}</span>: {{ $definition }}</li>
            @endforeach
        </ul>
    </div>
@endsection
