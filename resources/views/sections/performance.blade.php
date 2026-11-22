                <!-- ============================================== -->
                <!-- TAB 3: PERFORMANCE                             -->
                <!-- ============================================== -->
                <div id="tab-performance" class="tab-pane space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Origin response (Avg)</div>
                            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $analytics['summary']['avg_response_time'] ?? 0 }} ms</div>
                            <div class="text-[11px] text-slate-400 mt-1">Average execution latency per request</div>
                        </div>

                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Bandwidth Transferred</div>
                            <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $analytics['summary']['total_bytes_fmt'] ?? '0 B' }}</div>
                            <div class="text-[11px] text-slate-400 mt-1">Total payload egress sent to clients</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Origin response time over time</div>
                            <div class="relative">
                            <div id="chartLatency" class="h-64 w-full"></div>
                            <div id="chartLatencySkel" class="lwa-skeleton absolute inset-0 h-64 w-full"></div>
                        </div>
                        </div>

                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top paths by data transfer</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths_bandwidth'] ?? []])
                        </div>
                    </div>
                </div>
