            <!-- Subheader Bar: Segmented Tabs & Filters -->
            <div class="bg-white dark:bg-[#111827] border-b border-slate-200 dark:border-slate-800 px-4 sm:px-6 py-2.5 sticky top-14 z-30">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <!-- Cloudflare Segmented Sub-Tabs -->
                    <div class="flex items-center p-1 bg-slate-100 dark:bg-slate-800/80 rounded-lg overflow-x-auto no-scrollbar">
                        <button type="button" class="cf-tab-btn active" data-target="#tab-requests">Requests</button>
                        <button type="button" class="cf-tab-btn" data-target="#tab-visitors">Visitors</button>
                        <button type="button" class="cf-tab-btn" data-target="#tab-performance">Performance</button>
                        <button type="button" class="cf-tab-btn" data-target="#tab-security">Security</button>
                        <button type="button" class="cf-tab-btn" data-target="#tab-live">
                            <span class="flex items-center gap-1.5">
                                <span class="live-dot"></span> Live Stream
                            </span>
                        </button>
                    </div>

                    <!-- Right Controls: Add Filter, Range Dropdown, Export -->
                    <div class="flex items-center gap-2">
                        <!-- Data source switcher -->
                        <select onchange="switchDataSource(this.value)" title="Analytics data source"
                                class="text-xs px-2 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md text-slate-700 dark:text-slate-300 focus:outline-none">
                            <option value="database" {{ ($source ?? 'database') === 'database' ? 'selected' : '' }}>Database</option>
                            <option value="file" {{ ($source ?? 'database') === 'file' ? 'selected' : '' }}>Server log file</option>
                        </select>

                        <!-- Add Filter Button -->
                        <button type="button" onclick="document.getElementById('quickFilterInput').classList.toggle('hidden'); document.getElementById('quickFilterInput').focus();"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm transition">
                            <i class="bx bx-filter text-sm text-slate-400"></i> Add filter
                        </button>

                        <input type="text" id="quickFilterInput" placeholder="Filter path or IP..." 
                               class="hidden text-xs px-2.5 py-1.5 border border-slate-300 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500 w-44">

                        <!-- Time Range Dropdown -->
                        <div class="relative inline-block text-left" id="rangeDropdown">
                            <button type="button" onclick="document.getElementById('rangeMenu').classList.toggle('hidden')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm transition">
                                <i class="bx bx-time-five text-sm text-slate-400"></i>
                                <span id="rangeLabel">
                                    @if(($timeRange ?? '24h') === '1h') Last 1 hour
                                    @elseif(($timeRange ?? '24h') === '6h') Last 6 hours
                                    @elseif(($timeRange ?? '24h') === '12h') Last 12 hours
                                    @elseif(($timeRange ?? '24h') === 'today') Today
                                    @elseif(($timeRange ?? '24h') === '7d') Last 7 days
                                    @elseif(($timeRange ?? '24h') === '30d') Last 30 days
                                    @elseif(($timeRange ?? '24h') === 'all') All time
                                    @elseif(($timeRange ?? '24h') === 'custom') Custom range
                                    @else Last 24 hours
                                    @endif
                                </span>
                                <i class="bx bx-chevron-down text-xs text-slate-400"></i>
                            </button>

                            <div id="rangeMenu" class="hidden absolute right-0 mt-1 w-44 rounded-md bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 z-50 py-1">
                                <a href="{{ route('lwa_dashboard', ['range' => '1h']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last 1 hour</a>
                                <a href="{{ route('lwa_dashboard', ['range' => '6h']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last 6 hours</a>
                                <a href="{{ route('lwa_dashboard', ['range' => '24h']) }}" class="block px-3 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700">Last 24 hours</a>
                                <a href="{{ route('lwa_dashboard', ['range' => 'today']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Today</a>
                                <a href="{{ route('lwa_dashboard', ['range' => '7d']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last 7 days</a>
                                <a href="{{ route('lwa_dashboard', ['range' => '30d']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last 30 days</a>
                                <a href="{{ route('lwa_dashboard', ['range' => 'all']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">All time</a>
                                <button type="button" onclick="document.getElementById('customRangeFields').classList.toggle('hidden')" class="w-full text-left block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Custom range...</button>
                            </div>
                            <div id="customRangeFields" class="absolute right-0 mt-1 w-64 rounded-md bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 z-50 p-2.5 {{ ($timeRange ?? '24h') === 'custom' ? '' : 'hidden' }}">
                                <div class="text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-2">Custom date range</div>
                                <div class="flex items-center gap-1.5">
                                    <input type="date" id="customStartDate" value="{{ $customStart ?? '' }}" class="w-full text-[11px] px-1.5 py-1 border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                                    <span class="text-slate-400 text-xs">to</span>
                                    <input type="date" id="customEndDate" value="{{ $customEnd ?? '' }}" class="w-full text-[11px] px-1.5 py-1 border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                                </div>
                                <button type="button" onclick="applyCustomRange()" class="mt-2 w-full px-2 py-1 text-[11px] font-semibold text-white bg-[#0051c3] hover:bg-[#003d94] rounded">Apply</button>
                            </div>
                        </div>

                        <!-- Export to CSV -->
                        <a href="{{ route('lwa_export', array_filter(['range' => $timeRange ?? '24h', 'tenant_id' => $tenantId ?? null, 'start_date' => $customStart ?? null, 'end_date' => $customEnd ?? null])) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm transition"
                           title="Export filtered logs to CSV">
                            <i class="bx bx-download text-sm"></i> Export
                        </a>
                    </div>
                </div>
            </div>
