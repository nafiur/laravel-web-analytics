<?php

namespace Nafiur\WebAnalytics\Jobs;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\UnifiedEvent;

/**
 * Queued ingestion job for batches of normalized events.
 *
 * Persistence-level idempotency (firstOrCreate on the event uuid) makes
 * retries and redeliveries naturally duplicate-free, so the job is safe to
 * requeue without producing duplicate rows.
 *
 * @param array<int, UnifiedEvent|array<string, mixed>> $events
 */
class ProcessEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /** @param array<int, UnifiedEvent|array<string, mixed>> $events */
    public function __construct(public array $events = [])
    {
        $queue = config('laravel-web-analytics.analytics.queue');
        if ($queue) {
            $this->onQueue($queue);
        }
    }

    public function handle(EventStore $store): void
    {
        $store->insert(array_map(
            fn (UnifiedEvent|array $event): UnifiedEvent =>
                $event instanceof UnifiedEvent ? $event : $this->fromPayload($event),
            $this->events,
        ));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function fromPayload(array $payload): UnifiedEvent
    {
        $timestamp = isset($payload['timestamp']) && $payload['timestamp'] !== null
            ? CarbonImmutable::parse((string) $payload['timestamp'])
            : CarbonImmutable::now();

        $distinctId = isset($payload['distinct_id']) && $payload['distinct_id'] !== null
            ? (string) $payload['distinct_id']
            : null;
        $anonymousId = isset($payload['anonymous_id']) && $payload['anonymous_id'] !== null
            ? (string) $payload['anonymous_id']
            : null;

        return new UnifiedEvent(
            uuid:         (string) ($payload['uuid'] ?? ''),
            name:         (string) ($payload['name'] ?? ''),
            timestamp:    $timestamp,
            distinctId:  $distinctId,
            anonymousId: $anonymousId,
            sessionId:    isset($payload['session_id']) ? (string) $payload['session_id'] : null,
            websiteId:   (int) ($payload['website_id'] ?? 0),
            properties:  is_array($payload['properties'] ?? null) ? $payload['properties'] : [],
            context:      is_array($payload['context'] ?? null) ? $payload['context'] : [],
            value:        isset($payload['value']) ? (float) $payload['value'] : null,
            createdAt:    isset($payload['created_at']) ? CarbonImmutable::parse((string) $payload['created_at']) : $timestamp,
        );
    }
}
