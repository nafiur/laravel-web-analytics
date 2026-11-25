<?php

namespace Nafiur\WebAnalytics\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Models\Analytics\DeadLetter;
use Nafiur\WebAnalytics\Services\UnifiedEventMapper;
use Nafiur\WebAnalytics\Services\WebsiteScope;

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

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    /** @param array<int, UnifiedEvent|array<string, mixed>> $events */
    public function __construct(public array $events = [])
    {
        $queue = config('laravel-web-analytics.analytics.queue');
        if ($queue) {
            $this->onQueue($queue);
        }
    }

    public function handle(EventStore $store, UnifiedEventMapper $mapper): void
    {
        $store->insert(array_map(
            fn (UnifiedEvent|array $event): UnifiedEvent =>
                $event instanceof UnifiedEvent
                    ? $event
                    : $mapper->mapPayload($event, (int) ($event['website_id'] ?? config('laravel-web-analytics.analytics.website_id', 0))),
            $this->events,
        ));
    }

    /**
     * After the configured retries are exhausted the batch is preserved for
     * inspection instead of being silently dropped.
     */
    public function failed(?\Throwable $exception = null): void
    {
        DeadLetter::withoutGlobalScope(WebsiteScope::class)->create([
            'website_id' => (int) ($this->events[0]['website_id'] ?? config('laravel-web-analytics.analytics.website_id', 0)) ?: null,
            'payload'    => array_map(
                fn (UnifiedEvent|array $event): array =>
                    $event instanceof UnifiedEvent ? $event->toStoragePayload() : $event,
                $this->events,
            ),
            'error'      => $exception?->getMessage(),
            'attempts'   => $this->attempts(),
            'created_at' => now(),
        ]);
    }
}
