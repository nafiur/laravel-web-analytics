<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Models\Analytics\Survey;
use Nafiur\WebAnalytics\Models\Analytics\SurveyResponse;

class SurveyService
{
    private const CAP = 20000;

    public function __construct(private EventStore $events)
    {
    }

    /** @return array<int, Survey> */
    public function all(?int $websiteId, ?string $tenantId): array
    {
        return Survey::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId)
            ->withCount('responses')
            ->orderBy('name')
            ->get()
            ->all();
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function save(array $data, ?Survey $survey = null): array
    {
        $key = trim((string) ($data['key'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));
        $question = trim((string) ($data['question'] ?? ''));
        $type = (string) ($data['type'] ?? 'nps');

        if ($key === '' || strlen($key) > 100 || $name === '' || $question === '') {
            return ['ok' => false, 'message' => 'Key, name and question are required.'];
        }
        if (! in_array($type, ['nps', 'text'], true)) {
            return ['ok' => false, 'message' => 'Survey type must be nps or text.'];
        }

        $duplicate = Survey::query()
            ->forWebsite($data['website_id'] ?? null)
            ->forTenant($data['tenant_id'] ?? null)
            ->where('key', $key)
            ->when($survey !== null, fn ($query) => $query->where('id', '!=', $survey->id))
            ->exists();
        if ($duplicate) {
            return ['ok' => false, 'message' => 'This survey key already exists in the selected scope.'];
        }

        $survey ??= new Survey();
        $survey->fill([
            'website_id'  => $data['website_id'] ?? null,
            'tenant_id'   => $data['tenant_id'] ?? null,
            'key'         => $key,
            'name'        => mb_substr($name, 0, 150),
            'type'        => $type,
            'question'    => mb_substr($question, 0, 1000),
            'target_path' => $data['target_path'] ?? null,
            'status'      => in_array($data['status'] ?? '', ['draft', 'running', 'stopped'], true) ? $data['status'] : 'draft',
            'created_by'  => $data['created_by'] ?? null,
        ]);
        $survey->save();

        return ['ok' => true, 'survey' => $survey];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function storeResponse(Survey $survey, array $payload, int $websiteId, ?string $tenantId): array
    {
        if ($survey->status !== 'running') {
            return ['ok' => false, 'message' => 'Survey is not running.', 'status' => 403];
        }

        $subject = trim((string) ($payload['subject_key'] ?? ''));
        $subjectHash = $subject !== '' ? hash('sha256', $subject) : null;
        $path = mb_substr((string) ($payload['path'] ?? ''), 0, 500);
        $score = null;
        $answer = null;

        if ($survey->type === 'nps') {
            if (! isset($payload['score']) || ! is_numeric($payload['score'])) {
                return ['ok' => false, 'message' => 'A numeric score is required.', 'status' => 422];
            }
            $score = max(0, min(10, (int) $payload['score']));
        } else {
            $raw = trim((string) ($payload['answer'] ?? ''));
            if ($raw === '') {
                return ['ok' => false, 'message' => 'An answer is required.', 'status' => 422];
            }
            $max = (int) config('laravel-web-analytics.surveys.max_answer_chars', 2000);
            $answer = mb_substr($this->sanitize($raw), 0, $max);
        }

        $response = SurveyResponse::create([
            'survey_id'    => $survey->id,
            'website_id'   => $websiteId,
            'tenant_id'    => $tenantId,
            'subject_hash' => $subjectHash,
            'path'         => $path !== '' ? $path : null,
            'score'        => $score,
            'answer'       => $answer,
        ]);

        $now = CarbonImmutable::now();
        $this->events->insert([
            new UnifiedEvent(
                uuid: 'svr_' . $response->id,
                name: 'survey_response',
                timestamp: $now,
                distinctId: null,
                anonymousId: 'sv_' . substr($subjectHash ?? md5((string) $response->id), 0, 16),
                sessionId: null,
                websiteId: $websiteId,
                properties: [
                    'survey_key'  => $survey->key,
                    'survey_type' => $survey->type,
                    'score'       => $score,
                    'subject_hash' => $subjectHash,
                ],
                context: [],
                value: null,
                createdAt: $now,
            ),
        ]);

        return ['ok' => true, 'response_id' => $response->id];
    }

    /** @return array<string, mixed> */
    public function results(Survey $survey, string $range): array
    {
        $days = ['7d' => 7, '30d' => 30, '90d' => 90][$range] ?? 3650;
        $query = $survey->responses()->where('created_at', '>=', CarbonImmutable::now()->subDays($days));
        $responses = $query->orderByDesc('id')->limit(self::CAP + 1)->get();
        $capped = $responses->count() > self::CAP;
        if ($capped) {
            $responses = $responses->slice(0, self::CAP);
        }

        $result = [
            'range'       => $range,
            'type'        => $survey->type,
            'sample_size' => $responses->count(),
            'capped'      => $capped,
            'definitions' => [
                'source'    => 'lwa_survey_responses, bounded to the 20,000 most recent responses in the window.',
                'nps'       => 'Promoters 9–10, passives 7–8, detractors 0–6. NPS = promoters% - detractors%.',
                'privacy'   => 'Subject ids are hashed; free-text answers are truncated and sanitised before storage.',
            ],
        ];

        if ($survey->type === 'nps') {
            $buckets = array_fill(0, 11, 0);
            $sum = 0;
            $count = 0;
            foreach ($responses as $response) {
                if ($response->score === null) {
                    continue;
                }
                $buckets[$response->score]++;
                $sum += $response->score;
                $count++;
            }
            $promoters = array_sum(array_slice($buckets, 9));
            $passives = array_sum(array_slice($buckets, 7, 2));
            $detractors = array_sum(array_slice($buckets, 0, 7));
            $result['nps'] = [
                'responses'  => $count,
                'average'    => $count > 0 ? round($sum / $count, 2) : 0.0,
                'promoters'  => $promoters,
                'passives'   => $passives,
                'detractors' => $detractors,
                'score'      => $count > 0 ? round(($promoters / $count * 100) - ($detractors / $count * 100)) : 0,
                'buckets'    => $buckets,
            ];
        } else {
            $counts = [];
            foreach ($responses as $response) {
                $answer = mb_substr(trim((string) ($response->answer ?? '')), 0, 120);
                if ($answer !== '') {
                    $counts[$answer] = ($counts[$answer] ?? 0) + 1;
                }
            }
            arsort($counts);
            $top = [];
            foreach ($counts as $answer => $count) {
                $top[] = ['answer' => (string) $answer, 'count' => (int) $count];
            }
            $result['answers'] = array_slice($top, 0, 25);
        }

        return $result;
    }

    private function sanitize(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value) ?? '';
        $value = preg_replace_callback('/(?<key>[A-Za-z0-9_-]+)=([^\s&,]+)/i', function (array $match): string {
            $key = strtolower($match['key']);
            if (in_array($key, ['token', 'password', 'secret', 'api_key', 'apikey', 'authorization', 'card', 'cvv'], true)) {
                return $match['key'] . '=[redacted]';
            }

            return $match[0];
        }, $value) ?? '';

        return $value;
    }
}
