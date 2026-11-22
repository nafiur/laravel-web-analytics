<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Nafiur\WebAnalytics\Http\Middleware\TrackWebTraffic;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_known_crawler_is_captured_in_known_mode(): void
    {
        config()->set('laravel-web-analytics.capture.bots', 'known');

        $this->track($this->request('/bot', [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ]));

        $this->assertSame(1, LwaLogs::count());
    }

    public function test_generic_scraper_is_skipped_in_known_mode(): void
    {
        config()->set('laravel-web-analytics.capture.bots', 'known');

        $this->track($this->request('/scrape', ['HTTP_USER_AGENT' => 'python-requests/2.31.0']));

        $this->assertSame(0, LwaLogs::count());
    }

    public function test_bots_are_captured_when_enabled(): void
    {
        config()->set('laravel-web-analytics.capture.bots', true);

        $this->track($this->request('/bot', ['HTTP_USER_AGENT' => 'AhrefsBot/7.0']));

        $this->assertSame(1, LwaLogs::count());
    }

    public function test_do_not_track_header_skips_tracking(): void
    {
        $this->track($this->request('/dnt', ['HTTP_DNT' => '1']));

        $this->assertSame(0, LwaLogs::count());
    }

    public function test_fingerprint_strategy_uses_cookieless_daily_id(): void
    {
        config()->set('laravel-web-analytics.privacy.visitor_id_strategy', 'fingerprint');
        config()->set('laravel-web-analytics.privacy.fingerprint_salt', 'test-salt');

        $server = ['REMOTE_ADDR' => '8.8.8.8', 'HTTP_USER_AGENT' => 'TestBrowser/1.0'];
        $this->track($this->request('/a', $server));
        $this->track($this->request('/b', $server));

        $ids = LwaLogs::pluck('session_id')->all();

        $this->assertCount(2, $ids);
        $this->assertSame($ids[0], $ids[1]);
        $this->assertStringStartsWith('f_', $ids[0]);
    }

    public function test_utm_parameters_are_stored_and_aggregated(): void
    {
        $this->track($this->request('/?utm_source=google&utm_medium=cpc&utm_campaign=spring'));

        $this->assertDatabaseHas('lwa_logs', [
            'utm_source'   => 'google',
            'utm_medium'   => 'cpc',
            'utm_campaign' => 'spring',
        ]);

        $sources = $this->getJson('/web-analytics/data?range=all')->json('analytics.breakdowns.top_utm_sources');
        $this->assertSame('google', $sources[0]['name']);
    }

    public function test_command_aliases_are_registered(): void
    {
        $this->artisan('analytics:prune', ['--days' => 30])->assertExitCode(0);
        $this->artisan('analytics:summary')->assertExitCode(0);
    }

    public function test_export_command_writes_csv(): void
    {
        $this->track($this->request('/export-check'));
        $path = tempnam(sys_get_temp_dir(), 'lwa_export_');

        try {
            $this->artisan('lwa:export', ['--range' => 'all', '--output' => $path])->assertExitCode(0);
            $this->assertStringContainsString('UTM Source', file_get_contents($path));
            $this->assertStringContainsString('/export-check', file_get_contents($path));
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /** @param array<string, string> $server */
    private function request(string $uri, array $server = []): Request
    {
        return Request::create($uri, 'GET', [], [], [], $server + [
            'REMOTE_ADDR'     => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) Chrome/129.0.0.0 Safari/537.36',
        ]);
    }

    private function track(Request $request, ?SymfonyResponse $response = null): void
    {
        $response = $response ?: new SymfonyResponse('ok', 200, ['Content-Length' => 2]);
        $middleware = new TrackWebTraffic();
        $middleware->handle($request, fn () => $response);
        $middleware->terminate($request, $response);
    }
}
