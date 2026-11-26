<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Nafiur\WebAnalytics\Models\Analytics\ReplayChunk;
use Nafiur\WebAnalytics\Models\Analytics\ReplayMarker;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\ReplaySigner;
use Nafiur\WebAnalytics\Tests\TestCase;

class ReplayTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;
    private string $sessionKey = 'qa_replay_session_01';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('replay');
        Gate::define('viewLwaReplay', fn ($user = null) => true);
        config()->set('laravel-web-analytics.replay.enabled', true);
        config()->set('laravel-web-analytics.replay.disk', 'replay');
        config()->set('laravel-web-analytics.replay.consent.replay', true);
        config()->set('laravel-web-analytics.replay.record_guests', false);
        config()->set('laravel-web-analytics.replay.sample_rate', 1.0);

        $website = Website::create([
            'name'        => 'Replay QA',
            'domain'      => 'replay.test',
            'tracking_id' => 'trk_replay',
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function token(?string $userId = '42', array $overrides = []): string
    {
        return app(ReplaySigner::class)->make(array_merge([
            'website_id'      => $this->websiteId,
            'tenant_id'       => null,
            'user_id'         => $userId,
            'session_key'     => $this->sessionKey,
            'path'            => 'checkout',
            'sample_rate'     => 1.0,
            'mask_all_inputs' => true,
            'masked_selectors' => ['[data-lwa-mask]'],
            'excluded_paths'  => [],
        ], $overrides));
    }

    private function user(): AuthUser
    {
        return (new AuthUser())->forceFill([
            'id'       => 42,
            'name'     => 'Replay QA',
            'email'    => 'replay@example.test',
            'password' => 'secret',
        ]);
    }

    private function chunkPayload(string $token, array $overrides = []): array
    {
        return array_merge([
            'token'       => $token,
            'session_key' => $this->sessionKey,
            'sequence'    => 1,
            'encoding'    => 'identity',
            'from_ms'     => 0,
            'to_ms'       => 1250,
            'event_count' => 2,
            'chunk'       => base64_encode('{"events":[]}'),
            'markers'     => [
                ['type' => 'click', 'at_ms' => 250, 'payload' => ['target' => ['tag' => 'button']]],
                ['type' => 'ignored', 'at_ms' => 300, 'payload' => []],
            ],
        ], $overrides);
    }

    public function test_signer_round_trips_and_rejects_tampering(): void
    {
        $signer = app(ReplaySigner::class);
        $token = $this->token();

        $payload = $signer->verify($token);
        $this->assertNotNull($payload);
        $this->assertSame($this->websiteId, (int) $payload['website_id']);
        $this->assertSame($this->sessionKey, $payload['session_key']);
        $this->assertSame('42', $payload['user_id']);

        $parts = explode('.', $token);
        $parts[0][0] = $parts[0][0] === 'A' ? 'B' : 'A';
        $this->assertNull($signer->verify(implode('.', $parts)));
        $this->assertNull($signer->verify('not-a-token'));
    }

    public function test_replay_bundle_is_only_served_when_enabled(): void
    {
        $response = $this->get(route('lwa_replay_bundle'));
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertHeader('Cache-Control', 'max-age=3600, public')
            ->assertSee('window.LWA_REPLAY_DEFAULTS', false)
            ->assertSee('/web-analytics/api/v1/replay/chunks', false);

        config()->set('laravel-web-analytics.replay.enabled', false);
        $this->get(route('lwa_replay_bundle'))->assertNotFound();
    }

    public function test_embed_directive_respects_consent_and_guest_defaults(): void
    {
        $this->assertSame('', Blade::render('@lwaReplay'));

        $this->actingAs($this->user());

        $html = Blade::render('@lwaReplay');
        $this->assertStringContainsString('window.LWA_REPLAY_CONFIG=', $html);
        $this->assertStringContainsString('/web-analytics/replay.js', $html);
        $this->assertStringContainsString('"consent":true', $html);
        $this->assertStringContainsString('"session_key"', $html);

        config()->set('laravel-web-analytics.replay.enabled', false);
        $this->assertSame('', Blade::render('@lwaReplay'));
    }

    public function test_ingest_stores_a_signed_chunk_and_index_rows(): void
    {
        $response = $this->postJson(route('lwa_api_replay_chunks'), $this->chunkPayload($this->token()));

        $response->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('sequence', 1);

        $path = $response->json('disk_path');
        Storage::disk('replay')->assertExists($path);
        $this->assertSame('{"events":[]}', Storage::disk('replay')->get($path));

        $session = ReplaySession::where('session_key', $this->sessionKey)->firstOrFail();
        $this->assertSame($this->websiteId, (int) $session->website_id);
        $this->assertSame('42', $session->user_id);
        $this->assertSame(2, $session->event_count);
        $this->assertSame(1250, $session->duration_ms);
        $this->assertSame('checkout', $session->url);

        $chunk = ReplayChunk::where('replay_session_id', $session->id)->firstOrFail();
        $this->assertSame(1, $chunk->sequence);
        $this->assertSame('identity', $chunk->encoding);

        $this->assertSame(1, ReplayMarker::where('replay_session_id', $session->id)->count());
        $this->assertTrue(ReplayMarker::where('type', 'click')->exists());
        $this->assertFalse(ReplayMarker::where('type', 'ignored')->exists());
    }

    public function test_ingest_is_idempotent_for_the_same_sequence(): void
    {
        $payload = $this->chunkPayload($this->token());

        $this->postJson(route('lwa_api_replay_chunks'), $payload)->assertStatus(202);
        $this->postJson(route('lwa_api_replay_chunks'), $payload)->assertStatus(202);

        $this->assertSame(1, ReplayChunk::count());
        $this->assertSame(2, ReplaySession::firstOrFail()->event_count);
        $this->assertSame(1, ReplayMarker::count());
    }

    public function test_ingest_rejects_guest_unsigned_tampered_and_oversized_payloads(): void
    {
        $this->postJson(route('lwa_api_replay_chunks'), $this->chunkPayload($this->token(null)))
            ->assertStatus(403)
            ->assertJsonPath('message', 'Guest replay is disabled');

        $token = $this->token();
        $parts = explode('.', $token);
        $parts[0] = ($parts[0][0] === 'A' ? 'B' : 'A') . substr($parts[0], 1);
        $tampered = implode('.', $parts);
        $this->postJson(route('lwa_api_replay_chunks'), $this->chunkPayload($tampered))
            ->assertStatus(401);

        $this->postJson(route('lwa_api_replay_chunks'), $this->chunkPayload($token, [
            'session_key' => 'another_session',
        ]))->assertStatus(403);

        config()->set('laravel-web-analytics.replay.max_chunk_bytes', 8);
        $this->postJson(route('lwa_api_replay_chunks'), $this->chunkPayload($token))
            ->assertStatus(422);
    }

    public function test_ingest_accepts_masked_form_markers(): void
    {
        $payload = $this->chunkPayload($this->token(), [
            'markers' => [
                [
                    'type'   => 'field_focus',
                    'at_ms'  => 100,
                    'payload' => ['form' => ['id' => 'checkout'], 'field' => ['name' => 'email']],
                ],
                [
                    'type'   => 'form_submit',
                    'at_ms'  => 400,
                    'payload' => ['form' => ['id' => 'checkout'], 'valid' => true],
                ],
                ['type' => 'console', 'at_ms' => 450, 'payload' => ['level' => 'error', 'message' => 'boom']],
                ['type' => 'failed_request', 'at_ms' => 460, 'payload' => ['path' => '/api/checkout', 'status' => 500]],
                ['type' => 'rage_click', 'at_ms' => 470, 'payload' => ['target' => ['tag' => 'button'], 'clicks_in_window' => 3]],
            ],
        ]);

        $this->postJson(route('lwa_api_replay_chunks'), $payload)->assertStatus(202);

        $this->assertTrue(ReplayMarker::where('type', 'field_focus')->exists());
        $this->assertTrue(ReplayMarker::where('type', 'form_submit')->exists());
        $this->assertTrue(ReplayMarker::where('type', 'console')->exists());
        $this->assertTrue(ReplayMarker::where('type', 'failed_request')->exists());
        $this->assertTrue(ReplayMarker::where('type', 'rage_click')->exists());
    }

    public function test_prune_command_removes_old_replay_rows_and_files(): void
    {
        $response = $this->postJson(route('lwa_api_replay_chunks'), $this->chunkPayload($this->token()));
        $response->assertStatus(202);
        $path = $response->json('disk_path');
        Storage::disk('replay')->assertExists($path);

        ReplaySession::query()->update(['created_at' => now()->subDays(45)]);

        $this->artisan('lwa:replay:prune', ['--days' => 30])->assertExitCode(0);

        $this->assertSame(0, ReplaySession::count());
        $this->assertSame(0, ReplayChunk::count());
        $this->assertSame(0, ReplayMarker::count());
        Storage::disk('replay')->assertMissing($path);
    }

    public function test_replay_page_is_gated_and_disabled_returns_404(): void
    {
        Gate::define('viewLwaReplay', fn () => false);
        $this->actingAs($this->user())
            ->get(route('lwa_replay'))
            ->assertStatus(403);

        Gate::define('viewLwaReplay', fn () => true);
        $this->get(route('lwa_replay'))->assertOk()->assertSee('Session Replay');

        config()->set('laravel-web-analytics.replay.enabled', false);
        $this->get(route('lwa_replay'))->assertNotFound();
    }

    public function test_player_bundle_is_served_only_for_authorized_viewers(): void
    {
        $this->get(route('lwa_replay_player_bundle'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript; charset=utf-8')
            ->assertSee('LWA_REPLAY_PLAYER_READY', false);

        Gate::define('viewLwaReplay', fn ($user = null) => false);
        $this->get(route('lwa_replay_player_bundle'))->assertStatus(403);
    }

    public function test_recorder_bundle_emits_richer_markers(): void
    {
        $this->get(route('lwa_replay_bundle'))
            ->assertOk()
            ->assertSee('console.error', false)
            ->assertSee('failed_request', false)
            ->assertSee('rage_click', false)
            ->assertSee('layout-shift', false)
            ->assertSee('[redacted]', false);
    }

    public function test_manifest_and_chunk_views_are_scoped_and_gated(): void
    {
        $response = $this->postJson(route('lwa_api_replay_chunks'), $this->chunkPayload($this->token()));
        $response->assertStatus(202);
        $session = ReplaySession::firstOrFail();

        $this->get(route('lwa_replay_show', $session->id))
            ->assertOk()
            ->assertSee('Replay ' . $this->sessionKey)
            ->assertSee(route('lwa_replay_manifest', $session->id), false)
            ->assertSee('Inspect DOM')
            ->assertSee('Speed');

        $this->getJson(route('lwa_replay_manifest', $session->id))
            ->assertOk()
            ->assertJsonPath('session.session_key', $this->sessionKey)
            ->assertJsonPath('chunks.0.sequence', 1)
            ->assertJsonPath('markers.0.type', 'click')
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->get(route('lwa_replay_chunk', [$session->id, 1]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('{"events":[]}', false);

        $other = Website::create([
            'name'        => 'Other',
            'domain'      => 'other.test',
            'tracking_id' => 'trk_other',
        ]);
        config()->set('laravel-web-analytics.analytics.website_id', $other->id);

        $this->get(route('lwa_replay_show', $session->id))->assertNotFound();
        $this->getJson(route('lwa_replay_manifest', $session->id))->assertNotFound();
        $this->get(route('lwa_replay_chunk', [$session->id, 1]))->assertNotFound();

        Gate::define('viewLwaReplay', fn ($user = null) => false);
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
        $this->get(route('lwa_replay_show', $session->id))->assertStatus(403);
        $this->getJson(route('lwa_replay_manifest', $session->id))->assertStatus(403);
        $this->get(route('lwa_replay_chunk', [$session->id, 1]))->assertStatus(403);
    }

    public function test_replay_index_can_be_filtered_by_path(): void
    {
        ReplaySession::create([
            'session_key' => 'replay_path_checkout', 'website_id' => $this->websiteId,
            'url' => 'checkout', 'started_at' => now(), 'duration_ms' => 100, 'event_count' => 1, 'bytes' => 0, 'sample_rate' => 1,
        ]);
        ReplaySession::create([
            'session_key' => 'replay_path_pricing', 'website_id' => $this->websiteId,
            'url' => 'pricing', 'started_at' => now(), 'duration_ms' => 100, 'event_count' => 1, 'bytes' => 0, 'sample_rate' => 1,
        ]);

        $this->get(route('lwa_replay', ['path' => 'checkout']))
            ->assertOk()
            ->assertSee('Filtered to path')
            ->assertSee('replay_path_checkout')
            ->assertDontSee('replay_path_pricing');

        $this->get(route('lwa_replay'))->assertOk()->assertSee('replay_path_pricing');
    }

    public function test_breakdown_rows_link_to_filtered_recordings_when_replay_is_enabled(): void
    {
        $html = view('web-analytics::partials.breakdown-list', [
            'key'   => 'top_paths',
            'items' => [['name' => '/checkout', 'count' => 3, 'percentage' => 100]],
        ])->render();

        $this->assertStringContainsString(route('lwa_replay', ['path' => '/checkout']), $html);
        $this->assertStringContainsString('View recordings', $html);
    }
}
