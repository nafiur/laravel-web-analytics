<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\DatabaseTrafficAnalytics;
use Nafiur\WebAnalytics\Services\TestAccountFilter;
use Nafiur\WebAnalytics\Tests\TestCase;

class TestAccountFilterTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('laravel-web-analytics.privacy.filter_test_accounts', true);
        config()->set('laravel-web-analytics.privacy.test_account_keys', ['qa_test']);
        config()->set('laravel-web-analytics.privacy.test_account_ips', ['10.0.0.9']);
        config()->set('laravel-web-analytics.privacy.test_account_sessions', ['s_test']);

        $website = Website::create([
            'name'        => 'Filter QA',
            'domain'      => 'filter.test',
            'tracking_id' => 'trk_filter',
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    public function test_filter_resolves_configured_accounts_and_request_toggle(): void
    {
        $filter = app(TestAccountFilter::class);

        $this->assertTrue($filter->enabled());
        $this->assertSame(['qa_test'], $filter->keys());
        $this->assertSame(['10.0.0.9'], $filter->ips());
        $this->assertSame(['s_test'], $filter->sessions());
        $this->assertContains('u_qa_test', $filter->visitorHashes());
        $this->assertContains(hash('sha256', 'qa_test'), $filter->visitorHashes());

        $this->assertFalse(TestAccountFilter::activeFromRequest());
        request()->merge(['exclude_test' => '1']);
        $this->assertTrue(TestAccountFilter::activeFromRequest());

        config()->set('laravel-web-analytics.privacy.test_account_keys', []);
        config()->set('laravel-web-analytics.privacy.test_account_ips', []);
        config()->set('laravel-web-analytics.privacy.test_account_sessions', []);
        $this->assertFalse($filter->enabled());
    }

    public function test_dashboard_request_logs_exclude_test_sessions_and_ips(): void
    {
        $now = now();
        foreach ([['s_real', '127.0.0.1'], ['s_test', '127.0.0.1'], ['s_ip', '10.0.0.9']] as [$session, $ip]) {
            DB::table('lwa_logs')->insert([
                'ip_address' => $ip, 'session_id' => $session, 'method' => 'GET',
                'path' => '/products', 'full_url' => 'https://filter.test/products',
                'status_code' => 200, 'response_time_ms' => 10, 'bytes_sent' => 100,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        request()->merge(['exclude_test' => '1']);
        $analytics = app(DatabaseTrafficAnalytics::class)->getDashboardData('30d', null, null, null, true);
        $this->assertSame(1, $analytics['summary']['total_requests']);
    }

    public function test_event_store_queries_exclude_configured_visitors(): void
    {
        app(EventStore::class)->insert([
            [
                'website_id'   => $this->websiteId,
                'visitor_hash' => 'u_real',
                'name'         => 'purchase',
                'properties'   => [],
                'created_at'   => now()->toIso8601String(),
                'request_uuid' => (string) Str::uuid(),
            ],
            [
                'website_id'   => $this->websiteId,
                'visitor_hash' => 'u_qa_test',
                'name'         => 'purchase',
                'properties'   => [],
                'created_at'   => now()->toIso8601String(),
                'request_uuid' => (string) Str::uuid(),
            ],
        ]);

        $store = app(EventStore::class);
        $this->assertCount(2, iterator_to_array($store->query(new EventQuery(websiteId: $this->websiteId, name: 'purchase'))));

        request()->merge(['exclude_test' => '1']);
        $this->assertCount(1, iterator_to_array($store->query(new EventQuery(websiteId: $this->websiteId, name: 'purchase'))));
    }

    public function test_subheader_toggle_only_appears_when_configured(): void
    {
        $this->get(route('lwa_dashboard'))->assertOk()->assertSee('Filter test accounts');

        config()->set('laravel-web-analytics.privacy.filter_test_accounts', false);
        $this->get(route('lwa_dashboard'))->assertOk()->assertDontSee('Filter test accounts');
    }
}
