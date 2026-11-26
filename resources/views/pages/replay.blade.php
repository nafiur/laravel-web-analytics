@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Session Replay</h1>
        <p class="lwa-subtitle mt-1">Recorded sessions are stored verbatim on your configured disk. Index rows are scoped to the active website and tenant; chunks are never parsed or enriched on the server.</p>
    </div>

    @if($path !== null)
        <div class="lwa-card p-3 flex items-center justify-between gap-3 text-xs">
            <span>Filtered to path <code class="font-mono">{{ $path }}</code></span>
            <a href="{{ route('lwa_replay') }}" class="lwa-btn-ghost">Clear filter</a>
        </div>
    @endif

    <div class="lwa-card p-4">
        <div class="lwa-card-title">Privacy defaults</div>
        <p class="lwa-subtitle mt-1">
            Replay is opt-in. Guests are not recorded by default, every input is masked before capture, and recording only begins when the host page grants the separate replay consent.
            Chunks are retained for {{ $retentionDays }} days by the scheduled prune command. Access to this page is controlled by the <code class="font-mono">{{ $replayGate }}</code> gate.
        </p>
    </div>

    <div class="lwa-card p-4">
        <div class="flex items-center justify-between gap-3 mb-3">
            <div class="lwa-card-title">Recorded sessions</div>
            <span class="text-xs" style="color: var(--lwa-muted)">Most recent 50</span>
        </div>

        @if($sessions->isEmpty())
            <div class="lwa-empty">
                <div class="lwa-empty-title">No recordings yet</div>
                <p>Enable replay, grant consent on a host page, and upload a chunk to see it here.</p>
            </div>
        @else
            <div class="lwa-table-wrap">
                <table class="lwa-table w-full">
                    <thead>
                        <tr>
                            <th>Session</th>
                            <th>User</th>
                            <th>Started</th>
                            <th>Duration</th>
                            <th>Events</th>
                            <th>Bytes</th>
                            <th>Chunks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $session)
                            <tr>
                                <td class="font-mono">
                                    <a href="{{ route('lwa_replay_show', $session->id) }}" style="color: var(--lwa-accent)" class="hover:underline">Play</a>
                                    <span class="ml-2">{{ $session->session_key }}</span>
                                </td>
                                <td class="font-mono">{{ $session->user_id ?? '—' }}</td>
                                <td>{{ $session->started_at?->format('Y-m-d H:i:s') ?? '—' }}</td>
                                <td>{{ number_format($session->duration_ms / 1000, 1) }}s</td>
                                <td>{{ number_format($session->event_count) }}</td>
                                <td>{{ number_format($session->bytes) }}</td>
                                <td>{{ number_format($session->chunks_count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="lwa-subtitle mt-3">Playback shows stored snapshots and bounded markers; it is not a full-fidelity DOM replay.</p>
        @endif
    </div>
@endsection
