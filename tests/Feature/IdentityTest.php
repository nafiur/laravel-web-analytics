<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nafiur\WebAnalytics\Facades\Analytics;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;
use Nafiur\WebAnalytics\Models\Analytics\UserProfile;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\IdentityService;
use Nafiur\WebAnalytics\Tests\TestCase;

class IdentityTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;

    protected function setUp(): void
    {
        parent::setUp();

        $website = Website::create([
            'name' => 'Identity Test', 'domain' => 'identity.test', 'tracking_id' => 'trk_identity',
        ]);
        $this->websiteId = (int) $website->id;

        config()->set('laravel-web-analytics.analytics.enabled', true);
        config()->set('laravel-web-analytics.analytics.mode', 'sync');
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    public function test_identify_aliases_anonymous_activity_and_merges_traits(): void
    {
        // Activity recorded before sign-in belongs to the anonymous handle.
        Analytics::event('view_item', [], null, ['visitor_hash' => 'f_anon']);

        $this->withHeader('X-LWA-Project-Key', 'trk_identity')->postJson(route('lwa_api_events_batch'), [
            'events' => [
                [
                    'uuid' => 'identify-1',
                    'name' => 'identify',
                    'distinct_id' => 'u_42',
                    'anonymous_id' => 'f_anon',
                    'properties' => ['plan' => 'pro', 'password' => 'must-be-stripped'],
                ],
            ],
        ])->assertStatus(202);

        $profile = UserProfile::where('distinct_id', 'u_42')->firstOrFail();
        $this->assertSame(['plan' => 'pro'], $profile->traits);
        $this->assertSame(1, IdentityAlias::where('anonymous_id', 'f_anon')->where('distinct_id', 'u_42')->count());

        $identity = app(IdentityService::class);
        $this->assertSame($profile->id, $identity->profileFor($this->websiteId, 'f_anon')?->id);
        $this->assertSame($profile->id, $identity->profileFor($this->websiteId, 'u_42')?->id);

        $names = collect($identity->timeline($this->websiteId, 'u_42'))->pluck('name')->all();
        $this->assertContains('view_item', $names);
        $this->assertContains('identify', $names);
    }

    public function test_reidentify_merges_existing_traits(): void
    {
        $identity = app(IdentityService::class);

        $identity->alias($this->websiteId, 'f_one', 'u_7', ['plan' => 'free', 'city' => 'Dhaka']);
        $profile = $identity->alias($this->websiteId, 'f_two', '7', ['plan' => 'pro']);

        $this->assertEqualsCanonicalizing(['plan' => 'pro', 'city' => 'Dhaka'], $profile->traits);
        $this->assertSame(2, IdentityAlias::where('distinct_id', 'u_7')->count());
    }

    public function test_user_api_exposes_profile_and_timeline(): void
    {
        app(IdentityService::class)->alias($this->websiteId, 'f_anon', 'u_42', ['plan' => 'pro']);

        [, $reader] = ApiToken::issue($this->websiteId, 'reader', ['read']);

        $this->withHeader('Authorization', 'Bearer ' . $reader)
            ->getJson(route('lwa_api_users'))
            ->assertOk()
            ->assertJsonPath('data.0.distinct_id', 'u_42');

        $this->withHeader('Authorization', 'Bearer ' . $reader)
            ->getJson(route('lwa_api_user', ['distinctId' => 'u_42']))
            ->assertOk()
            ->assertJsonPath('data.profile.distinct_id', 'u_42')
            ->assertJsonPath('data.aliases.0', 'f_anon');

        $this->withHeader('Authorization', 'Bearer ' . $reader)
            ->getJson(route('lwa_api_user', ['distinctId' => 'u_missing']))
            ->assertStatus(404);
    }
}
