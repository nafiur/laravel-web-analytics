<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Services\PageReportsService;
use Nafiur\WebAnalytics\Tests\TestCase;

class PageReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('laravel-web-analytics.page_reports.enabled', true);
    }

    private function row(string $session, string $path, $at, string $ip = '127.0.0.1'): void
    {
        DB::table('lwa_logs')->insert([
            'ip_address'       => $ip,
            'session_id'       => $session,
            'method'           => 'GET',
            'path'             => $path,
            'full_url'         => 'https://example.test' . $path,
            'status_code'      => 200,
            'response_time_ms' => 20,
            'bytes_sent'       => 1024,
            'created_at'       => $at,
            'updated_at'       => $at,
        ]);
    }

    private function seedRows(): void
    {
        $this->row('page_s1', '/landing', now()->subDays(3));
        $this->row('page_s1', '/pricing', now()->subDays(2));
        $this->row('page_s2', '/landing', now()->subDay());
        $this->row('page_s3', '/checkout', now()->subDay());
        $this->row('page_s3', '/thankyou', now()->subDay()->addMinute());
        $this->row('page_prior', '/landing', now()->subDays(40));
    }

    public function test_page_report_aggregates_visitors_views_bounce_and_paths(): void
    {
        $this->seedRows();

        $result = app(PageReportsService::class)->report(null, null, '30d', '/landing', false);

        $this->assertSame(5, $result['sample_size']);
        $this->assertFalse($result['capped']);
        $this->assertSame(2, $result['summary']['visitors']);
        $this->assertSame(2, $result['summary']['views']);
        $this->assertSame(2, $result['summary']['entries']);
        $this->assertSame(50.0, $result['summary']['bounce_rate']);
        $this->assertSame('/pricing', $result['summary']['next_paths'][0]['path']);
        $this->assertSame('lwa_logs rows in the selected window, bounded to the 20,000 most recent requests.', $result['definitions']['source']);

        $landing = collect($result['paths'])->firstWhere('path', '/landing');
        $this->assertSame(2, $landing['views']);
        $this->assertSame(2, $landing['visitors']);
        $this->assertSame(50.0, $landing['bounce_rate']);
    }

    public function test_page_report_can_compare_with_the_previous_window(): void
    {
        $this->seedRows();

        $result = app(PageReportsService::class)->report(null, null, '30d', '/landing', true);

        $this->assertNotNull($result['comparison']);
        $this->assertSame(100.0, $result['comparison']['total_visitors']['change']);
        $this->assertSame(100.0, $result['comparison']['pageviews']['change']);
        $this->assertSame('points', $result['comparison']['bounce_rate']['unit']);
    }

    public function test_page_reports_link_to_filtered_recordings_when_replay_is_enabled(): void
    {
        config()->set('laravel-web-analytics.replay.enabled', true);
        $this->seedRows();

        $this->get(route('lwa_page_reports'))
            ->assertOk()
            ->assertSee(route('lwa_replay', ['path' => '/landing']), false)
            ->assertSee('Recordings');
    }

    public function test_page_reports_page_filters_and_hides_when_disabled(): void
    {
        $this->seedRows();

        $this->get(route('lwa_page_reports'))
            ->assertOk()
            ->assertSee('Page Reports')
            ->assertSee('Top paths')
            ->assertSee('/landing')
            ->assertSee('Definitions');

        $this->get(route('lwa_page_reports', ['path' => '/landing', 'range' => '7d', 'compare' => 1]))
            ->assertOk()
            ->assertSee('value="/landing"', false)
            ->assertSee('Bounce Rate')
            ->assertSee('Compare prior');

        config()->set('laravel-web-analytics.page_reports.enabled', false);
        $this->get(route('lwa_page_reports'))->assertNotFound();
    }
}
