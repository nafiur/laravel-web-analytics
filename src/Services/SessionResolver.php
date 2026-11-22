<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Models\Analytics as AnalyticsModels;

/**
 * Resolves and updates the persistent v3 visitor/session records for a hit.
 * Uses explicit website_id filters because queue workers may not have the
 * dashboard's active-website context.
 */
class SessionResolver
{
    /** @return array{0: mixed, 1: mixed} visitor, session */
    public function resolve(int $websiteId, string $visitorHash, Carbon $occurredAt, ?string $path = null): array
    {
        $visitor = $this->visitor($websiteId, $visitorHash, $occurredAt);

        $session = AnalyticsModels\AnalyticsSession::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->where('visitor_id', $visitor->id)
            ->latest('started_at')
            ->first();

        if ($session !== null) {
            $lastActivity = $session->ended_at ?? $session->started_at;
            if (!MetricDefinitions::isSameSession($lastActivity, $occurredAt)) {
                $session = null;
            }
        }

        if ($session === null) {
            $session = AnalyticsModels\AnalyticsSession::create([
                'website_id'      => $websiteId,
                'visitor_id'      => $visitor->id,
                'session_key'     => hash('sha256', $websiteId . '|' . $visitorHash . '|' . $occurredAt->timestamp . '|' . Str::random(12)),
                'started_at'      => $occurredAt,
                'ended_at'        => $occurredAt,
                'landing_path'    => $path,
                'exit_path'       => $path,
                'pageviews_count' => 0,
                'duration_seconds'=> 0,
                'is_bounce'       => true,
            ]);
        }

        return [$visitor, $session];
    }

    /** @return array{0: mixed, 1: mixed} visitor, session */
    public function recordPageview(
        int $websiteId,
        string $visitorHash,
        Carbon $occurredAt,
        string $path,
        ?string $referrer = null,
        ?string $utmSource = null,
        ?string $utmMedium = null,
        ?string $utmCampaign = null,
    ): array {
        [$visitor, $session] = $this->resolve($websiteId, $visitorHash, $occurredAt, $path);

        $session->pageviews_count = (int) $session->pageviews_count + 1;
        $session->ended_at = $occurredAt;
        $session->exit_path = $path;
        $session->duration_seconds = abs(Carbon::parse($session->started_at)->diffInSeconds($occurredAt));
        $session->referrer = $referrer ?? $session->referrer;
        $session->utm_source = $utmSource ?? $session->utm_source;
        $session->utm_medium = $utmMedium ?? $session->utm_medium;
        $session->utm_campaign = $utmCampaign ?? $session->utm_campaign;
        $session->is_bounce = MetricDefinitions::isBounce(
            (int) $session->pageviews_count,
            (int) $session->duration_seconds
        );
        $session->save();

        if ($visitor->last_seen_at === null || $occurredAt->gt($visitor->last_seen_at)) {
            $visitor->last_seen_at = $occurredAt;
            $visitor->save();
        }

        return [$visitor, $session];
    }

    private function visitor(int $websiteId, string $visitorHash, Carbon $occurredAt): mixed
    {
        $visitor = AnalyticsModels\Visitor::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->where('visitor_hash', $visitorHash)
            ->first();

        if ($visitor === null) {
            $visitor = AnalyticsModels\Visitor::create([
                'website_id'   => $websiteId,
                'visitor_hash' => $visitorHash,
                'first_seen_at'=> $occurredAt,
                'last_seen_at' => $occurredAt,
            ]);
        }

        return $visitor;
    }
}
