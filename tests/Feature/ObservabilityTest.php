<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\ErrorEvent;
use Nafiur\WebAnalytics\Models\Analytics\ErrorGroup;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Tests\TestCase;

class ObservabilityTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;
    private string $projectKey = 'trk_observability';

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('viewLwaObservability', fn ($user = null) => true);
        config()->set('laravel-web-analytics.observability.enabled', true);

        $website = Website::create([
            'name'        => 'Observability QA',
            'domain'      => 'observability.test',
            'tracking_id' => $this->projectKey,
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function report(array $overrides = []): array
    {
        return array_merge([
            'type'     => 'RuntimeException',
            'message'  => 'Payment failed api_key=secret123 for user 42',
            'file'     => '/app/Services/Checkout.php',
            'line'     => 88,
            'severity' => 'error',
            'stack'    => "RuntimeException: Payment failed api_key=secret123\n#0 /app/Checkout.php(88)",
            'event_id' => 'err_qa_1',
            'context'  => [
                'url'         => 'https://example.test/checkout?token=abc&utm_source=mail',
                'release'     => 'v3.6.0',
                'environment' => 'production',
                'secret'      => 'must-not-be-stored',
            ],
        ], $overrides);
    }

    private function postError(array $payload, ?string $key = null)
    {
        return $this->withHeader('X-LWA-Project-Key', $key ?? $this->projectKey)
            ->postJson(route('lwa_api_observability_errors'), $payload);
    }

    public function test_error_ingest_groups_sanitizes_and_logs_events(): void
    {
        $this->postError($this->report())->assertStatus(202)->assertJsonPath('success', true);

        $group = ErrorGroup::firstOrFail();
        $this->assertSame($this->websiteId, (int) $group->website_id);
        $this->assertSame('RuntimeException', $group->type);
        $this->assertSame(1, $group->occurrences);
        $this->assertSame('open', $group->status);
        $this->assertStringNotContainsString('secret123', $group->message);

        $event = ErrorEvent::firstOrFail();
        $this->assertSame('err_qa_1', $event->event_uuid);
        $this->assertStringNotContainsString('secret123', $event->stack);
        $this->assertSame('https://example.test/checkout', $event->context['url']);
        $this->assertArrayNotHasKey('secret', $event->context);

        $this->assertSame(1, AnalyticEvent::where('name', 'error')->count());
        $logged = AnalyticEvent::where('name', 'error')->firstOrFail();
        $this->assertSame($group->fingerprint, $logged->properties['fingerprint']);
        $this->assertStringNotContainsString('secret123', json_encode($logged->properties));
    }

    public function test_error_ingest_is_idempotent_and_groups_repeats(): void
    {
        $this->postError($this->report())->assertStatus(202);
        $duplicate = $this->postError($this->report())->assertStatus(202);
        $this->assertTrue($duplicate->json('duplicate'));

        $this->postError($this->report(['event_id' => 'err_qa_2']))->assertStatus(202);

        $this->assertSame(1, ErrorGroup::count());
        $this->assertSame(2, ErrorGroup::firstOrFail()->occurrences);
        $this->assertSame(2, ErrorEvent::count());
        $this->assertSame(2, AnalyticEvent::where('name', 'error')->count());
    }

    public function test_ingest_rejects_missing_or_invalid_project_key(): void
    {
        $this->postJson(route('lwa_api_observability_errors'), $this->report())->assertStatus(401);
        $this->postError($this->report(), 'trk_wrong')->assertStatus(401);
    }

    public function test_observability_pages_are_gated_and_status_can_change(): void
    {
        Gate::define('viewLwaObservability', fn ($user = null) => false);
        $this->get(route('lwa_observability'))->assertStatus(403);

        Gate::define('viewLwaObservability', fn ($user = null) => true);
        $this->postError($this->report())->assertStatus(202);
        $group = ErrorGroup::firstOrFail();

        $this->get(route('lwa_observability'))->assertOk()->assertSee('RuntimeException');
        $this->get(route('lwa_observability_show', $group->id))
            ->assertOk()
            ->assertSee('Payment failed api_key=[redacted]')
            ->assertSee('v3.6.0');

        $this->post(route('lwa_observability_status', $group->id), ['status' => 'resolved'])
            ->assertRedirect(route('lwa_observability'));
        $this->assertSame('resolved', $group->fresh()->status);
        $this->assertNotNull($group->fresh()->resolved_at);

        config()->set('laravel-web-analytics.observability.enabled', false);
        $this->get(route('lwa_observability'))->assertNotFound();
        $this->postError($this->report(['event_id' => 'err_qa_3']))->assertNotFound();
    }

    public function test_observability_is_scoped_to_website_and_tenant(): void
    {
        $this->postError($this->report())->assertStatus(202);
        $group = ErrorGroup::firstOrFail();

        $other = Website::create([
            'name'        => 'Other',
            'domain'      => 'other-observability.test',
            'tracking_id' => 'trk_other_observability',
        ]);
        config()->set('laravel-web-analytics.analytics.website_id', $other->id);

        $this->get(route('lwa_observability'))->assertOk()->assertDontSee('RuntimeException');
        $this->get(route('lwa_observability_show', $group->id))->assertNotFound();
        $this->post(route('lwa_observability_status', $group->id), ['status' => 'ignored'])->assertNotFound();
    }
}
