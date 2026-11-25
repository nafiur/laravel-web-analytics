<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Carbon;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;
use Nafiur\WebAnalytics\Models\Analytics\Pageview;
use Nafiur\WebAnalytics\Models\Analytics\UserProfile;
use Nafiur\WebAnalytics\Models\Analytics\Visitor;

/**
 * Identity resolution for v3.2: anonymous visitors are aliased to a known
 * distinct id on `identify`, so events recorded before sign-in remain part of
 * the same profile. Profile traits are merged (new values win) and every
 * lookup is explicitly website scoped.
 */
class IdentityService
{
    public function alias(
        int $websiteId,
        string $anonymousId,
        string $distinctId,
        array $traits = [],
    ): UserProfile {
        $distinctId = $this->normalize($distinctId);

        IdentityAlias::withoutGlobalScope(WebsiteScope::class)->updateOrCreate(
            ['website_id' => $websiteId, 'anonymous_id' => $anonymousId],
            ['distinct_id' => $distinctId, 'aliased_at' => Carbon::now()],
        );

        $now = Carbon::now();
        $profile = UserProfile::withoutGlobalScope(WebsiteScope::class)->firstOrCreate(
            ['website_id' => $websiteId, 'distinct_id' => $distinctId],
            ['first_seen_at' => $now, 'last_seen_at' => $now, 'traits' => []],
        );

        $merged = array_merge((array) ($profile->traits ?? []), $traits);
        $profile->forceFill([
            'traits'        => $merged,
            'last_seen_at'  => $now,
            'first_seen_at' => $profile->first_seen_at ?? $now,
        ])->save();

        return $profile;
    }

    public function profileFor(int $websiteId, string $visitorHash): ?UserProfile
    {
        $profiles = UserProfile::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId);

        if (str_starts_with($visitorHash, 'u_')) {
            return (clone $profiles)->where('distinct_id', $visitorHash)->first();
        }

        $alias = IdentityAlias::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->where('anonymous_id', $visitorHash)
            ->first();

        return $alias ? (clone $profiles)->where('distinct_id', $alias->distinct_id)->first() : null;
    }

    /**
     * Combined session/event timeline for a profile, including activity
     * recorded under any of its aliased anonymous ids.
     *
     * @return array<int, array<string, mixed>>
     */
    public function timeline(int $websiteId, string $distinctId, int $limit = 100): array
    {
        $distinctId = $this->normalize($distinctId);

        $hashes = array_merge(
            [$distinctId],
            IdentityAlias::withoutGlobalScope(WebsiteScope::class)
                ->where('website_id', $websiteId)
                ->where('distinct_id', $distinctId)
                ->pluck('anonymous_id')
                ->all(),
        );

        $visitorIds = Visitor::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->whereIn('visitor_hash', $hashes)
            ->pluck('id')
            ->all();

        if (empty($visitorIds)) {
            return [];
        }

        $timeline = [];

        AnalyticEvent::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->whereIn('visitor_id', $visitorIds)
            ->get()
            ->each(function (AnalyticEvent $event) use (&$timeline) {
                $timeline[] = [
                    'type'       => 'event',
                    'name'       => $event->name,
                    'properties' => $event->properties ?? [],
                    'value'      => $event->value,
                    'timestamp'  => $event->created_at?->toIso8601String(),
                ];
            });

        Pageview::withoutGlobalScope(WebsiteScope::class)
            ->where('website_id', $websiteId)
            ->whereIn('visitor_id', $visitorIds)
            ->get()
            ->each(function (Pageview $pageview) use (&$timeline) {
                $timeline[] = [
                    'type'       => 'pageview',
                    'name'       => 'pageview',
                    'properties' => ['path' => $pageview->path],
                    'value'      => null,
                    'timestamp'  => $pageview->created_at?->toIso8601String(),
                ];
            });

        usort($timeline, fn (array $a, array $b): int =>
            strcmp((string) ($b['timestamp'] ?? ''), (string) ($a['timestamp'] ?? '')));

        return array_slice($timeline, 0, $limit);
    }

    private function normalize(string $distinctId): string
    {
        return str_starts_with($distinctId, 'u_') ? $distinctId : 'u_' . $distinctId;
    }
}
