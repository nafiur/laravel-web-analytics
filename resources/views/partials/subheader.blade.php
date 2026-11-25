            <!-- Subheader Bar: Segmented Tabs & Filters -->
            <div class="lwa-app-subheader px-4 sm:px-6 py-2 sticky top-14 z-30">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <!-- Segmented Sub-Tabs (hidden on the full Settings screen) -->
                    @if(($section ?? '') !== 'settings')
                    <div id="segmentedTabs" class="lwa-segmented">
                        <a href="{{ route('lwa_overview') }}" class="cf-tab-btn {{ ($section ?? '') === 'overview' ? 'active' : '' }}" data-i18n="overview">Overview</a>
                        <a href="{{ route('lwa_visitors') }}" class="cf-tab-btn {{ ($section ?? '') === 'visitors' ? 'active' : '' }}" data-i18n="visitors">Visitors</a>
                        <a href="{{ route('lwa_performance') }}" class="cf-tab-btn {{ ($section ?? '') === 'performance' ? 'active' : '' }}" data-i18n="performance">Performance</a>
                        <a href="{{ route('lwa_errors') }}" class="cf-tab-btn {{ ($section ?? '') === 'errors' ? 'active' : '' }}" data-i18n="errors">Error Monitoring</a>
                        <a href="{{ route('lwa_realtime') }}" class="cf-tab-btn {{ ($section ?? '') === 'realtime' ? 'active' : '' }}">
                            <span class="flex items-center gap-1.5">
                                <span class="live-dot"></span> <span data-i18n="realtime">Real-time</span>
                            </span>
                        </a>
                    </div>
                    @endif

                    <!-- Right Controls: filters, range, export -->
                    <div class="flex flex-wrap items-center gap-1.5">
                        <!-- Data source switcher -->
                        <select onchange="switchDataSource(this.value)" title="Analytics data source"
                            class="lwa-control">
                            <option value="database" {{ ($source ?? 'database') === 'database' ? 'selected' : '' }}>
                                Database</option>
                            <option value="file" {{ ($source ?? 'database') === 'file' ? 'selected' : '' }}>Server log
                                file</option>
                        </select>

                        <!-- Add Filter Button -->
                        <button type="button"
                            onclick="document.getElementById('quickFilterInput').classList.toggle('hidden'); document.getElementById('quickFilterInput').focus();"
                            class="lwa-btn">
                            <i data-lucide="filter" class="text-sm"></i> <span data-i18n="addFilter">Add filter</span>
                        </button>

                        <input type="text" id="quickFilterInput" placeholder="Filter path or IP..."
                            class="lwa-control hidden w-44">

                        <!-- Time Range Dropdown -->
                        <div class="relative inline-block text-left" id="rangeDropdown">
                            <button type="button"
                                onclick="document.getElementById('rangeMenu').classList.toggle('hidden')"
                                class="lwa-btn font-semibold">
                                <i data-lucide="clock" class="text-sm"></i>
                                <span id="rangeLabel">
                                    @if (($timeRange ?? '24h') === '1h')
                                        Last 1 hour
                                    @elseif(($timeRange ?? '24h') === '6h')
                                        Last 6 hours
                                    @elseif(($timeRange ?? '24h') === '12h')
                                        Last 12 hours
                                    @elseif(($timeRange ?? '24h') === 'today')
                                        Today
                                    @elseif(($timeRange ?? '24h') === '7d')
                                        Last 7 days
                                    @elseif(($timeRange ?? '24h') === '30d')
                                        Last 30 days
                                    @elseif(($timeRange ?? '24h') === 'all')
                                        All time
                                    @elseif(($timeRange ?? '24h') === 'custom')
                                        Custom range
                                    @else
                                        Last 24 hours
                                    @endif
                                </span>
                                <i data-lucide="chevron-down" class="text-xs"></i>
                            </button>

                            <div id="rangeMenu"
                                class="hidden absolute right-0 mt-1.5 w-44 lwa-card p-1 z-50">
                                <a href="{{ route('lwa_dashboard', ['range' => '1h']) }}"
                                    class="block px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last
                                    1 hour</a>
                                <a href="{{ route('lwa_dashboard', ['range' => '6h']) }}"
                                    class="block px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last
                                    6 hours</a>
                                <a href="{{ route('lwa_dashboard', ['range' => '24h']) }}"
                                    class="block px-2.5 py-1.5 rounded text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700">Last
                                    24 hours</a>
                                <a href="{{ route('lwa_dashboard', ['range' => 'today']) }}"
                                    class="block px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Today</a>
                                <a href="{{ route('lwa_dashboard', ['range' => '7d']) }}"
                                    class="block px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last
                                    7 days</a>
                                <a href="{{ route('lwa_dashboard', ['range' => '30d']) }}"
                                    class="block px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last
                                    30 days</a>
                                <a href="{{ route('lwa_dashboard', ['range' => 'all']) }}"
                                    class="block px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">All
                                    time</a>
                                <button type="button"
                                    onclick="document.getElementById('customRangeFields').classList.toggle('hidden')"
                                    class="w-full text-left block px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Custom
                                    range...</button>
                            </div>
                            <div id="customRangeFields"
                                class="absolute right-0 mt-1.5 w-64 lwa-card p-3 z-50 {{ ($timeRange ?? '24h') === 'custom' ? '' : 'hidden' }}">
                                <div class="lwa-section-label mb-2">Custom date range</div>
                                <div class="flex items-center gap-1.5">
                                    <input type="date" id="customStartDate" value="{{ $customStart ?? '' }}"
                                        class="lwa-control w-full">
                                    <span class="text-slate-400 text-xs">to</span>
                                    <input type="date" id="customEndDate" value="{{ $customEnd ?? '' }}"
                                        class="lwa-control w-full">
                                </div>
                                <button type="button" onclick="applyCustomRange()"
                                    class="mt-2 w-full lwa-btn-primary">Apply</button>
                            </div>
                        </div>

                        <!-- Auto refresh interval -->
                        <select id="lwaAutoRefresh" title="Auto refresh reports" class="lwa-control">
                            <option value="0">Auto: Off</option>
                            <option value="5">Every 5s</option>
                            <option value="10">Every 10s</option>
                            <option value="30">Every 30s</option>
                        </select>

                        <!-- Refresh current report -->
                        <button type="button" id="lwaRefreshBtn"
                            class="lwa-btn p-1.5"
                            title="Refresh data" aria-label="Refresh data">
                            <i data-lucide="refresh-cw" class="text-sm"></i>
                        </button>

                        <!-- Export to CSV -->
                        <a href="{{ route('lwa_export', array_filter(['range' => $timeRange ?? '24h', 'tenant_id' => $tenantId ?? null, 'start_date' => $customStart ?? null, 'end_date' => $customEnd ?? null])) }}"
                            class="lwa-btn lwa-export-link"
                            title="Export filtered logs to CSV">
                            <i data-lucide="download" class="text-sm"></i> <span data-i18n="export">Export</span>
                        </a>
                    </div>
                </div>

                <!-- Error toast with retry -->
                <div id="lwaErrorToast"
                    class="hidden fixed bottom-4 right-4 z-50 max-w-sm px-4 py-3 lwa-card">
                    <div class="flex items-start gap-2">
                        <i data-lucide="circle-alert" class="text-base text-rose-500 mt-0.5"></i>
                        <div class="flex-1">
                            <div class="font-semibold text-slate-900 dark:text-white">Could not refresh data</div>
                            <div class="text-slate-500 dark:text-slate-400 mt-0.5">Check your connection and try again.</div>
                            <button type="button" onclick="document.getElementById('lwaRefreshBtn').click()"
                                class="mt-2 text-blue-600 dark:text-blue-400 hover:underline font-medium">Retry</button>
                        </div>
                        <button type="button" onclick="this.closest('#lwaErrorToast').classList.add('hidden')"
                            class="text-slate-400 hover:text-slate-600" aria-label="Dismiss">✕</button>
                    </div>
                </div>
            </div>
