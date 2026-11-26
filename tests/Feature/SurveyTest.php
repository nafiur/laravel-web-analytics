<?php

namespace Nafiur\WebAnalytics\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\Survey;
use Nafiur\WebAnalytics\Models\Analytics\SurveyResponse;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\SurveyService;
use Nafiur\WebAnalytics\Tests\TestCase;

class SurveyTest extends TestCase
{
    use RefreshDatabase;

    private int $websiteId;
    private string $projectKey = 'trk_surveys';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('laravel-web-analytics.surveys.enabled', true);
        Gate::define('viewLwaSurveys', fn ($user = null) => true);

        $website = Website::create([
            'name'        => 'Survey QA',
            'domain'      => 'surveys.test',
            'tracking_id' => $this->projectKey,
        ]);
        $this->websiteId = (int) $website->id;
        config()->set('laravel-web-analytics.analytics.website_id', $this->websiteId);
    }

    private function survey(string $type = 'nps'): Survey
    {
        return Survey::create([
            'website_id' => $this->websiteId,
            'key'        => 'qa_' . $type,
            'name'       => 'QA ' . $type,
            'type'       => $type,
            'question'   => 'How are we doing?',
            'status'     => 'running',
        ]);
    }

    private function postResponse(array $payload)
    {
        return $this->withHeader('X-LWA-Project-Key', $this->projectKey)
            ->postJson(route('lwa_api_surveys_responses'), $payload);
    }

    public function test_nps_responses_are_stored_logged_and_aggregated(): void
    {
        $survey = $this->survey();

        $this->postResponse(['key' => 'qa_nps', 'score' => 10, 'subject_key' => 'u_1', 'path' => 'checkout'])
            ->assertStatus(202)
            ->assertJsonPath('success', true);
        $this->postResponse(['key' => 'qa_nps', 'score' => 3, 'subject_key' => 'u_2', 'path' => 'checkout'])
            ->assertStatus(202);

        $this->assertSame(2, SurveyResponse::count());
        $logged = AnalyticEvent::where('name', 'survey_response')->firstOrFail();
        $this->assertSame('qa_nps', $logged->properties['survey_key']);
        $this->assertArrayNotHasKey('subject_key', $logged->properties);

        $result = app(SurveyService::class)->results($survey, '30d');
        $this->assertSame(2, $result['sample_size']);
        $this->assertSame(1, $result['nps']['promoters']);
        $this->assertSame(1, $result['nps']['detractors']);
        $this->assertSame(0.0, $result['nps']['score']);
        $this->assertSame(1, $result['nps']['buckets'][10]);
    }

    public function test_text_responses_are_sanitised_and_grouped(): void
    {
        $survey = $this->survey('text');

        $this->postResponse(['key' => 'qa_text', 'answer' => 'Great job token=abc123'])->assertStatus(202);
        $this->postResponse(['key' => 'qa_text', 'answer' => 'Great job token=def456'])->assertStatus(202);

        $this->assertStringContainsString('[redacted]', (string) SurveyResponse::firstOrFail()->answer);
        $result = app(SurveyService::class)->results($survey, '30d');
        $this->assertGreaterThanOrEqual(1, count($result['answers']));
    }

    public function test_surveys_have_web_crud_and_results(): void
    {
        $this->post(route('lwa_surveys_store'), [
            'key' => 'crud', 'name' => 'CRUD Survey', 'type' => 'nps',
            'question' => 'Rate us', 'status' => 'running',
        ])->assertRedirect(route('lwa_surveys'));

        $survey = Survey::where('key', 'crud')->firstOrFail();
        $this->get(route('lwa_surveys'))->assertOk()->assertSee('CRUD Survey');
        $this->get(route('lwa_surveys_edit', $survey->id))
            ->assertOk()
            ->assertSee('Results — CRUD Survey');

        $this->put(route('lwa_surveys_update', $survey->id), [
            'key' => 'crud', 'name' => 'CRUD Survey', 'type' => 'nps',
            'question' => 'Rate our store', 'status' => 'stopped',
        ])->assertRedirect(route('lwa_surveys'));
        $this->assertSame('Rate our store', $survey->fresh()->question);

        $this->delete(route('lwa_surveys_destroy', $survey->id))->assertRedirect(route('lwa_surveys'));
        $this->assertDatabaseMissing('lwa_surveys', ['id' => $survey->id]);
    }

    public function test_embed_directive_renders_running_survey_and_honours_target_path(): void
    {
        $survey = $this->survey();

        $html = \Illuminate\Support\Facades\Blade::render("@lwaSurvey('qa_nps')");
        $this->assertStringContainsString('window.LWA_SURVEY_CONFIG', $html);
        $this->assertStringContainsString(route('lwa_survey_bundle'), $html);
        $this->assertStringContainsString('project_key', $html);

        $survey->update(['target_path' => 'pricing']);
        $this->assertSame('', Blade::render("@lwaSurvey('qa_nps')"));
    }

    public function test_survey_pages_are_gated_and_disabled_returns_404(): void
    {
        Gate::define('viewLwaSurveys', fn ($user = null) => false);
        $this->get(route('lwa_surveys'))->assertStatus(403);

        Gate::define('viewLwaSurveys', fn ($user = null) => true);
        config()->set('laravel-web-analytics.surveys.enabled', false);
        $this->get(route('lwa_surveys'))->assertNotFound();
        $this->postResponse(['key' => 'qa_nps', 'score' => 8])->assertNotFound();
    }
}
