<?php

namespace Nafiur\WebAnalytics\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Services\UnifiedEventMapper;

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
}
