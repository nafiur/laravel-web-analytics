<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;
use Nafiur\WebAnalytics\Services\TenantResolver;
use Nafiur\WebAnalytics\Services\WebsiteResolver;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Playback routes. Every request re-checks the configured gate and the
 * website/tenant scope, including asset requests, so a signed URL or a
 * guessed sequence number can never bypass authorization.
 */
class ReplayPlayerController extends Controller
{
    public function show(int $replaySession): View
    {
        $session = $this->session($replaySession);

        return view('web-analytics::pages.replay-show', [
            'section'      => 'replay',
            'session'      => $session,
            'manifestUrl'  => route('lwa_replay_manifest', $session->id),
            'chunkUrl'     => route('lwa_replay_chunk', [$session->id, '__SEQ__']),
        ]);
    }

    public function manifest(int $replaySession): JsonResponse
    {
        $session = $this->session($replaySession);

        return response()->json([
            'session' => [
                'id'          => $session->id,
                'session_key' => $session->session_key,
                'user_id'     => $session->user_id,
                'url'         => $session->url,
                'started_at'  => $session->started_at?->toIso8601String(),
                'duration_ms' => $session->duration_ms,
                'event_count' => $session->event_count,
                'bytes'       => $session->bytes,
            ],
            'chunks' => $session->chunks()->orderBy('sequence')->get([
                'sequence', 'from_ms', 'to_ms', 'event_count', 'bytes', 'encoding', 'format',
            ])->map(fn ($chunk) => [
                'sequence'    => $chunk->sequence,
                'from_ms'     => $chunk->from_ms,
                'to_ms'       => $chunk->to_ms,
                'event_count' => $chunk->event_count,
                'bytes'       => $chunk->bytes,
                'encoding'    => $chunk->encoding,
                'format'      => $chunk->format ?: 'snapshot',
            ])->all(),
            'markers' => $session->markers()->orderBy('at_ms')->limit(200)->get([
                'type', 'at_ms', 'payload',
            ])->map(fn ($marker) => [
                'type'   => $marker->type,
                'at_ms'  => $marker->at_ms,
                'payload' => $marker->payload,
            ])->all(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function chunk(int $replaySession, int $sequence): SymfonyResponse
    {
        $session = $this->session($replaySession);
        $chunk = $session->chunks()->where('sequence', $sequence)->firstOrFail();

        $diskName = (string) (config('laravel-web-analytics.replay.disk') ?: config('filesystems.default', 'local'));
        $disk = Storage::disk($diskName);
        if (! $disk->exists($chunk->disk_path)) {
            abort(404, 'Replay chunk is no longer available.');
        }

        return response($disk->get($chunk->disk_path), 200, [
            'Content-Type'              => $chunk->encoding === 'gzip' ? 'application/gzip' : 'application/json',
            'Cache-Control'             => 'private, no-store',
            'X-Content-Type-Options'    => 'nosniff',
        ]);
    }

    private function session(int $id): ReplaySession
    {
        if (! config('laravel-web-analytics.replay.enabled', false)) {
            abort(404);
        }

        Gate::authorize((string) config('laravel-web-analytics.replay.gate', 'viewLwaReplay'));

        $websiteId = app(WebsiteResolver::class)->currentId();

        return ReplaySession::query()
            ->forWebsite($websiteId)
            ->forTenant(TenantResolver::resolveTrusted())
            ->findOrFail($id);
    }
}
