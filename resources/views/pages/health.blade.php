@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Installation Health</h1>
        <p class="lwa-subtitle mt-1">The same checks as <code class="font-mono">php artisan lwa:doctor</code>, rendered as a read-only report. Nothing here changes configuration or data.</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="lwa-card lwa-kpi">
            <div class="lwa-kpi-label">Overall</div>
            <div class="lwa-kpi-value text-base">{{ strtoupper($report['summary']['status']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Passed</div><div class="lwa-kpi-value">{{ number_format($report['summary']['passed']) }}</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Warnings</div><div class="lwa-kpi-value">{{ number_format($report['summary']['warnings']) }}</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Failures</div><div class="lwa-kpi-value">{{ number_format($report['summary']['failures']) }}</div></div>
    </div>

    <div class="lwa-card p-4">
        <div class="lwa-card-title">Next step</div>
        <p class="lwa-subtitle mt-1">{{ $report['summary']['next'] }}</p>
        <p class="lwa-subtitle mt-1">For machine-readable output use <code class="font-mono">php artisan lwa:doctor --json</code>; to repair missing advanced tables use <code class="font-mono">php artisan lwa:doctor --repair</code>.</p>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Checks</div></div>
        <div class="lwa-table-wrap">
            <table class="lwa-table w-full">
                <thead><tr><th>Check</th><th>Status</th><th>Detail</th></tr></thead>
                <tbody>
                    @foreach($report['checks'] as $check)
                        @php
                            $color = $check['status'] === 'ok' ? 'var(--lwa-success)'
                                : ($check['status'] === 'warn' ? 'var(--lwa-warning)' : 'var(--lwa-danger)');
                        @endphp
                        <tr>
                            <td class="font-medium">{{ $check['check'] }}</td>
                            <td><span class="font-mono text-[11px]" style="color: {{ $color }}">{{ strtoupper($check['status']) }}</span></td>
                            <td class="text-xs break-all" style="color: var(--lwa-muted)">{{ $check['detail'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
