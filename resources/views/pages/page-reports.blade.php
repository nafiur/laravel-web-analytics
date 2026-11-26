@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Page Reports</h1>
        <p class="lwa-subtitle mt-1">Per-path visitor, view, bounce, entry and exit metrics from bounded <code class="font-mono">lwa_logs</code> rows. All definitions are printed below.</p>
    </div>

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_page_reports') }}" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[240px] flex-1">
                <label class="lwa-field-label" for="reportPath">Path</label>
                <input id="reportPath" list="lwaReportPaths" name="path" class="lwa-input font-mono" placeholder="select a path (blank = top paths)" value="{{ $path ?? '' }}">
                <datalist id="lwaReportPaths">
                    @foreach($result['paths'] as $candidate)<option value="{{ $candidate['path'] }}">@endforeach
                </datalist>
            </div>
            <div>
                <label class="lwa-field-label" for="reportRange">Window</label>
                <select id="reportRange" name="range" class="lwa-select">
                    @foreach(['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'all' => 'All time'] as $value => $label)
                        <option value="{{ $value }}" {{ $range === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <input type="hidden" name="compare" value="0">
                <input id="reportCompare" type="checkbox" name="compare" value="1" class="lwa-checkbox" {{ request()->boolean('compare') ? 'checked' : '' }}>
                <label class="lwa-field-label" for="reportCompare">Compare prior</label>
            </div>
            <button type="submit" class="lwa-btn-primary">Apply</button>
        </form>
    </div>

    @if($result['capped'] ?? false)
        <div class="lwa-card p-4 text-xs" style="color: var(--lwa-warning)">Sample cap reached: only the 20,000 most recent requests were read. Counts are lower bounds.</div>
    @endif

    @if($path !== null && $path !== '')
        @php
            $cards = [
                ['Visitors', 'visitors', 'total_visitors'],
                ['Views', 'views', 'pageviews'],
                ['Entries', 'entries', 'sessions'],
                ['Bounce Rate', 'bounce_rate', 'bounce_rate'],
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @foreach($cards as [$label, $key, $comparisonKey])
                <div class="lwa-card lwa-kpi">
                    <div class="lwa-kpi-label">{{ $label }}</div>
                    <div class="lwa-kpi-value">{{ number_format($result['summary'][$key]) }}{!! $key === 'bounce_rate' ? '%' : '' !!}</div>
                    @if(!empty($result['comparison'][$comparisonKey]) && $result['comparison'][$comparisonKey]['available'])
                        @php
                            $delta = $result['comparison'][$comparisonKey];
                        @endphp
                        <div class="mt-1 text-[10px]" style="color: {{ $delta['direction'] === 'up' ? 'var(--lwa-danger)' : ($delta['direction'] === 'down' ? 'var(--lwa-success)' : 'var(--lwa-muted)') }}">
                            @if($delta['is_new']) new vs prior @else {{ $delta['direction'] === 'up' ? '↑' : ($delta['direction'] === 'down' ? '↓' : '→') }} {{ number_format(abs((float) $delta['change']), 2) }}{{ $delta['unit'] === 'points' ? ' pp' : '%' }} vs prior @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="grid md:grid-cols-2 gap-3">
            <div class="lwa-card p-4">
                <div class="lwa-card-title">Next paths</div>
                <div class="mt-2 space-y-1">
                    @forelse($result['summary']['next_paths'] as $item)
                        <div class="flex justify-between text-xs border-b lwa-border py-1"><span class="font-mono truncate">{{ $item['path'] }}</span><span>{{ number_format($item['count']) }}</span></div>
                    @empty
                        <div class="lwa-subtitle">No recorded next path.</div>
                    @endforelse
                </div>
            </div>
            <div class="lwa-card p-4">
                <div class="lwa-card-title">Previous paths</div>
                <div class="mt-2 space-y-1">
                    @forelse($result['summary']['previous_paths'] as $item)
                        <div class="flex justify-between text-xs border-b lwa-border py-1"><span class="font-mono truncate">{{ $item['path'] }}</span><span>{{ number_format($item['count']) }}</span></div>
                    @empty
                        <div class="lwa-subtitle">No recorded previous path.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Top paths</div></div>
        @if(empty($result['paths']))
            <div class="p-4 lwa-empty"><div class="lwa-empty-title">No requests in this window</div><p>Select another range or path.</p></div>
        @else
            <div class="lwa-table-wrap">
                <table class="lwa-table w-full">
                    <thead><tr><th>Path</th><th>Visitors</th><th>Views</th><th>Bounce rate</th><th></th></tr></thead>
                    <tbody>
                        @foreach($result['paths'] as $item)
                            <tr>
                                <td class="font-mono">{{ $item['path'] }}</td>
                                <td>{{ number_format($item['visitors']) }}</td>
                                <td>{{ number_format($item['views']) }}</td>
                                <td>{{ number_format($item['bounce_rate'], 2) }}%</td>
                                <td><a href="{{ route('lwa_page_reports', ['path' => $item['path'], 'range' => $range, 'compare' => request()->boolean('compare') ? 1 : 0]) }}" class="lwa-btn-ghost">Open</a></td>
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
