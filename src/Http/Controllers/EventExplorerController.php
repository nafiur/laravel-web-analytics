<?php

namespace Nafiur\WebAnalytics\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent;
use Nafiur\WebAnalytics\Models\Analytics\IdentityAlias;
use Nafiur\WebAnalytics\Models\Analytics\Website;
use Nafiur\WebAnalytics\Services\AnalyticsWindow;
use Nafiur\WebAnalytics\Services\DatabaseReadiness;
use Nafiur\WebAnalytics\Services\WebsiteResolver;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class EventExplorerController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'event'         => 'sometimes|string|max:100',
            'property_key'  => 'sometimes|string|max:100',
            'property_value' => 'sometimes|string|max:255',
            'user'          => 'sometimes|string|max:100',
            'range'         => 'sometimes|string|in:' . implode(',', AnalyticsWindow::RANGES),
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|date',
        ]);

        $websiteId = app(WebsiteResolver::class)->currentId()
            ?? config('laravel-web-analytics.analytics.website_id')
            ?? 0;
        config()->set('laravel-web-analytics.analytics.website_id', $websiteId);

        $ready = DatabaseReadiness::advancedReady();
        $names = $ready
            ? AnalyticEvent::query()->distinct()->orderBy('name')->pluck('name')->all()
            : [];

        [$from, $to, $range] = AnalyticsWindow::resolve($filters);

        $rows = [];
        if ($ready) {
            $user = $filters['user'] ?? null;
            $aliasedHashes = null;

            if ($user !== null && str_starts_with($user, 'u_')) {
                $distinctId = $user;
                $aliasedHashes = array_merge(
                    [$distinctId],
                    IdentityAlias::withoutGlobalScope(WebsiteScope::class)
                        ->where('website_id', $websiteId)
                        ->where('distinct_id', $distinctId)
                        ->pluck('anonymous_id')
                        ->all(),
                );
            }

            $query = EventQuery::fromArray([
                'website_id'     => $websiteId,
                'name'           => $filters['event'] ?? null,
                'from'           => $from,
                'to'             => $to,
                'limit'          => 20000,
                'property_key'   => $filters['property_key'] ?? null,
                'property_value' => $filters['property_value'] ?? null,
                'visitor_hash'   => $aliasedHashes === null ? $user : null,
            ]);

            $rows = app(EventStore::class)->query($query)
                ->filter(function (AnalyticEvent $event) use ($aliasedHashes): bool {
                    return $aliasedHashes === null
                        || in_array($event->visitor?->visitor_hash, $aliasedHashes, true);
                })
                ->map(fn (AnalyticEvent $event): array => [
                    'name'       => $event->name,
                    'visitor'    => $event->visitor?->visitor_hash ?? 'anonymous',
                    'properties' => $event->properties ?? [],
                    'value'      => $event->value,
                    'created_at' => $event->created_at?->toIso8601String(),
                ])
                ->values()
                ->all();
        }

        $stats = [
            'events'  => count($rows),
            'users'   => count(array_unique(array_column($rows, 'visitor'))),
            'value'   => array_sum(array_map(fn (array $row): float => (float) ($row['value'] ?? 0), $rows)),
        ];
        $shownRows = array_slice($rows, 0, 200);

        return view('web-analytics::pages.explorer', [
            'section'       => 'explorer',
            'rows'          => $shownRows,
            'totalRows'     => count($rows),
            'names'         => $names,
            'filters'       => $filters,
            'stats'         => $stats,
            'ready'         => $ready,
            'websites'      => Website::orderBy('domain')->get(),
            'activeWebsite' => Website::find($websiteId),
            'timeRange'     => $range,
            'source'        => 'database',
            'tenantId'      => $request->input('tenant_id'),
        ]);
    }
}
