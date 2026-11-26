@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Observability</h1>
        <p class="lwa-subtitle mt-1">Exception groups recorded through <code class="font-mono">lwa_events</code> and the dedicated error tables. Stack traces are bounded, sanitised and visible only behind the <code class="font-mono">{{ $gate }}</code> gate.</p>
    </div>

    @if(session('lwa_success'))<div class="lwa-card p-3 mb-3 text-xs" style="color: var(--lwa-success)">{{ session('lwa_success') }}</div>@endif

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_observability') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="lwa-field-label" for="errorStatus">Status</label>
                <select id="errorStatus" name="status" class="lwa-select">
                    @foreach(['' => 'All', 'open' => 'Open', 'resolved' => 'Resolved', 'ignored' => 'Ignored'] as $value => $label)
                        <option value="{{ $value }}" {{ $status === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="lwa-btn-primary">Apply</button>
        </form>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Exception groups</div></div>
        @if(empty($groups))
            <div class="p-4 lwa-empty">
                <div class="lwa-empty-title">No errors recorded</div>
                <p>Post sanitised error reports to <code class="font-mono">POST /web-analytics/api/v1/observability/errors</code> to populate this view.</p>
            </div>
        @else
            <div class="lwa-table-wrap">
                <table class="lwa-table w-full">
                    <thead><tr><th>Type</th><th>Message</th><th>Location</th><th>Severity</th><th>Count</th><th>Last seen</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @foreach($groups as $group)
                            <tr>
                                <td class="font-mono text-[11px]">{{ $group['type'] }}</td>
                                <td class="max-w-[280px] truncate">{{ $group['message'] }}</td>
                                <td class="font-mono text-[11px]">{{ $group['file'] ?: '—' }}{{ $group['line'] ? ':' . $group['line'] : '' }}</td>
                                <td>{{ $group['severity'] }}</td>
                                <td>{{ number_format($group['occurrences']) }}</td>
                                <td>{{ $group['last_seen'] ? \Illuminate\Support\Carbon::parse($group['last_seen'])->format('Y-m-d H:i:s') : '—' }}</td>
                                <td>{{ $group['status'] }}</td>
                                <td><a href="{{ route('lwa_observability_show', $group['id']) }}" class="lwa-btn-ghost">Open</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
