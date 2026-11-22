                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Device Types</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_devices'] ?? []])
                    </div>
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Operating Systems</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_os'] ?? []])
                    </div>
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Browsers</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_browsers'] ?? []])
                    </div>
                </div>
