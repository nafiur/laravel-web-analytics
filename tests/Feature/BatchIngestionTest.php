<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Jobs\ProcessEvent;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\DeadLetter;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\UnifiedEventMapper;
use Nafiur\WebAnalytics\Tests\TestCase;
use RuntimeException;

class BatchIngestionTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Batch Test', 'domain' => 'batch.test', 'tracking_id' => 'trk_batch',
        ]);
        $this->websiteId = (int) $website->id;

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    public function test_job_ingests_and_dedupes_a_batch(): void
    {
        $payload = ['website_id' => $this->websiteId, 'uuid' => 'job-1', 'name' => 'purchase', 'value' => 10];

        for ($i = 0; $i < 2; $i++) {
            (new ProcessEvent([$payload]))->handle(app(EventStore::class), app(UnifiedEventMapper::class));
        }

        $this->assertSame(1, AnalyticEvent::where('request_uuid', 'job-1')->count());
    }

    public function test_failed_batch_is_preserved_in_dead_letters(): void
    {
        $job = new ProcessEvent([
            ['website_id' => $this->websiteId, 'uuid' => 'job-2', 'name' => 'purchase'],
        ]);

        $job->failed(new RuntimeException('storage unavailable'));

        $letter = DeadLetter::firstOrFail();
        $this->assertSame($this->websiteId, (int) $letter->website_id);
        $this->assertSame('storage unavailable', $letter->error);
        $this->assertSame('purchase', $letter->payload[0]['name']);
        $this->assertSame(3, $job->tries);
        $this->assertSame([10, 60, 300], $job->backoff);
    }
}
