<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Models\Analytics\FeatureFlag;
use Nafiur\WebAnalytics\Models\Analytics\FlagOverride;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\FeatureFlagService;
use Nafiur\WebAnalytics\Tests\TestCase;

class FeatureFlagTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('laravel-web-analytics.flags.enabled', true);
        $website = Website::create([
            'name'        => 'Flags QA',
            'domain'      => 'flags.test',
            'tracking_id' => 'trk_flags',
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
        app(FeatureFlagService::class)->resetLogged();
    }

    private function flag(array $overrides = []): FeatureFlag
    {
        return FeatureFlag::create(array_merge([
            'website_id' => $this->websiteId,
            'key'        => 'qa-flag',
            'name'       => 'QA Flag',
            'type'       => 'boolean',
            'enabled'    => true,
            'rollout'    => 100,
        ], $overrides));
    }

    public function test_evaluations_are_scoped_deterministic_and_logged(): void
    {
        $service = app(FeatureFlagService::class);

        $missing = $service->evaluate('missing', 'u_1');
        $this->assertFalse($missing['enabled']);
        $this->assertSame('missing', $missing['reason']);

        $this->flag();

        $enabled = $service->evaluate('qa-flag', 'u_1');
        $this->assertTrue($enabled['enabled']);
        $this->assertSame('on', $enabled['variant']);
        $this->assertSame('enabled', $enabled['reason']);
        $this->assertSame(1, AnalyticEvent::where('name', 'feature_flag_evaluated')->count());
        $event = AnalyticEvent::where('name', 'feature_flag_evaluated')->firstOrFail();
        $this->assertSame('qa-flag', $event->properties['flag_key']);
        $this->assertArrayNotHasKey('subject_key', $event->properties);
        $this->assertSame(hash('sha256', 'u_1'), $event->properties['subject_hash']);

        $again = $service->evaluate('qa-flag', 'u_1');
        $this->assertSame($enabled, $again);
        $this->assertSame(1, AnalyticEvent::where('name', 'feature_flag_evaluated')->count());

        $otherWebsite = Website::create(['name' => 'Other', 'domain' => 'other.test', 'tracking_id' => 'trk_other']);
        $foreign = $service->evaluate('qa-flag', 'u_1', [], (int) $otherWebsite->id);
        $this->assertFalse($foreign['enabled']);
        $this->assertSame('missing', $foreign['reason']);
    }

    public function test_disabled_rollout_targeting_override_and_multivariate_variants(): void
    {
        $service = app(FeatureFlagService::class);

        $this->flag(['enabled' => false]);
        $this->assertSame('disabled', $service->evaluate('qa-flag', 'u_2')['reason']);

        FeatureFlag::query()->update(['enabled' => true, 'rollout' => 0]);
        $this->assertSame('rollout', $service->evaluate('qa-flag', 'u_2')['reason']);

        FeatureFlag::query()->update([
            'rollout'   => 0,
            'targeting' => [['attribute' => 'country', 'operator' => 'equals', 'value' => 'BD']],
        ]);
        $this->assertTrue($service->evaluate('qa-flag', 'u_2', ['country' => 'BD'])['enabled']);
        $this->assertSame('targeted', $service->evaluate('qa-flag', 'u_2', ['country' => 'BD'])['reason']);
        $this->assertSame('rollout', $service->evaluate('qa-flag', 'u_2', ['country' => 'US'])['reason']);

        $flag = FeatureFlag::firstOrFail();
        FlagOverride::create([
            'flag_id' => $flag->id, 'website_id' => $this->websiteId,
            'subject_key' => 'u_2', 'variant' => 'off',
        ]);
        $this->assertSame('override_off', $service->evaluate('qa-flag', 'u_2', ['country' => 'BD'])['reason']);

        $flag->update([
            'type'     => 'multivariate',
            'variants' => [['key' => 'control', 'weight' => 100]],
        ]);
        FlagOverride::query()->delete();
        $variant = $service->evaluate('qa-flag', 'u_3', ['country' => 'BD']);
        $this->assertTrue($variant['enabled']);
        $this->assertSame('control', $variant['variant']);
    }

    public function test_feature_flags_have_web_crud(): void
    {
        $this->post(route('lwa_flags_store'), [
            'key' => 'new-flag', 'name' => 'New Flag', 'type' => 'boolean',
            'enabled' => 1, 'rollout' => 50,
        ])->assertRedirect(route('lwa_flags'));

        $flag = FeatureFlag::where('key', 'new-flag')->firstOrFail();
        $this->assertSame(50, $flag->rollout);

        $this->get(route('lwa_flags'))->assertOk()->assertSee('new-flag');
        $this->get(route('lwa_flags_edit', $flag->id))->assertOk()->assertSee('Edit flag');

        $this->put(route('lwa_flags_update', $flag->id), [
            'key' => 'new-flag', 'type' => 'boolean', 'enabled' => 1, 'rollout' => 75,
        ])->assertRedirect(route('lwa_flags'));
        $this->assertSame(75, $flag->fresh()->rollout);

        $this->delete(route('lwa_flags_destroy', $flag->id))->assertRedirect(route('lwa_flags'));
        $this->assertDatabaseMissing('lwa_feature_flags', ['id' => $flag->id]);
    }

    public function test_api_evaluation_requires_a_read_token(): void
    {
        $this->flag();
        $url = route('lwa_api_flags_evaluate', ['key' => 'qa-flag', 'subject' => 'u_9']);

        $this->getJson($url)->assertStatus(401);

        [, $plain] = ApiToken::issue($this->websiteId, 'flag-reader', ['read']);
        $this->withHeader('Authorization', 'Bearer ' . $plain)
            ->getJson($url)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('flag.key', 'qa-flag')
            ->assertJsonPath('flag.enabled', true);
    }

    public function test_blade_directives_render_flag_markup_and_variant(): void
    {
        $this->flag(['type' => 'multivariate', 'variants' => [['key' => 'variant-a', 'weight' => 100]]]);

        $html = Blade::render("@feature('qa-flag')<b>ON</b>@endfeature @featureVariant('qa-flag')");

        $this->assertStringContainsString('<b>ON</b>', $html);
        $this->assertStringContainsString('variant-a', $html);
    }

    public function test_flags_page_is_hidden_when_disabled(): void
    {
        config()->set('laravel-web-analytics.flags.enabled', false);
        $this->get(route('lwa_flags'))->assertNotFound();
    }
}
