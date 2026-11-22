                    <!-- 5 KPI Cards Row (Total requests, Data transfer, Visits, Cache/Success rate, 5xx rate) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                        <!-- Total requests -->
                        <div
                            class="lwa-surface border lwa-border rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                                    data-i18n="totalRequests">Total requests</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white"
                                        id="kpiRequests">{{ $analytics['summary']['total_requests_fmt'] ?? '0' }}</span>
                                </div>
                            </div>
                            <div id="sparklineRequests" class="mt-2 h-7 w-full"></div>
                        </div>

                        <!-- Total data transfer -->
                        <div
                            class="lwa-surface border lwa-border rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                                    data-i18n="dataTransfer">Total data transfer</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white"
                                        id="kpiBytes">{{ $analytics['summary']['total_bytes_fmt'] ?? '0 B' }}</span>
                                </div>
                            </div>
                            <div id="sparklineBytes" class="mt-2 h-7 w-full"></div>
                        </div>

                        <!-- Total visits -->
                        <div
                            class="lwa-surface border lwa-border rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                                    data-i18n="visits">Total visits</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white"
                                        id="kpiVisits">{{ $analytics['summary']['unique_visitors_fmt'] ?? '0' }}</span>
                                </div>
                            </div>
                            <div id="sparklineVisits" class="mt-2 h-7 w-full"></div>
                        </div>

                        <!-- Cache hit / Success rate -->
                        <div
                            class="lwa-surface border lwa-border rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                                    data-i18n="successRate">Success rate (2xx)</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white"
                                        id="kpiSuccessRate">{{ $analytics['summary']['success_rate'] ?? '100' }}%</span>
                                </div>
                            </div>
                            <div class="mt-2 h-7 flex items-center text-[10px] text-slate-400">
                                {{ $analytics['summary']['status_counts']['2xx'] ?? 0 }} successful requests
                            </div>
                        </div>

                        <!-- 5xx error rate -->
                        <div
                            class="lwa-surface border lwa-border rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                                    data-i18n="errorRate">5xx error rate</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpi5xxRate">
                                        {{ number_format(($analytics['summary']['total_requests'] ?? 0) > 0 ? (($analytics['summary']['status_counts']['5xx'] ?? 0) / $analytics['summary']['total_requests']) * 100 : 0, 2) }}%
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 h-7 flex items-center text-[10px] text-slate-400">
                                {{ $analytics['summary']['status_counts']['5xx'] ?? 0 }} server failures
                            </div>
                        </div>
                    </div>

                    <!-- Charts Grid Row 1 (Requests over time & Data transfer) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Requests over time -->
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Requests over time
                                </div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <span class="text-slate-400">Group by:</span>
                                    <select
                                        class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded px-1.5 py-0.5 text-slate-700 dark:text-slate-300 focus:outline-none">
                                        <option>None</option>
                                        <option>Status Code</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mb-2">
                                <span class="w-2 h-2 rounded-full bg-[#0051c3]"></span> Requests: <strong
                                    class="text-slate-800 dark:text-slate-200">{{ $analytics['summary']['total_requests_fmt'] ?? 0 }}</strong>
                            </div>
                            <div class="relative">
                            <div id="chartRequestsOverTime" class="h-64 w-full"></div>
                            <div id="chartRequestsOverTimeSkel" class="lwa-skeleton absolute inset-0 h-64 w-full"></div>
                        </div>
                        </div>

                        <!-- Data transfer over time -->
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Data transfer
                                </div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <span class="text-slate-400">Group by:</span>
                                    <select
                                        class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded px-1.5 py-0.5 text-slate-700 dark:text-slate-300 focus:outline-none">
                                        <option>None</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mb-2">
                                <span class="w-2 h-2 rounded-full bg-[#0051c3]"></span> Data transfer: <strong
                                    class="text-slate-800 dark:text-slate-200">{{ $analytics['summary']['total_bytes_fmt'] ?? '0 B' }}</strong>
                            </div>
                            <div class="relative">
                            <div id="chartDataTransfer" class="h-64 w-full"></div>
                            <div id="chartDataTransferSkel" class="lwa-skeleton absolute inset-0 h-64 w-full"></div>
                        </div>
                        </div>
                    </div>

                    <!-- Charts Grid Row 2 (Requests by status code & Requests by method) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Requests by status code -->
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Requests by status
                                    code</div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                    <span class="flex items-center gap-1"><span
                                            class="w-2 h-2 rounded-full bg-emerald-500"></span> 2xx:
                                        {{ $analytics['summary']['status_counts']['2xx'] ?? 0 }}</span>
                                    <span class="flex items-center gap-1"><span
                                            class="w-2 h-2 rounded-full bg-cyan-500"></span> 3xx:
                                        {{ $analytics['summary']['status_counts']['3xx'] ?? 0 }}</span>
                                    <span class="flex items-center gap-1"><span
                                            class="w-2 h-2 rounded-full bg-rose-500"></span> 4xx:
                                        {{ $analytics['summary']['status_counts']['4xx'] ?? 0 }}</span>
                                    <span class="flex items-center gap-1"><span
                                            class="w-2 h-2 rounded-full bg-purple-500"></span> 5xx:
                                        {{ $analytics['summary']['status_counts']['5xx'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="relative">
                            <div id="chartStatusCodeBars" class="h-64 w-full"></div>
                            <div id="chartStatusCodeBarsSkel" class="lwa-skeleton absolute inset-0 h-64 w-full"></div>
                        </div>
                        </div>

                        <!-- Requests by method -->
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Requests by HTTP
                                    method</div>
                                <div class="text-[11px] text-slate-400">GET, POST, PUT, DELETE breakdown</div>
                            </div>
                            <div class="relative">
                            <div id="chartMethodBars" class="h-64 w-full"></div>
                            <div id="chartMethodBarsSkel" class="lwa-skeleton absolute inset-0 h-64 w-full"></div>
                        </div>
                        </div>
                    </div>

                    <!-- Breakdown Lists Grid (2 Columns, Progress Bar Style) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Top paths -->
                        <div id="section-top-pages" class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm scroll-mt-32">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top paths</div>
                            @include('web-analytics::partials.breakdown-list', [
                                'items' => $analytics['breakdowns']['top_paths'] ?? [],
                            ])
                        </div>

                        <!-- Top countries -->
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top countries
                            </div>
                            @include('web-analytics::partials.breakdown-list', [
                                'items' => $analytics['breakdowns']['top_countries'] ?? [],
                            ])
                        </div>

                        <!-- Top client IPs -->
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top client IPs
                            </div>
                            @include('web-analytics::partials.breakdown-list', [
                                'items' => $analytics['breakdowns']['top_ips'] ?? [],
                            ])
                        </div>

                        <!-- Top operating systems -->
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top operating
                                systems</div>
                            @include('web-analytics::partials.breakdown-list', [
                                'items' => $analytics['breakdowns']['top_os'] ?? [],
                            ])
                        </div>

                        <!-- Top browsers -->
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top browsers
                            </div>
                            @include('web-analytics::partials.breakdown-list', [
                                'items' => $analytics['breakdowns']['top_browsers'] ?? [],
                            ])
                        </div>

                        <!-- Top referrers -->
                        <div id="section-traffic-sources" class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm scroll-mt-32">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top referrers
                            </div>
                            @include('web-analytics::partials.breakdown-list', [
                                'items' => $analytics['breakdowns']['top_referrers'] ?? [],
                            ])
                        </div>
                    </div>
