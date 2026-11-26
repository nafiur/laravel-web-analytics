<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Models\Analytics\ErrorEvent;
use Nafiur\WebAnalytics\Models\Analytics\ErrorGroup;

/**
 * Stores error reports verbatim (bounded) in dedicated tables and mirrors a
 * safe summary into lwa_events. Website/tenant scope always comes from the
 * authenticated request, never the client payload.
 */
class ErrorTrackingService
{
    private const SENSITIVE_KEYS = ['token', 'password', 'secret', 'api_key', 'apikey', 'authorization', 'card', 'cvv'];
    private const CONTEXT_KEYS = ['url', 'path', 'method', 'release', 'environment', 'browser', 'replay_session_key', 'user_hash'];

    public function __construct(private EventStore $events)
    {
    }

    /** @param array<string, mixed> $payload @return array<string, mixed> */
    public function ingest(array $payload, int $websiteId, ?string $tenantId): array
    {
        $type = $this->clean((string) ($payload['type'] ?? 'Error'), 150);
        $message = $this->clean((string) ($payload['message'] ?? ''), 1000);
        if ($message === '') {
            $message = 'Empty error message';
        }
        $file = $this->clean((string) ($payload['file'] ?? ''), 255);
        $line = max(0, (int) ($payload['line'] ?? 0));
        $severity = in_array((string) ($payload['severity'] ?? 'error'), ['debug', 'info', 'warning', 'error', 'fatal'], true)
            ? (string) $payload['severity'] : 'error';

        $fingerprint = hash('sha256', $type . '|' . preg_replace('/\d+/', 'N', $message) . '|' . $file . '|' . $line);
        $uuid = trim((string) ($payload['event_id'] ?? ''));
        if ($uuid === '' || strlen($uuid) > 64 || ! preg_match('/^[A-Za-z0-9_-]+$/', $uuid)) {
            $uuid = (string) Str::uuid();
        }

        $existingEvent = ErrorEvent::withoutGlobalScopes()->where('event_uuid', $uuid)->first();
        if ($existingEvent !== null) {
            return [
                'duplicate'  => true,
                'group_id'   => (int) $existingEvent->error_group_id,
                'event_uuid' => $uuid,
            ];
        }

        $occurredAt = CarbonImmutable::now();
        $group = ErrorGroup::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId)
            ->where('fingerprint', $fingerprint)
            ->first();
        $isNew = $group === null;
        $group ??= new ErrorGroup();
        if ($isNew) {
            $group->first_seen_at = $occurredAt;
        }
        $group->fill([
            'website_id'   => $websiteId,
            'tenant_id'    => $tenantId,
            'fingerprint'  => $fingerprint,
            'type'         => $type,
            'message'      => $message,
            'file'         => $file !== '' ? $file : null,
            'line'         => $line > 0 ? $line : null,
            'severity'     => $severity,
            'status'       => $isNew ? 'open' : ($group->status === 'resolved' ? 'open' : $group->status),
            'resolved_at'  => $group->status === 'resolved' && ! $isNew ? null : $group->resolved_at,
            'last_seen_at' => $occurredAt,
        ]);
        $group->occurrences = (int) $group->occurrences + 1;
        $group->save();

        $stack = $this->cleanStack((string) ($payload['stack'] ?? ''));
        $context = $this->context($payload['context'] ?? []);

        ErrorEvent::create([
            'error_group_id' => $group->id,
            'website_id'     => $websiteId,
            'tenant_id'      => $tenantId,
            'event_uuid'     => $uuid,
            'occurred_at'    => $occurredAt,
            'severity'       => $severity,
            'context'        => $context,
            'stack'          => $stack !== '' ? $stack : null,
        ]);

        $this->events->insert([
            new UnifiedEvent(
                uuid: 'err_' . $uuid,
                name: 'error',
                timestamp: $occurredAt,
                distinctId: null,
                anonymousId: 'err_' . substr($fingerprint, 0, 16),
                sessionId: null,
                websiteId: $websiteId,
                properties: [
                    'fingerprint' => $fingerprint,
                    'type'        => $type,
                    'severity'    => $severity,
                    'file'        => $file,
                    'line'        => $line,
                ],
                context: [],
                value: null,
                createdAt: $occurredAt,
            ),
        ]);

