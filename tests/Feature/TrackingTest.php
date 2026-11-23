<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Nafiur\WebAnalytics\Http\Middleware\TrackWebTraffic;
use Nafiur\WebAnalytics\Models\LwaLogs;
use Nafiur\WebAnalytics\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    public function test_tracking_records_request_metadata(): void
    {
        $request = $this->request('/hello?x=1', [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36',
            'HTTP_REFERER'    => 'https://example.com/page',
            'REMOTE_ADDR'     => '8.8.8.8',
        ]);
        $response = new SymfonyResponse('hello', 200, ['Content-Length' => 5]);

        $this->track($request, $response);

        $this->assertDatabaseHas('lwa_logs', [
            'path'        => '/hello',
            'method'      => 'GET',
            'status_code' => 200,
            'bytes_sent'  => 5,
            'ip_address'  => '8.8.8.8',
            'browser'     => 'Google Chrome',
            'os'          => 'Windows 10/11',
            'device'      => 'Desktop',
            'referrer'    => 'https://example.com/page',
        ]);
    }

    public function test_ignored_paths_are_not_recorded(): void
    {
        $this->track($this->request('/web-analytics/stream'), new SymfonyResponse('x'));

        $this->assertSame(0, LwaLogs::count());
    }

    public function test_bots_are_skipped_by_default(): void
    {
        $request = $this->request('/bot-page', [
            'HTTP_USER_AGENT' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        ]);

        $this->track($request, new SymfonyResponse('x'));

        $this->assertSame(0, LwaLogs::count());
    }

    public function test_forwarded_ip_headers_are_ignored_unless_trusted(): void
    {
        config()->set('laravel-web-analytics.capture.trust_forwarded_headers', false);

        $request = $this->request('/ip-check', [
            'REMOTE_ADDR'            => '8.8.8.8',
            'HTTP_CF_CONNECTING_IP'  => '1.1.1.1',
            'HTTP_X_FORWARDED_FOR'   => '2.2.2.2',
        ]);

        $this->track($request, new SymfonyResponse('x'));

        $this->assertDatabaseHas('lwa_logs', ['ip_address' => '8.8.8.8']);
    }

    public function test_ip_anonymization_masks_last_octet(): void
    {
        config()->set('laravel-web-analytics.anonymize_ip', true);

        $this->track($this->request('/anon', ['REMOTE_ADDR' => '192.168.1.123']), new SymfonyResponse('x'));

        $this->assertDatabaseHas('lwa_logs', ['ip_address' => '192.168.1.0']);
    }

    public function test_sensitive_query_parameters_are_masked(): void
    {
        $request = $this->request('/track?token=super-secret&utm_source=google');
        $response = new SymfonyResponse('ok', 200, ['Content-Length' => 2]);
        $request->headers->set('referer', 'https://ref.test/callback?access_token=abc&utm_medium=cpc');

        $this->track($request, $response);

        $log = \Nafiur\WebAnalytics\Models\LwaLogs::first();
        $this->assertStringContainsString('token=%5BREDACTED%5D', $log->full_url);
        $this->assertStringNotContainsString('super-secret', $log->full_url);
        $this->assertStringContainsString('utm_source=google', $log->full_url);
        $this->assertStringContainsString('access_token=%5BREDACTED%5D', $log->referrer);
        $this->assertStringNotContainsString('abc', $log->referrer);
    }

    public function test_full_private_ip_subnet_resolves_to_internal_location(): void
    {
        $this->track($this->request('/private-ip', ['REMOTE_ADDR' => '172.20.10.5']), new SymfonyResponse('x'));

        $this->assertDatabaseHas('lwa_logs', [
            'ip_address' => '172.20.10.5',
            'country'    => 'Localhost / Internal',
            'city'       => 'Local',
        ]);
    }

    public function test_web_capture_can_be_disabled(): void
    {
        config()->set('laravel-web-analytics.capture.web', false);

        $this->track($this->request('/no-web'), new SymfonyResponse('x'));

        $this->assertSame(0, LwaLogs::count());
    }

    /** @param array<string, string> $server */
    private function request(string $uri, array $server = []): Request
    {
        return Request::create($uri, 'GET', [], [], [], $server + [
            'REMOTE_ADDR'    => '127.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) Chrome/129.0.0.0 Safari/537.36',
        ]);
    }

    private function track(Request $request, SymfonyResponse $response): void
    {
        $middleware = new TrackWebTraffic();
        $middleware->handle($request, fn () => $response);
        $middleware->terminate($request, $response);
    }
}
