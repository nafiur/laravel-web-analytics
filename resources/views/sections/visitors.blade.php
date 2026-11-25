                    <div>
                        <h1 class="lwa-page-title">Visitors</h1>
                        <p class="lwa-subtitle mt-1">Unique visitors, live activity and audience breakdowns.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="lwa-card p-4">
                            <div class="lwa-kpi-label">Total Unique Visitors</div>
                            <div class="lwa-kpi-value mt-1">{{ $analytics['summary']['unique_visitors_fmt'] ?? '0' }}</div>
                            <div class="lwa-subtitle mt-1">Calculated via hybrid visitor sessions</div>
                        </div>

                        <div class="lwa-card p-4">
                            <div class="lwa-kpi-label">Active Live Visitors (Last {{ config('laravel-web-analytics.active_visitor_window', 5) }}m)</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="live-dot"></span>
                                <span class="lwa-kpi-value" style="color: var(--lwa-success)" id="liveVisitorTabCounter">{{ $analytics['summary']['live_visitors_fmt'] ?? '0' }}</span>
                            </div>
                            <div class="lwa-subtitle mt-1">Real-time active users currently browsing</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div id="section-countries" class="lwa-card p-4 scroll-mt-32">
                            <div class="lwa-card-title mb-3">Top Countries</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? [], 'key' => 'top_countries'])
                        </div>
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-3">Top Client IPs</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_ips'] ?? [], 'key' => 'top_ips'])
                        </div>
                    </div>

                    <div id="section-devices" class="grid grid-cols-1 lg:grid-cols-3 gap-4 scroll-mt-32">
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-3">Device Types</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_devices'] ?? [], 'key' => 'top_devices'])
                        </div>
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-3">Operating Systems</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_os'] ?? [], 'key' => 'top_os'])
                        </div>
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-3">Browsers</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_browsers'] ?? [], 'key' => 'top_browsers'])
                        </div>
                    </div>

                    <div id="section-campaigns" class="grid grid-cols-1 lg:grid-cols-3 gap-4 scroll-mt-32">
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-3">UTM Sources</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_sources'] ?? [], 'key' => 'top_utm_sources'])
                        </div>
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-3">UTM Mediums</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_mediums'] ?? [], 'key' => 'top_utm_mediums'])
                        </div>
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-3">UTM Campaigns</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_campaigns'] ?? [], 'key' => 'top_utm_campaigns'])
                        </div>
                    </div>
