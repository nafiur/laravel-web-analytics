                    <div class="space-y-4">
                    @if(($analytics['summary']['total_requests'] ?? 0) === 0)
                        <div class="lwa-card p-10 text-center">
                            <div class="mx-auto lwa-icon-tile mb-3" style="width:40px;height:40px">
                                <i data-lucide="chart-no-axes-combined" class="text-lg"></i>
                            </div>
                            <div class="lwa-card-title">No analytics data yet</div>
                            <p class="lwa-subtitle mt-1 max-w-md mx-auto">
                                Register the <code class="font-mono">TrackWebTraffic</code> middleware in your web group, then reload this page. Requests will appear here once traffic is captured.
                            </p>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h1 class="lwa-page-title">Overview</h1>
                            <p class="lwa-subtitle mt-1">Traffic, engagement and audience for the selected period — {{ $timeRange ?? '24h' }}</p>
                        </div>
                    </div>

                    <!-- KPI cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-7 gap-3">
                        @php
                            $kpis = [
                                ['Total Visitors', 'total_visitors_fmt', 'total_visitors', 'Distinct IP addresses in the selected range', 'users-round'],
                                ['Unique Visitors', 'unique_visitors_fmt', 'unique_visitors', 'Distinct visitor sessions (session ID or IP fallback)', 'user-check'],
                                ['Pageviews', 'pageviews_fmt', 'pageviews', 'GET requests to non-API paths', 'eye'],
                                ['Sessions', 'sessions_fmt', 'sessions', 'Distinct visitor session identifiers', 'layers'],
                                ['Bounce Rate', 'bounce_rate', 'bounce_rate', 'Sessions with only one recorded request', 'corner-down-right'],
                                ['Avg Engagement', 'avg_engagement_fmt', 'avg_engagement_time', 'Average time between first and last request per session', 'timer'],
                                ['Live Now', 'live_visitors_fmt', 'live_visitors', 'Active visitors in the configured live window', 'radio'],
                            ];
                        @endphp
                        @foreach($kpis as [$label, $key, $rawKey, $definition, $icon])
                            <div class="lwa-card lwa-kpi" title="{{ $definition }}">
                                <div class="lwa-kpi-top">
                                    <span class="lwa-kpi-label">{{ $label }}</span>
                                    <span class="lwa-icon-tile">
                                        <i data-lucide="{{ $icon }}" class="text-[13px]"></i>
                                    </span>
                                </div>
                                <div class="lwa-kpi-value">
                                    {{ $analytics['summary'][$key] ?? 0 }}@if(in_array($key, ['bounce_rate'], true))%@endif
                                </div>
                                @if(!empty($comparison[$rawKey]) && $comparison[$rawKey]['available'])
                                    @php
                                        $delta = $comparison[$rawKey];
                                    @endphp
                                    <div class="mt-1 text-[10px]" title="{{ $delta['definition'] }}" style="color: {{ $delta['direction'] === 'up' ? 'var(--lwa-danger)' : ($delta['direction'] === 'down' ? 'var(--lwa-success)' : 'var(--lwa-muted)') }}">
                                        @if($delta['is_new'])
                                            new vs prior
                                        @else
                                            {{ $delta['direction'] === 'up' ? '↑' : ($delta['direction'] === 'down' ? '↓' : '→') }}
                                            {{ number_format(abs((float) $delta['change']), 2) }}{{ $delta['unit'] === 'points' ? ' pp' : '%' }} vs prior
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($errorGroups !== null)
                        <div class="lwa-card p-4">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <div class="lwa-card-title">Error tracking</div>
                                @can(config('laravel-web-analytics.observability.gate', 'viewLwaObservability'))
                                    <a href="{{ route('lwa_observability') }}" class="lwa-btn-ghost">Open observability</a>
                                @endcan
                            </div>
                            @if(empty($errorGroups))
                                <div class="lwa-subtitle">No open exception groups recorded.</div>
                            @else
                                <div class="space-y-1">
                                    @foreach(array_slice($errorGroups, 0, 5) as $group)
                                        <div class="flex items-center justify-between gap-3 text-xs border-b lwa-border py-1">
                                            <span class="truncate font-mono">{{ $group['type'] }}</span>
                                            <span class="truncate" style="color: var(--lwa-muted)">{{ $group['message'] }}</span>
                                            <span>{{ number_format($group['occurrences']) }}×</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Reports pinned from card menus (stored in localStorage) -->
                    <div id="lwaPinnedReports" class="space-y-3 hidden">
                        <div class="lwa-section-label">Pinned reports</div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            @php
                                $pinnedReports = [
                                    'top-os'          => ['breakdown' => 'top_os', 'title' => 'Top operating systems', 'icon' => 'os'],
                                    'top-browsers'    => ['breakdown' => 'top_browsers', 'title' => 'Top browsers', 'icon' => 'browser'],
                                    'top-user-agents' => ['breakdown' => 'top_user_agents', 'title' => 'Top user agents', 'icon' => null],
                                ];
                            @endphp
                            @foreach($pinnedReports as $pinId => $pinReport)
                                <div data-lwa-pin-slot="{{ $pinId }}" class="hidden">
                                    @include('web-analytics::partials.breakdown-card', [
                                        'cardId'     => $pinId,
                                        'cardTitle'  => $pinReport['title'],
                                        'cardItems'  => $analytics['breakdowns'][$pinReport['breakdown']] ?? [],
                                        'cardTrend'  => $analytics['breakdown_trends'][$pinReport['breakdown']] ?? null,
                                        'iconKind'   => $pinReport['icon'],
                                        'exportData' => [$pinReport['breakdown'] => $analytics['breakdowns'][$pinReport['breakdown']] ?? []],
                                    ])
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Visitor geography (uses the same offline GeoIP data as Real-time) -->
                    @include('web-analytics::partials.visitors-map', [
                        'mapId' => 'lwaOverviewMap',
                        'title' => 'Visitors Map',
                        'subtitle' => 'Approximate visitor locations — country centroid when precise GeoIP is unavailable',
                    ])

                    <!-- Traffic overview + top pages -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="lwa-card p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="lwa-card-title">Traffic Overview</div>
                                    <p class="lwa-subtitle mt-0.5">Requests over the selected period — {{ $timeRange ?? '24h' }}</p>
                                </div>
                                @include('web-analytics::partials.group-by-select', ['chartId' => 'chartOverviewTraffic'])
                            </div>
                            <div class="relative mt-3">
                                <div id="chartOverviewTraffic" class="h-64 w-full"></div>
                                <div id="chartOverviewTrafficSkel" class="lwa-skeleton absolute inset-0 h-64 w-full"></div>
                            </div>
                        </div>
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-2">Top Pages</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths'] ?? [], 'key' => 'top_paths'])
                        </div>
                    </div>

                    <!-- Supporting reports -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-2">Traffic Sources</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_referrers'] ?? [], 'key' => 'top_referrers'])
                        </div>
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-2">Countries</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? [], 'key' => 'top_countries'])
                        </div>
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-2">Device Breakdown</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_devices'] ?? [], 'key' => 'top_devices'])
                        </div>
                    </div>
                    </div>
