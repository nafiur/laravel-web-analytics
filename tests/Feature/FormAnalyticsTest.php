<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Nafiur\WebAnalytics\Models\Analytics\ReplayMarker;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\FormAnalyticsService;
use Nafiur\WebAnalytics\Tests\TestCase;

class FormAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('viewLwaReplay', fn ($user = null) => true);
        config()->set('laravel-web-analytics.replay.enabled', true);

        $website = Website::create([
            'name'        => 'Forms QA',
            'domain'      => 'forms.test',
            'tracking_id' => 'trk_forms',
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function newSession(string $key): ReplaySession
    {
        return ReplaySession::create([
            'session_key' => $key,
            'website_id'  => $this->websiteId,
            'url'         => 'checkout',
            'started_at'  => now()->subDay(),
            'duration_ms' => 400,
            'event_count' => 4,
            'bytes'       => 0,
            'sample_rate' => 1,
        ]);
    }

    private function marker(ReplaySession $session, string $type, int $at, array $extra = []): void
    {
        ReplayMarker::create(array_merge([
            'replay_session_id' => $session->id,
            'website_id'        => $this->websiteId,
            'type'              => $type,
            'at_ms'             => $at,
            'payload'           => [
                'form'  => ['id' => 'checkout', 'cls' => '', 'method' => 'post'],
                'field' => ['tag' => 'input', 'type' => 'email', 'name' => 'email', 'id' => '', 'cls' => ''],
            ],
        ], $extra));
    }

    private function seedMarkers(): void
    {
        $first = $this->newSession('forms_session_a');
        $this->marker($first, 'field_focus', 100);
        $this->marker($first, 'field_change', 200);
        $this->marker($first, 'form_error', 250);
        $this->marker($first, 'form_submit', 400, ['payload' => [
            'form' => ['id' => 'checkout', 'cls' => '', 'method' => 'post'],
            'valid' => true,
            'invalid_fields' => 0,
        ]]);

        $second = $this->newSession('forms_session_b');
        $this->marker($second, 'field_focus', 100);
        $this->marker($second, 'form_error', 200);
        $this->marker($second, 'form_submit', 300, ['payload' => [
            'form' => ['id' => 'checkout', 'cls' => '', 'method' => 'post'],
            'valid' => false,
            'invalid_fields' => 1,
        ]]);

        $third = ReplaySession::create([
            'session_key' => 'forms_session_c',
            'website_id'  => $this->websiteId,
            'url'         => 'pricing',
            'started_at'  => now()->subDay(),
            'duration_ms' => 200,
            'event_count' => 3,
            'bytes'       => 0,
            'sample_rate' => 1,
        ]);
        ReplayMarker::create([
            'replay_session_id' => $third->id,
            'website_id'        => $this->websiteId,
            'type'              => 'field_focus',
            'at_ms'             => 100,
            'payload'           => ['form' => ['id' => 'newsletter'], 'field' => ['tag' => 'input', 'type' => 'email', 'name' => 'email']],
        ]);
        ReplayMarker::create([
            'replay_session_id' => $third->id,
            'website_id'        => $this->websiteId,
            'type'              => 'field_change',
            'at_ms'             => 150,
            'payload'           => ['form' => ['id' => 'newsletter'], 'field' => ['tag' => 'input', 'type' => 'email', 'name' => 'email']],
        ]);
        ReplayMarker::create([
            'replay_session_id' => $third->id,
            'website_id'        => $this->websiteId,
            'type'              => 'form_submit',
            'at_ms'             => 200,
            'payload'           => ['form' => ['id' => 'newsletter'], 'valid' => true],
        ]);
    }

    public function test_form_service_aggregates_distinct_sessions_and_fields(): void
    {
        $this->seedMarkers();

        $result = app(FormAnalyticsService::class)->aggregate($this->websiteId, null, null, '30d');

        $this->assertSame(10, $result['sample_size']);
        $this->assertCount(2, $result['forms']);

        $checkout = $result['forms'][0];
        $this->assertSame('form#checkout [post]', $checkout['form']);
        $this->assertSame(2, $checkout['starts']);
        $this->assertSame(2, $checkout['submits']);
        $this->assertSame(2, $checkout['errors']);
        $this->assertSame(1, $checkout['invalid_submits']);
        $this->assertSame(100.0, $checkout['conversion_rate']);
        $this->assertSame('input[email] name=email', $checkout['fields'][0]['field']);
        $this->assertSame(2, $checkout['fields'][0]['focus_sessions']);
        $this->assertSame(1, $checkout['fields'][0]['change_sessions']);
        $this->assertSame(50.0, $checkout['fields'][0]['change_rate']);
        $this->assertStringContainsString('field values and validation messages never are', $result['definitions']['privacy']);
    }

    public function test_form_page_filters_and_scopes_results(): void
    {
        $this->seedMarkers();

        $this->get(route('lwa_forms'))
            ->assertOk()
            ->assertSee('Form Analytics')
            ->assertSee('form#checkout')
            ->assertSee('Definitions')
            ->assertSee('No field values');

        $checkout = app(FormAnalyticsService::class)->aggregate($this->websiteId, null, 'checkout', '7d');
        $this->assertCount(1, $checkout['forms']);
        $this->assertSame(2, $checkout['forms'][0]['starts']);

        $other = Website::create([
            'name'        => 'Other',
            'domain'      => 'other-forms.test',
            'tracking_id' => 'trk_other_forms',
        ]);
        $foreign = app(FormAnalyticsService::class)->aggregate((int) $other->id, null, null, '30d');
        $this->assertSame(0, $foreign['sample_size']);
        $this->assertSame([], $foreign['forms']);
    }

    public function test_forms_are_gated_and_disabled_returns_404(): void
    {
        Gate::define('viewLwaReplay', fn ($user = null) => false);
        $this->get(route('lwa_forms'))->assertStatus(403);

        Gate::define('viewLwaReplay', fn ($user = null) => true);
        config()->set('laravel-web-analytics.replay.enabled', false);
        $this->get(route('lwa_forms'))->assertNotFound();
    }
}
