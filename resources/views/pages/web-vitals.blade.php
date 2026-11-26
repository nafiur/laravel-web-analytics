@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Web Vitals</h1>
        <p class="lwa-subtitle mt-1">Field metrics emitted by the browser SDK into <code class="font-mono">lwa_events</code>. Nothing is inferred from server response times.</p>
    </div>

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_web_vitals') }}" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[220px] flex-1">
                <label class="lwa-field-label" for="vitalPath">Path</label>
                <input id="vitalPath" list="lwaVitalPaths" name="path" class="lwa-input" placeholder="any recorded path" value="{{ $path ?? '' }}">
                <datalist id="lwaVitalPaths">
                    @foreach($paths as $candidate)<option value="{{ $candidate }}">@endforeach
                </datalist>
            </div>
            <div>
                <label class="lwa-field-label" for="vitalRange">Window</label>
                <select id="vitalRange" name="range" class="lwa-select">
                    @foreach(['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'all' => 'All time'] as $value => $label)
                        <option value="{{ $value }}" {{ $range === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="lwa-btn-primary">Apply</button>
        </form>
    </div>

    @if($result['capped'] ?? false)
        <div class="lwa-card p-4 text-xs" style="color: var(--lwa-warning)">Sample cap reached: only the 20,000 most recent web vital events were read.</div>
    @endif

    <div class="grid md:grid-cols-3 gap-3">
        @foreach($result['metrics'] as $metric)
            <div class="lwa-card p-4">
                <div class="lwa-kpi-label">{{ $metric['label'] }}</div>
                <div class="lwa-kpi-value">
                    {{ $metric['p75'] === null ? '—' : number_format($metric['p75'], 3) }}
                    <span class="text-xs" style="color: var(--lwa-muted)">p75 {{ $metric['unit'] }}</span>
                </div>
                <p class="lwa-subtitle mt-1">Median {{ $metric['p50'] === null ? '—' : number_format($metric['p50'], 3) }} · {{ number_format($metric['count']) }} samples</p>
                <div class="mt-3 flex h-2 overflow-hidden rounded" style="background: var(--lwa-border)">
                    @if($metric['count'] > 0)
                        <span style="width: {{ ($metric['good'] / $metric['count']) * 100 }}%; background: var(--lwa-success)"></span>
                        <span style="width: {{ ($metric['needs_improvement'] / $metric['count']) * 100 }}%; background: var(--lwa-warning)"></span>
                        <span style="width: {{ ($metric['poor'] / $metric['count']) * 100 }}%; background: var(--lwa-danger)"></span>
                    @endif
                </div>
                <div class="mt-2 flex justify-between text-[11px]" style="color: var(--lwa-muted)">
                    <span>Good {{ number_format($metric['good']) }}</span>
                    <span>Needs work {{ number_format($metric['needs_improvement']) }}</span>
                    <span>Poor {{ number_format($metric['poor']) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border"><div class="lwa-card-title">By path</div></div>
        @if(empty($result['paths']))
            <div class="p-4 lwa-empty">
                <div class="lwa-empty-title">No web vitals recorded</div>
                <p>Enable the SDK's <code class="font-mono">web_vitals</code> option to start reporting LCP, CLS and INP.</p>
            </div>
        @else
            <div class="lwa-table-wrap">
                <table class="lwa-table w-full">
                    <thead><tr><th>Path</th><th>Samples</th><th>LCP p75</th><th>CLS p75</th><th>INP p75</th></tr></thead>
                    <tbody>
                        @foreach($result['paths'] as $row)
                            <tr>
                                <td class="font-mono">{{ $row['path'] }}</td>
                                <td>{{ number_format($row['lcp']['count']) }}</td>
                                <td>{{ $row['lcp']['p75'] === null ? '—' : number_format($row['lcp']['p75'], 3) }} ms</td>
                                <td>{{ $row['cls']['p75'] === null ? '—' : number_format($row['cls']['p75'], 3) }}</td>
                                <td>{{ $row['inp']['p75'] === null ? '—' : number_format($row['inp']['p75'], 3) }} ms</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
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
