@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Replay {{ $session->session_key }}</h1>
        <p class="lwa-subtitle mt-1">
            Stored snapshots and bounded markers from <code class="font-mono">lwa_replay_chunks</code>. Playback is intentionally minimal — it is not a full-fidelity DOM replay.
        </p>
    </div>

    <div class="lwa-card p-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div><span class="lwa-field-label">User</span><div class="font-mono mt-1">{{ $session->user_id ?? '—' }}</div></div>
            <div><span class="lwa-field-label">Started</span><div class="mt-1">{{ $session->started_at?->format('Y-m-d H:i:s') ?? '—' }}</div></div>
            <div><span class="lwa-field-label">Duration</span><div class="mt-1">{{ number_format($session->duration_ms / 1000, 1) }}s</div></div>
            <div><span class="lwa-field-label">Events</span><div class="mt-1">{{ number_format($session->event_count) }}</div></div>
        </div>
    </div>

    <div id="lwaReplayPlayer"
         class="lwa-card overflow-hidden"
         data-manifest-url="{{ $manifestUrl }}"
         data-chunk-template="{{ $chunkUrl }}">
        <div class="p-4 flex items-center justify-between gap-3 border-b lwa-border">
            <div class="lwa-card-title">Playback</div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="lwaReplayPrev" class="lwa-btn-ghost" aria-label="Previous chunk">Prev</button>
                <button type="button" id="lwaReplayPlay" class="lwa-btn-primary">Play</button>
                <button type="button" id="lwaReplayNext" class="lwa-btn-ghost" aria-label="Next chunk">Next</button>
                <label class="flex items-center gap-1 text-[11px]" style="color: var(--lwa-muted)">
                    Speed
                    <select id="lwaReplaySpeed" class="lwa-select" aria-label="Replay speed">
                        <option value="2400">0.5x</option>
                        <option value="1200" selected>1x</option>
                        <option value="600">2x</option>
                    </select>
                </label>
                <button type="button" id="lwaReplayInspect" class="lwa-btn-ghost" aria-expanded="false" aria-controls="lwaReplayDom">Inspect DOM</button>
            </div>
        </div>
        <div class="p-4 border-b lwa-border">
            <input id="lwaReplaySeek" type="range" min="0" max="0" value="0" step="1" class="w-full" aria-label="Replay timeline">
            <div class="flex justify-between text-[11px] mt-1" style="color: var(--lwa-muted)">
                <span id="lwaReplayTime">0.0s</span>
                <span id="lwaReplayChunk">No chunks</span>
            </div>
        </div>
        <div class="grid md:grid-cols-3">
            <div class="md:col-span-2 p-4 border-r lwa-border">
                <iframe id="lwaReplayFrame" title="Replay snapshot" sandbox="" class="w-full" style="height: 460px; border: 1px solid var(--lwa-border); border-radius: 6px; background: #fff" srcdoc="&lt;p&gt;Loading snapshot…&lt;/p&gt;"></iframe>
                <pre id="lwaReplayDom" hidden class="mt-3 p-3 text-[10px] whitespace-pre-wrap break-all lwa-surface-muted" style="max-height: 360px; overflow: auto; border: 1px solid var(--lwa-border); border-radius: 6px"></pre>
            </div>
            <div class="p-4">
                <div class="lwa-card-title mb-2">Markers</div>
                <div id="lwaReplayMarkers" class="space-y-1 text-xs" style="color: var(--lwa-muted)">Loading…</div>
            </div>
        </div>
        <div class="p-4 border-t lwa-border text-[11px]" style="color: var(--lwa-muted)">
            Source: <code class="font-mono">lwa_replay_chunks</code> and <code class="font-mono">lwa_replay_markers</code>. Asset requests are re-gated on every load.
        </div>
    </div>

    <script defer src="{{ route('lwa_replay_player_bundle') }}"></script>
@endsection
