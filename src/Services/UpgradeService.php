<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\Carbon;
use Nafiur\WebAnalytics\Models\Analytics as AnalyticsModels;
use Nafiur\WebAnalytics\Models\LwaLogs;

/**
 * Resumable, idempotent backfill from the legacy lwa_logs table into the
 * advanced analytics schema. Never deletes legacy rows unless explicitly told.
 */
class UpgradeService
{
    public function __construct(
        private SessionResolver $sessions,
        private AggregationService $aggregation,
    ) {
    }

    /** @return array<string, int|string> */
    public function run(array $options = []): array
    {
        $missing = SchemaGuard::missing();
        if (!empty($missing)) {
            return ['error' => 'Missing advanced analytics tables: ' . implode(', ', $missing)
                . '. Set WEB_ANALYTICS_SCHEMA_ENABLED=true and run php artisan migrate first.'];
        }

        if (!DatabaseReadiness::legacyReady()) {
            return ['error' => 'Legacy table ' . DatabaseReadiness::legacyTable()
                . ' does not exist. Run php artisan migrate first.'];
        }

        $dryRun = (bool) ($options['dry_run'] ?? false);
        $chunk = max(1, (int) ($options['chunk'] ?? 1000));
        $since = !empty($options['since']) ? Carbon::parse($options['since']) : null;
        $pruneLegacy = (bool) ($options['prune_legacy'] ?? false);

        $website = $this->website($options);
        if ($website === null) {
            return ['error' => 'No website available'];
        }

        $checkpoint = AnalyticsModels\UpgradeCheckpoint::firstOrCreate(
            ['name' => 'legacy_logs'],
            ['last_id' => 0]
        );

        $query = LwaLogs::query()->orderBy('id');
        if ($since !== null) {
            $query->where('created_at', '>=', $since);
        }
        if (!$dryRun) {
            $query->where('id', '>', $checkpoint->last_id);
        }

        $counts = ['read' => 0, 'pageviews' => 0];
        $visitorHashes = [];
        $sessionKeys = [];

        $query->chunkById($chunk, function ($logs) use (&$counts, &$visitorHashes, &$sessionKeys, $website, $dryRun, $checkpoint) {
            foreach ($logs as $log) {
                $counts['read']++;
                if ($dryRun) {
                    continue;
                }

                $visitorHash = $this->visitorHash($log);
                $visitorHashes[$visitorHash] = true;

                $createdAt = Carbon::parse($log->created_at ?? now());
                [$visitor, $session] = $this->sessions->recordPageview(
                    (int) $website->id,
                    $visitorHash,
                    $createdAt,
                    (string) ($log->path ?: '/'),
                    $log->referrer,
                    $log->utm_source,
                    $log->utm_medium,
                    $log->utm_campaign,
                );

                $sessionKeys[$session->session_key] = true;

                AnalyticsModels\Pageview::create([
                    'website_id'       => (int) $website->id,
                    'session_id'       => $session->id,
                    'visitor_id'       => $visitor->id,
                    'path'             => (string) ($log->path ?: '/'),
                    'status_code'      => (int) ($log->status_code ?? 200),
                    'response_time_ms' => (float) ($log->response_time_ms ?? 0),
                    'bytes_sent'       => (int) ($log->bytes_sent ?? 0),
                    'created_at'       => $createdAt,
                ]);

                $counts['pageviews']++;
            }

            if (!$dryRun && $logs->isNotEmpty()) {
                $checkpoint->last_id = $logs->last()->id;
                $checkpoint->save();
            }
        });

        if ($dryRun) {
            $pending = LwaLogs::where('id', '>', $checkpoint->last_id)->count();

            return $counts + [
                'website'           => $website->domain,
                'pending_since_id'  => $pending,
                'checkpoint_last_id'=> (int) $checkpoint->last_id,
                'dry_run'           => 1,
            ];
        }

        $websiteId = (int) $website->id;
        $this->aggregation->aggregateRange($websiteId, now()->subDays(2), now(), 'hourly', 48);
        $this->aggregation->aggregateRange($websiteId, now()->subDays(90), now(), 'daily', 90);

        $deleted = 0;
        if ($pruneLegacy && $checkpoint->last_id > 0) {
            $deleted = LwaLogs::where('id', '<=', $checkpoint->last_id)->delete();
        }

        return $counts + [
            'website'    => $website->domain,
            'visitors'   => count($visitorHashes),
            'sessions'   => count($sessionKeys),
            'last_id'    => (int) $checkpoint->last_id,
            'pruned'     => $deleted,
        ];
    }

    private function website(array $options): ?AnalyticsModels\Website
    {
        if (!empty($options['website_id'])) {
            return AnalyticsModels\Website::find((int) $options['website_id']);
        }

        $domain = $options['website_domain'] ?? config('app.url', 'localhost');
        $domain = parse_url($domain, PHP_URL_HOST) ?: $domain;

        return AnalyticsModels\Website::firstOrCreate(
            ['domain' => $domain],
            [
                'name'        => $domain,
                'tracking_id' => 'trk_' . substr(sha1($domain), 0, 12),
            ]
        );
    }

    private function visitorHash(mixed $log): string
    {
        if (!empty($log->session_id)) {
            return 'u_' . substr((string) $log->session_id, 0, 90);
        }

        $salt = config('laravel-web-analytics.privacy.fingerprint_salt') ?: config('app.key', 'lwa');
        $day = Carbon::parse($log->created_at ?? now())->toDateString();

        return 'f_' . substr(hash('sha256', ($log->ip_address ?? '') . '|' . ($log->user_agent ?? '') . '|' . $day . '|' . $salt), 0, 32);
    }
}
