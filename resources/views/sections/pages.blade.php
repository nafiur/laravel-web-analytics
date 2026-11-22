                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Pages</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths'] ?? []])
                    </div>
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Paths by Bandwidth</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths_bandwidth'] ?? []])
                    </div>
                </div>
