                    @if(($analytics['summary']['total_requests'] ?? 0) === 0)
                        <div class="lwa-surface border lwa-border rounded-lg p-8 text-center">
                            <div class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                                <i data-lucide="chart-no-axes-combined" class="text-lg text-slate-400"></i>
                            </div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white">No analytics data yet</div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-md mx-auto">
                                Register the <code class="font-mono">TrackWebTraffic</code> middleware in your web group, then reload this page. Requests will appear here once traffic is captured.
                            </p>
                        </div>
                    @endif

                    <!-- KPI cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-3">
                        @php
                            $kpis = [
                                ['Total Visitors', 'total_visitors_fmt', 'Distinct IP addresses in the selected range'],
                                ['Unique Visitors', 'unique_visitors_fmt', 'Distinct visitor sessions (session ID or IP fallback)'],
                                ['Pageviews', 'pageviews_fmt', 'GET requests to non-API paths'],
                                ['Sessions', 'sessions_fmt', 'Distinct visitor session identifiers'],
                                ['Bounce Rate', 'bounce_rate', 'Sessions with only one recorded request'],
                                ['Avg Engagement', 'avg_engagement_fmt', 'Average time between first and last request per session'],
                                ['Live Now', 'live_visitors_fmt', 'Active visitors in the configured live window'],
                            ];
                        @endphp
                        @foreach($kpis as [$label, $key, $definition])
                            <div class="lwa-surface border lwa-border rounded-lg p-3.5 shadow-sm lwa-kpi" title="{{ $definition }}">
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">{{ $label }}</div>
                                <div class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                                    {{ $analytics['summary'][$key] ?? 0 }}@if(in_array($key, ['bounce_rate'], true))%@endif
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 truncate">{{ $definition }}</div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Reports pinned from card menus (stored in localStorage) -->
                    <div id="lwaPinnedReports" class="space-y-4 hidden">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Pinned reports</div>
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
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Traffic Overview</div>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Requests over the selected period — {{ $timeRange ?? '24h' }}</p>
                                </div>
                                @include('web-analytics::partials.group-by-select', ['chartId' => 'chartOverviewTraffic'])
                            </div>
                            <div class="relative mt-3">
                                <div id="chartOverviewTraffic" class="h-64 w-full"></div>
                                <div id="chartOverviewTrafficSkel" class="lwa-skeleton absolute inset-0 h-64 w-full"></div>
                            </div>
                        </div>
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Pages</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths'] ?? [], 'key' => 'top_paths'])
                        </div>
                    </div>

                    <!-- Supporting reports -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Traffic Sources</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_referrers'] ?? [], 'key' => 'top_referrers'])
                        </div>
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Countries</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? [], 'key' => 'top_countries'])
                        </div>
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Device Breakdown</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_devices'] ?? [], 'key' => 'top_devices'])
                        </div>
                    </div>