        return [
            'duplicate'  => false,
            'group_id'   => $group->id,
            'event_uuid' => $uuid,
            'fingerprint' => $fingerprint,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function summarize(?int $websiteId, ?string $tenantId, ?string $status = null): array
    {
        $query = ErrorGroup::query()->forWebsite($websiteId)->forTenant($tenantId);
        if ($status !== null && in_array($status, ['open', 'resolved', 'ignored'], true)) {
            $query->where('status', $status);
        }

        return $query->latest('last_seen_at')->limit(100)->get()->map(fn (ErrorGroup $group) => [
            'id'          => $group->id,
            'fingerprint' => $group->fingerprint,
            'type'        => $group->type,
            'message'     => $group->message,
            'file'        => $group->file,
            'line'        => $group->line,
            'severity'    => $group->severity,
            'occurrences' => $group->occurrences,
            'status'      => $group->status,
            'first_seen'  => $group->first_seen_at?->toIso8601String(),
            'last_seen'   => $group->last_seen_at?->toIso8601String(),
        ])->all();
    }

    /** @return array<string, mixed>|null */
    public function detail(int $id, ?int $websiteId, ?string $tenantId): ?array
    {
        $group = ErrorGroup::query()->forWebsite($websiteId)->forTenant($tenantId)->find($id);
        if ($group === null) {
            return null;
        }

        return [
            'group' => [
                'id'          => $group->id,
                'fingerprint' => $group->fingerprint,
                'type'        => $group->type,
                'message'     => $group->message,
                'file'        => $group->file,
                'line'        => $group->line,
                'severity'    => $group->severity,
                'occurrences' => $group->occurrences,
                'status'      => $group->status,
            ],
            'events' => $group->events()->latest('occurred_at')->limit(50)->get()->map(fn (ErrorEvent $event) => [
                'event_uuid'  => $event->event_uuid,
                'occurred_at' => $event->occurred_at?->toIso8601String(),
                'severity'    => $event->severity,
                'context'     => $event->context ?? [],
                'stack'       => $event->stack,
            ])->all(),
        ];
    }

    public function setStatus(int $id, string $status, ?int $websiteId, ?string $tenantId): bool
    {
        $group = ErrorGroup::query()->forWebsite($websiteId)->forTenant($tenantId)->find($id);
        if ($group === null || ! in_array($status, ['open', 'resolved', 'ignored'], true)) {
            return false;
        }

        $group->status = $status;
        $group->resolved_at = $status === 'resolved' ? now() : null;
        $group->save();

        return true;
    }

    private function clean(string $value, int $max): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value) ?? '';
        $value = preg_replace_callback('/(?<key>[A-Za-z0-9_-]+)=([^\s&,]+)/i', function (array $match): string {
            $key = strtolower($match['key']);
            if (in_array($key, self::SENSITIVE_KEYS, true)) {
                return $match['key'] . '=[redacted]';
            }

            return $match[0];
        }, $value) ?? '';

        return mb_substr($value, 0, $max);
    }

    private function cleanStack(string $stack): string
    {
        $max = (int) config('laravel-web-analytics.observability.max_stack_bytes', 20000);

        return mb_substr($this->clean($stack, $max), 0, $max);
    }

    /** @param mixed $context @return array<string, string> */
    private function context($context): array
    {
        if (! is_array($context)) {
            return [];
        }
        $clean = [];
        foreach (self::CONTEXT_KEYS as $key) {
            if (isset($context[$key]) && is_scalar($context[$key])) {
                $value = $this->clean((string) $context[$key], 500);
                if ($key === 'url' || $key === 'path') {
                    $value = $this->stripQuery($value);
                }
                $clean[$key] = $value;
            }
        }

        return $clean;
    }

    private function stripQuery(string $value): string
    {
        $parts = parse_url($value);
        if (! is_array($parts) || ! isset($parts['path'])) {
            return mb_substr($value, 0, 500);
        }

        return mb_substr(($parts['scheme'] ?? '') . (isset($parts['scheme']) ? '://' : '') . ($parts['host'] ?? '') . $parts['path'], 0, 500);
    }
}
