<?php

namespace Nafiur\WebAnalytics\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Nafiur\WebAnalytics\Contracts\StorageDriver;

class RecordEvent implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public array $payload)
    {
        $queue = config('laravel-web-analytics.analytics.queue');
        if ($queue) {
            $this->onQueue($queue);
        }
    }

    public function handle(StorageDriver $storage): void
    {
        $storage->storeEvent($this->payload);
    }
}
