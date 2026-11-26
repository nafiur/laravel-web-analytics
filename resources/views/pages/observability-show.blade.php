@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">{{ $detail['group']['type'] }}</h1>
        <p class="lwa-subtitle mt-1">Fingerprint <code class="font-mono">{{ $detail['group']['fingerprint'] }}</code> · {{ number_format($detail['group']['occurrences']) }} occurrence(s) · {{ $detail['group']['status'] }}</p>
    </div>

    <div class="grid md:grid-cols-3 gap-3">
        <div class="lwa-card p-4"><div class="lwa-kpi-label">Severity</div><div class="lwa-kpi-value text-base">{{ $detail['group']['severity'] }}</div></div>
        <div class="lwa-card p-4"><div class="lwa-kpi-label">File</div><div class="font-mono text-xs mt-2 break-all">{{ $detail['group']['file'] ?: '—' }}:{{ $detail['group']['line'] ?: '—' }}</div></div>
        <div class="lwa-card p-4">
            <div class="lwa-kpi-label">Status</div>
            <form method="POST" action="{{ route('lwa_observability_status', $detail['group']['id']) }}" class="flex gap-2 mt-2">
                @csrf
                <select name="status" class="lwa-select">
                    @foreach(['open' => 'Open', 'resolved' => 'Resolved', 'ignored' => 'Ignored'] as $value => $label)
                        <option value="{{ $value }}" {{ $detail['group']['status'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="lwa-btn-primary">Save</button>
            </form>
        </div>
    </div>

    <div class="lwa-card p-4">
        <div class="lwa-card-title">Message</div>
        <pre class="mt-2 text-xs whitespace-pre-wrap break-all" style="color: var(--lwa-muted)">{{ $detail['group']['message'] }}</pre>
    </div>

    @forelse($detail['events'] as $event)
        <div class="lwa-card overflow-hidden">
            <div class="p-4 border-b lwa-border flex justify-between gap-3 text-xs">
                <span class="font-mono">{{ $event['event_uuid'] }}</span>
                <span style="color: var(--lwa-muted)">{{ $event['occurred_at'] ? \Illuminate\Support\Carbon::parse($event['occurred_at'])->format('Y-m-d H:i:s') : '—' }}</span>
            </div>
            @if(!empty($event['context']))
                <div class="p-4 border-b lwa-border text-[11px]" style="color: var(--lwa-muted)">
                    @foreach($event['context'] as $key => $value)<span class="mr-3"><span class="font-mono">{{ $key }}</span>: {{ $value }}</span>@endforeach
                </div>
            @endif
            <div class="p-4">
                <pre class="text-[11px] whitespace-pre-wrap break-all" style="color: var(--lwa-muted)">{{ $event['stack'] ?? 'No stack recorded.' }}</pre>
            </div>
        </div>
    @empty
        <div class="lwa-card p-4 lwa-empty"><div class="lwa-empty-title">No events</div><p>This group has no stored occurrences.</p></div>
    @endforelse
@endsection
