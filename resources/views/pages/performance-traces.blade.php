@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Performance &amp; Traces</h1>
        <p class="lwa-subtitle mt-1">Span timings recorded through <code class="font-mono">lwa_events</code> and the dedicated span table. No timing is inferred from request logs.</p>
    </div>

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_performance_traces') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="lwa-field-label" for="traceRange">Window</label>
                <select id="traceRange" name="range" class="lwa-select">
                    @foreach(['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'all' => 'All time'] as $value => $label)
                        <option value="{{ $value }}" {{ $range === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[240px] flex-1">
                <label class="lwa-field-label" for="traceFilter">Trace ID</label>
                <input id="traceFilter" name="trace" maxlength="64" class="lwa-input font-mono" placeholder="optional trace id" value="{{ $trace ?? '' }}">
            </div>
            <button type="submit" class="lwa-btn-primary">Apply</button>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Spans</div><div class="lwa-kpi-value">{{ number_format($result['spans']) }}</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Traces</div><div class="lwa-kpi-value">{{ number_format($result['traces']) }}</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Error rate</div><div class="lwa-kpi-value">{{ number_format($result['error_rate'], 2) }}%</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Sampled</div><div class="lwa-kpi-value">{{ number_format($result['sample_size']) }}</div></div>
    </div>

    @if($result['capped'] ?? false)
        <div class="lwa-card p-4 text-xs" style="color: var(--lwa-warning)">Sample cap reached: only the 20,000 most recent spans were read.</div>
    @endif

    @if($trace !== null)
        <div class="lwa-card overflow-hidden">
            <div class="p-4 border-b lwa-border">
                <div class="lwa-card-title">Trace {{ $trace }}</div>
                <p class="lwa-subtitle mt-1">Spans ordered by start offset.</p>
            </div>
            @if($traceData === null)
                <div class="p-4 lwa-empty"><div class="lwa-empty-title">Trace not found</div><p>No spans for this id in the active scope.</p></div>
            @else
                <div class="lwa-table-wrap">
                    <table class="lwa-table w-full">
                        <thead><tr><th>Span</th><th>Parent</th><th>Kind</th><th>Start</th><th>Duration</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($traceData['spans'] as $span)
                                <tr>
                                    <td class="font-mono">{{ $span['name'] }}</td>
                                    <td class="font-mono text-[11px]">{{ $span['parent_id'] ?? '—' }}</td>
                                    <td>{{ $span['kind'] }}</td>
                                    <td>{{ number_format($span['start_ms']) }} ms</td>
                                    <td>{{ number_format($span['duration_ms']) }} ms</td>
                                    <td>{{ $span['status'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-3">
        <div class="lwa-card overflow-hidden">
            <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Slow operations</div></div>
            <div class="lwa-table-wrap">
                <table class="lwa-table w-full">
                    <thead><tr><th>Operation</th><th>Count</th><th>p50</th><th>p95</th><th>Max</th></tr></thead>
                    <tbody>
                        @forelse($result['operations'] as $operation)
                            <tr>
                                <td class="font-mono">{{ $operation['name'] }}</td>
                                <td>{{ number_format($operation['count']) }}</td>
                                <td>{{ number_format($operation['p50']) }} ms</td>
                                <td>{{ number_format($operation['p95']) }} ms</td>
                                <td>{{ number_format($operation['max']) }} ms</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="lwa-subtitle">No spans recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="lwa-card overflow-hidden">
            <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Slowest spans</div></div>
            <div class="space-y-1 p-4">
                @forelse($result['slowest'] as $span)
                    <div class="flex justify-between gap-3 text-xs border-b lwa-border py-1">
                        <a href="{{ route('lwa_performance_traces', ['trace' => $span['trace_id'], 'range' => $range]) }}" class="font-mono truncate" style="color: var(--lwa-accent)">{{ $span['name'] }}</a>
                        <span>{{ number_format($span['duration_ms']) }} ms · {{ $span['status'] }}</span>
                    </div>
                @empty
                    <div class="lwa-subtitle">No spans recorded.</div>
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
