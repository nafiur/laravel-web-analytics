                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Referrers</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_referrers'] ?? []])
                    </div>
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">UTM Sources</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_sources'] ?? []])
                    </div>
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">UTM Mediums</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_mediums'] ?? []])
                    </div>
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Client IPs</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_ips'] ?? []])
                    </div>
                </div>
