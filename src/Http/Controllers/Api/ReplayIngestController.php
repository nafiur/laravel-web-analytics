<?php

namespace Nafiur\WebAnalytics\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Nafiur\WebAnalytics\Models\Analytics\ReplayChunk;
use Nafiur\WebAnalytics\Models\Analytics\ReplayMarker;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;

class ReplayIngestController extends Controller
{
    private const MARKER_TYPES = [
        'click', 'scroll', 'pageview', 'error', 'failed_request',
        'web_vital', 'rage_click', 'console', 'custom',
        'field_focus', 'field_change', 'form_submit', 'form_error',
    ];

    public function store(Request $request): JsonResponse
    {
        /** @var array<string, mixed> $signed */
        $signed = $request->attributes->get('lwa_replay_payload', []);
        $websiteId = (int) ($signed['website_id'] ?? 0);
        $tenantId = $signed['tenant_id'] ?? null;
        $userId = $signed['user_id'] ?? null;

        if ($userId === null || $userId === '' || $userId === false) {
            if (! config('laravel-web-analytics.replay.record_guests', false)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Guest replay is disabled',
                ], 403);
            }
        }

        $data = $request->validate([
            'session_key' => 'required|string|max:64',
            'sequence'    => 'required|integer|min:0|max:1000000',
            'chunk'       => 'required|string',
            'encoding'    => 'sometimes|in:gzip,identity',
            'from_ms'     => 'sometimes|integer|min:0',
            'to_ms'       => 'sometimes|integer|min:0',
            'event_count' => 'sometimes|integer|min:0|max:100000',
            'markers'     => 'sometimes|array|max:25',
        ]);

        if (! hash_equals((string) ($signed['session_key'] ?? ''), (string) $data['session_key'])) {
            return response()->json([
                'success' => false,
                'message' => 'Session key does not match the signed configuration',
            ], 403);
        }

        $bytes = base64_decode((string) $data['chunk'], true);
        $maxBytes = (int) config('laravel-web-analytics.replay.max_chunk_bytes', 1000000);
        if ($bytes === false || $bytes === '' || strlen($bytes) > $maxBytes) {
            return response()->json([
                'success' => false,
                'message' => 'Replay chunk is invalid or exceeds the configured size limit',
            ], 422);
        }

        $encoding = (string) ($data['encoding'] ?? 'gzip');
        $sessionKey = (string) $signed['session_key'];
        $safeKey = preg_replace('/[^A-Za-z0-9_-]/', '', $sessionKey) ?: 'session';
        $sequence = (int) $data['sequence'];
        $extension = $encoding === 'gzip' ? 'json.gz' : 'json';
        $path = 'lwa-replay/sessions/' . $safeKey . '/' . $sequence . '.' . $extension;

        $diskName = (string) (config('laravel-web-analytics.replay.disk') ?: config('filesystems.default', 'local'));
        Storage::disk($diskName)->put($path, $bytes);

        $eventCount = (int) ($data['event_count'] ?? 0);
        $fromMs = (int) ($data['from_ms'] ?? 0);
        $toMs = (int) ($data['to_ms'] ?? $fromMs);

        $session = ReplaySession::firstOrNew(['session_key' => $sessionKey]);
        if ($session->exists && (int) $session->website_id !== $websiteId) {
            return response()->json([
                'success' => false,
                'message' => 'Session belongs to another website',
            ], 403);
        }
        if (! $session->exists) {
            $session->started_at = now();
            $session->url = mb_substr(ltrim((string) ($signed['path'] ?? ''), '/'), 0, 500);
            $session->sample_rate = (float) ($signed['sample_rate'] ?? 0);
        }
        $session->fill([
            'website_id' => $websiteId,
            'tenant_id'  => $tenantId,
            'user_id'    => $userId === null || $userId === '' || $userId === false ? null : (string) $userId,
        ]);
        $session->save();

        $chunk = ReplayChunk::firstOrCreate([
            'replay_session_id' => $session->id,
            'sequence'          => $sequence,
        ], [
            'website_id'  => $websiteId,
            'tenant_id'   => $tenantId,
            'disk_path'   => $path,
            'encoding'    => $encoding,
            'bytes'       => strlen($bytes),
            'event_count' => $eventCount,
            'from_ms'     => $fromMs,
            'to_ms'       => $toMs,
        ]);

        if ($chunk->wasRecentlyCreated) {
            $session->event_count += $eventCount;
            $session->bytes += strlen($bytes);
            $session->duration_ms = max($session->duration_ms, $toMs);
            $session->ended_at = now();
            $session->save();

            if (! empty($data['markers'])) {
                foreach (array_slice($data['markers'], 0, (int) config('laravel-web-analytics.replay.max_markers', 25)) as $marker) {
                    if (! is_array($marker) || ! in_array((string) ($marker['type'] ?? ''), self::MARKER_TYPES, true)) {
                        continue;
                    }

                    $payload = is_array($marker['payload'] ?? null) ? $marker['payload'] : [];
                    $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES);
                    if (is_string($encoded) && strlen($encoded) > 4096) {
                        $payload = ['truncated' => true];
                    }

                    ReplayMarker::create([
                        'replay_session_id' => $session->id,
                        'website_id'        => $websiteId,
                        'tenant_id'         => $tenantId,
                        'type'              => (string) $marker['type'],
                        'at_ms'             => max(0, (int) ($marker['at_ms'] ?? 0)),
                        'payload'           => $payload,
                    ]);
                }
            }
        }

        return response()->json([
            'success'     => true,
            'session_id'  => $session->id,
            'sequence'    => $sequence,
            'bytes'       => strlen($bytes),
            'disk_path'   => $path,
            'disk'        => $diskName,
        ], 202);
    }
}
