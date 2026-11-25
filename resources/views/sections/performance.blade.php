                    <div>
                        <h1 class="lwa-page-title">Performance</h1>
                        <p class="lwa-subtitle mt-1">Origin latency and data transfer for the selected period.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="lwa-card p-4">
                            <div class="lwa-kpi-label">Origin response (Avg)</div>
                            <div class="lwa-kpi-value mt-1">{{ $analytics['summary']['avg_response_time'] ?? 0 }} ms</div>
                            <div class="lwa-subtitle mt-1">Average execution latency per request</div>
                        </div>

                        <div class="lwa-card p-4">
                            <div class="lwa-kpi-label">Total Bandwidth Transferred</div>
                            <div class="lwa-kpi-value mt-1">{{ $analytics['summary']['total_bytes_fmt'] ?? '0 B' }}</div>
                            <div class="lwa-subtitle mt-1">Total payload egress sent to clients</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-3">Origin response time over time</div>
                            <div class="relative">
                            <div id="chartLatency" class="h-64 w-full"></div>
                            <div id="chartLatencySkel" class="lwa-skeleton absolute inset-0 h-64 w-full"></div>
                        </div>
                        </div>

                        <div class="lwa-card p-4">
                            <div class="lwa-card-title mb-3">Top paths by data transfer</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths_bandwidth'] ?? [], 'key' => 'top_paths_bandwidth'])
                        </div>
                    </div>
