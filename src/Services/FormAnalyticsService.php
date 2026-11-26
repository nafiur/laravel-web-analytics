<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Carbon;
use Nafiur\WebAnalytics\Models\Analytics\ReplayMarker;
use Nafiur\WebAnalytics\Models\Analytics\ReplaySession;

/**
 * Aggregates masked form interaction markers into per-form and per-field
 * funnel counts. Field values are never recorded, so nothing here can leak
 * submitted data.
 */
class FormAnalyticsService
{
    private const CAP = 20000;
    private const TYPES = ['field_focus', 'field_change', 'form_submit', 'form_error'];

    /** @return array<string, mixed> */
    public function aggregate(?int $websiteId, ?string $tenantId, ?string $path, string $range): array
    {
        $cutoff = $this->cutoff($range);

        $sessionQuery = ReplaySession::query()->forWebsite($websiteId)->forTenant($tenantId);
        if ($path !== null && $path !== '') {
            $sessionQuery->where('url', $path);
        }
        $sessionIds = $sessionQuery->pluck('id')->all();
        if ($sessionIds === []) {
            return $this->emptyResult($range, $path);
        }

        $query = ReplayMarker::query()
            ->whereIn('replay_session_id', $sessionIds)
            ->whereIn('type', self::TYPES);
        if ($cutoff !== null) {
            $query->where('created_at', '>=', $cutoff);
        }

        $markers = $query->orderByDesc('id')->limit(self::CAP + 1)->get()->reverse()->values();
        $capped = $markers->count() > self::CAP;
        if ($capped) {
            $markers = $markers->slice(-self::CAP)->values();
        }

        $forms = [];
        $starts = [];
        $submits = [];
        $errors = [];
        $invalidSubmits = [];
        $fieldActivity = [];

        foreach ($markers as $marker) {
            $sessionId = (int) $marker->replay_session_id;
            $payload = is_array($marker->payload) ? $marker->payload : [];
            $form = $this->labelForm(is_array($payload['form'] ?? null) ? $payload['form'] : []);
            $forms[$form] = true;

            if ($marker->type === 'field_focus') {
                $starts[$form][$sessionId] = true;
                $field = $this->labelField(is_array($payload['field'] ?? null) ? $payload['field'] : []);
                $fieldActivity[$form][$field]['focus'][$sessionId] = true;
                continue;
            }

            if ($marker->type === 'field_change') {
                $field = $this->labelField(is_array($payload['field'] ?? null) ? $payload['field'] : []);
                $fieldActivity[$form][$field]['change'][$sessionId] = true;
                continue;
            }

            if ($marker->type === 'form_submit') {
                $submits[$form][$sessionId] = true;
                if (($payload['valid'] ?? true) === false) {
                    $invalidSubmits[$form][$sessionId] = true;
                }
                continue;
            }

            if ($marker->type === 'form_error') {
                $errors[$form][$sessionId] = true;
            }
        }

        $rows = [];
        foreach (array_keys($forms) as $form) {
            $formStarts = count($starts[$form] ?? []);
            $formSubmits = count($submits[$form] ?? []);
            $formErrors = count($errors[$form] ?? []);

            $fields = [];
            foreach ($fieldActivity[$form] ?? [] as $field => $activity) {
                $focus = count($activity['focus'] ?? []);
                $change = count($activity['change'] ?? []);
                $fields[] = [
                    'field'          => $field,
                    'focus_sessions' => $focus,
                    'change_sessions' => $change,
                    'change_rate'    => $focus > 0 ? round($change / $focus * 100, 2) : 0.0,
                ];
            }
            usort($fields, fn (array $a, array $b): int => $b['focus_sessions'] <=> $a['focus_sessions']);

            $rows[] = [
                'form'            => $form,
                'starts'          => $formStarts,
                'submits'         => $formSubmits,
                'errors'          => $formErrors,
                'invalid_submits' => count($invalidSubmits[$form] ?? []),
                'conversion_rate' => $formStarts > 0 ? round($formSubmits / $formStarts * 100, 2) : 0.0,
                'error_rate'      => $formStarts > 0 ? round($formErrors / $formStarts * 100, 2) : 0.0,
                'fields'          => array_slice($fields, 0, 25),
            ];
        }
        usort($rows, fn (array $a, array $b): int => $b['starts'] <=> $a['starts']);

        return [
            'range'       => $range,
            'path'        => $path,
            'sample_size' => $markers->count(),
            'capped'      => $capped,
            'forms'       => array_slice($rows, 0, 50),
            'definitions' => $this->definitions(),
        ];
    }

    /** @return array<string, mixed> */
    private function emptyResult(string $range, ?string $path): array
    {
        return [
            'range'       => $range,
            'path'        => $path,
            'sample_size' => 0,
            'capped'      => false,
            'forms'       => [],
            'definitions' => $this->definitions(),
        ];
    }

    /** @return array<string, string> */
    private function definitions(): array
    {
        return [
            'source'      => 'lwa_replay_markers, scoped to the active website and tenant.',
            'sample'      => 'At most 20,000 most recent matching form markers are read; counts are lower bounds when the cap is reached.',
            'start'       => 'A start is a session with at least one field_focus marker for that form.',
            'submit'      => 'A submit is a session with a form_submit marker; no submission is inferred from navigation.',
            'conversion'  => 'Conversion rate is submits / starts. It is 0 when no start was recorded, not a fabricated rate.',
            'errors'      => 'An error is a session with a form_error marker; invalid_submits counts form_submit markers where checkValidity() was false.',
            'field_rate'  => 'Change rate is sessions with a field_change marker divided by sessions with a field_focus marker for that field.',
            'privacy'     => 'Field names and structural attributes are recorded; field values and validation messages never are.',
        ];
    }

    private function cutoff(string $range): ?Carbon
    {
        $days = ['7d' => 7, '30d' => 30, '90d' => 90][$range] ?? null;

        return $days === null ? null : now()->subDays($days);
    }

    /** @param array<string, mixed> $form */
    private function labelForm(array $form): string
    {
        $id = trim((string) ($form['id'] ?? ''));
        $class = trim((string) ($form['cls'] ?? ''));
        $method = trim((string) ($form['method'] ?? ''));

        $label = 'form' . ($id !== '' ? '#' . $id : '');
        if ($class !== '') {
            $label .= '.' . str_replace(' ', '.', $class);
        }
        if ($method !== '') {
            $label .= ' [' . $method . ']';
        }

        return mb_substr($label, 0, 120);
    }

    /** @param array<string, mixed> $field */
    private function labelField(array $field): string
    {
        $tag = (string) ($field['tag'] ?? 'field');
        $type = trim((string) ($field['type'] ?? ''));
        $name = trim((string) ($field['name'] ?? ''));
        $id = trim((string) ($field['id'] ?? ''));

        $label = $tag;
        if ($type !== '') {
            $label .= '[' . $type . ']';
        }
        if ($name !== '') {
            $label .= ' name=' . $name;
        } elseif ($id !== '') {
            $label .= '#' . $id;
        }

        return mb_substr($label, 0, 120);
    }
}
