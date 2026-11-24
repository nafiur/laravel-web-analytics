                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Unique Visitors</div>
                            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $analytics['summary']['unique_visitors_fmt'] ?? '0' }}</div>
                            <div class="text-[11px] text-slate-400 mt-1">Calculated via hybrid visitor sessions</div>
                        </div>

                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Active Live Visitors (Last {{ config('laravel-web-analytics.active_visitor_window', 5) }}m)</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="live-dot"></span>
                                <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" id="liveVisitorTabCounter">{{ $analytics['summary']['live_visitors_fmt'] ?? '0' }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">Real-time active users currently browsing</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div id="section-countries" class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm scroll-mt-32">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Countries</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? [], 'key' => 'top_countries'])
                        </div>
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Client IPs</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_ips'] ?? [], 'key' => 'top_ips'])
                        </div>
                    </div>

                    <div id="section-devices" class="grid grid-cols-1 lg:grid-cols-3 gap-4 scroll-mt-32">
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Device Types</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_devices'] ?? [], 'key' => 'top_devices'])
                        </div>
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Operating Systems</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_os'] ?? [], 'key' => 'top_os'])
                        </div>
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Browsers</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_browsers'] ?? [], 'key' => 'top_browsers'])
                        </div>
                    </div>

                    <div id="section-campaigns" class="grid grid-cols-1 lg:grid-cols-3 gap-4 scroll-mt-32">
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">UTM Sources</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_sources'] ?? [], 'key' => 'top_utm_sources'])
                        </div>
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">UTM Mediums</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_mediums'] ?? [], 'key' => 'top_utm_mediums'])
                        </div>
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">UTM Campaigns</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_campaigns'] ?? [], 'key' => 'top_utm_campaigns'])
                        </div>
                    </div>
