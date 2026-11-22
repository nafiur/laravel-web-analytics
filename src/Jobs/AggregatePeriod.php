<?php

namespace Nafiur\WebAnalytics\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Nafiur\WebAnalytics\Services\AggregationService;

class AggregatePeriod implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public int $websiteId,
        public string $bucketStart,
        public string $granularity = 'hourly',
    ) {
        $queue = config('laravel-web-analytics.analytics.queue');
        if ($queue) {
            $this->onQueue($queue);
        }
    }

    public function handle(AggregationService $aggregation): void
    {
        $aggregation->aggregate(
            $this->websiteId,
            Carbon::parse($this->bucketStart),
            $this->granularity
        );
    }
}
