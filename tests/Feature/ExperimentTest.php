<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Models\Analytics\Experiment;
use Nafiur\WebAnalytics\Models\Analytics\FeatureFlag;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\ExperimentService;
use Nafiur\WebAnalytics\Tests\TestCase;

class ExperimentTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('laravel-web-analytics.flags.enabled', true);
        $website = Website::create([
            'name'        => 'Experiments QA',
            'domain'      => 'experiments.test',
            'tracking_id' => 'trk_experiments',
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function event(string $name, array $properties): void
    {
        $now = CarbonImmutable::now();
        app(EventStore::class)->insert([
            new UnifiedEvent(
                uuid: (string) Str::uuid(),
                name: $name,
                timestamp: $now,
                distinctId: null,
                anonymousId: 'experiment-qa',
                sessionId: null,
                websiteId: $this->websiteId,
                properties: $properties,
                context: [],
                value: null,
                createdAt: $now,
            ),
        ]);
    }

    private function seedEvents(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->event('feature_flag_evaluated', [
                'flag_key' => 'checkout-test', 'variant' => 'control', 'subject_hash' => hash('sha256', 'u_control_' . $i),
            ]);
            $this->event('feature_flag_evaluated', [
                'flag_key' => 'checkout-test', 'variant' => 'variant-a', 'subject_hash' => hash('sha256', 'u_variant_' . $i),
            ]);
        }
        for ($i = 0; $i < 2; $i++) {
            $this->event('purchase', ['variant' => 'control']);
        }
        for ($i = 0; $i < 5; $i++) {
            $this->event('purchase', ['variant' => 'variant-a']);
        }
        $this->event('purchase', []); // unattributed goal must be ignored
    }

    public function test_experiment_results_read_only_recorded_events(): void
    {
        FeatureFlag::create([
            'website_id' => $this->websiteId, 'key' => 'checkout-test', 'name' => 'Checkout Test',
            'type' => 'multivariate', 'enabled' => true, 'rollout' => 100,
            'variants' => [['key' => 'control', 'weight' => 50], ['key' => 'variant-a', 'weight' => 50]],
        ]);
        $experiment = Experiment::create([
            'website_id' => $this->websiteId, 'name' => 'Checkout Experiment',
            'flag_key' => 'checkout-test', 'goal_event' => 'purchase', 'status' => 'running',
            'control_variant' => 'control', 'started_at' => now(),
        ]);
        $this->seedEvents();

        $result = app(ExperimentService::class)->results($this->websiteId, null, '30d');

        $this->assertCount(1, $result['experiments']);
        $item = $result['experiments'][0];
        $this->assertSame('checkout-test', $item['flag_key']);
        $this->assertCount(2, $item['variants']);

        $control = collect($item['variants'])->firstWhere('variant', 'control');
        $variant = collect($item['variants'])->firstWhere('variant', 'variant-a');
        $this->assertSame(10, $control['exposures']);
        $this->assertSame(2, $control['conversions']);
        $this->assertSame(20.0, $control['rate']);
        $this->assertTrue($control['is_control']);
        $this->assertNull($control['lift_percent']);

        $this->assertSame(10, $variant['exposures']);
        $this->assertSame(5, $variant['conversions']);
        $this->assertSame(50.0, $variant['rate']);
        $this->assertSame(150.0, $variant['lift_percent']);
        $this->assertNotNull($variant['p_value']);
        $this->assertFalse($variant['significant']);
        $this->assertSame(20, $item['exposure_events']);
        $this->assertSame(8, $item['goal_events']);
        $this->assertSame('lwa_events only; exposures are feature_flag_evaluated, conversions are the configured goal event.', $result['definitions']['source']);

        $filtered = app(ExperimentService::class)->results($this->websiteId, null, '30d', 9999);
        $this->assertSame([], $filtered['experiments']);
        $this->assertSame($experiment->id, $experiment->id);
    }

    public function test_experiments_have_web_crud(): void
    {
        FeatureFlag::create([
            'website_id' => $this->websiteId, 'key' => 'crud-flag', 'name' => 'CRUD Flag',
            'type' => 'boolean', 'enabled' => true, 'rollout' => 100,
        ]);

        $this->post(route('lwa_experiments_store'), [
            'name' => 'CRUD Experiment', 'flag_key' => 'crud-flag', 'goal_event' => 'purchase',
            'variant_property' => 'variant', 'control_variant' => 'control', 'status' => 'running',
        ])->assertRedirect(route('lwa_experiments'));

        $experiment = Experiment::where('name', 'CRUD Experiment')->firstOrFail();
        $this->assertSame('purchase', $experiment->goal_event);

        $this->get(route('lwa_experiments'))->assertOk()->assertSee('CRUD Experiment');
        $this->get(route('lwa_experiments_edit', $experiment->id))->assertOk()->assertSee('Edit experiment');

        $this->put(route('lwa_experiments_update', $experiment->id), [
            'name' => 'CRUD Experiment', 'flag_key' => 'crud-flag', 'goal_event' => 'signup',
            'variant_property' => 'variant', 'control_variant' => 'control', 'status' => 'stopped',
        ])->assertRedirect(route('lwa_experiments'));
        $this->assertSame('signup', $experiment->fresh()->goal_event);

        $this->delete(route('lwa_experiments_destroy', $experiment->id))->assertRedirect(route('lwa_experiments'));
        $this->assertDatabaseMissing('lwa_experiments', ['id' => $experiment->id]);
    }

    public function test_experiments_page_is_hidden_when_flags_are_disabled(): void
    {
        config()->set('laravel-web-analytics.flags.enabled', false);
        $this->get(route('lwa_experiments'))->assertNotFound();
    }
}
