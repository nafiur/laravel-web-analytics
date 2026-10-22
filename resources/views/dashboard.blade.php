<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ request()->getHost() ?: config('app.name', 'Laravel') }} - Web Analytics</title>
    
    <!-- Tailwind CSS (Play CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        cf: {
                            blue: '#0051c3',
                            hover: '#003d94',
                            border: '#e5e7eb',
                            card: '#ffffff',
                            bg: '#f8fafc',
                            subtle: '#64748b'
                        },
                        dark: {
                            bg: '#0b0f19',
                            card: '#111827',
                            border: '#1f2937',
                            text: '#f1f5f9',
                            subtle: '#94a3b8'
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Boxicons & Google Fonts -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        
        /* Cloudflare Nav Tabs */
        .cf-tab-btn {
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            white-space: nowrap;
        }
        .cf-tab-btn:hover {
            color: #0f172a;
            background-color: #f1f5f9;
        }
        .dark .cf-tab-btn {
            color: #94a3b8;
        }
        .dark .cf-tab-btn:hover {
            color: #ffffff;
            background-color: #1e293b;
        }
        .cf-tab-btn.active {
            color: #0051c3;
            background-color: #eff6ff;
            font-weight: 600;
        }
        .dark .cf-tab-btn.active {
            color: #38bdf8;
            background-color: rgba(56, 189, 248, 0.12);
        }

        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        .live-dot {
            width: 7px;
            height: 7px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 1.8s infinite;
        }
        @keyframes pulse-green {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* ApexCharts Overrides */
        .apexcharts-tooltip {
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
            border: 1px solid #e2e8f0 !important;
        }
        .dark .apexcharts-tooltip {
            background: #111827 !important;
            border: 1px solid #374151 !important;
            color: #f3f4f6 !important;
        }
        .dark .apexcharts-tooltip-title {
            background: #1f2937 !important;
            border-bottom: 1px solid #374151 !important;
        }
        .apexcharts-xaxistooltip, .apexcharts-yaxistooltip {
            display: none !important;
        }
    </style>
</head>
<body class="bg-[#f8fafc] dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 min-h-full flex flex-col transition-colors duration-200">

    <!-- ============ TOP NAVBAR ============ -->
    <header class="bg-white dark:bg-[#111827] border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Row 1: Site Header & Actions -->
            <div class="flex items-center justify-between h-14 border-b border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center gap-3">
                    <button type="button" class="text-slate-400 hover:text-amber-500 transition" title="Favorite">
                        <i class="bx bxs-star text-base text-amber-400"></i>
                    </button>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-base text-slate-900 dark:text-white tracking-tight">
                            {{ request()->getHost() ?: config('app.name', 'Laravel') }}
                        </span>
                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center gap-1">
                            free <i class="bx bx-chevron-down text-xs text-slate-400"></i>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Live indicator -->
                    <div class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-md">
                        <span class="live-dot"></span> Live
                    </div>

                    <!-- Export to CSV -->
                    <a href="{{ route('lwa_export', ['range' => $timeRange ?? '24h']) }}" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm transition"
                       title="Export filtered logs to CSV">
                        <i class="bx bx-download text-sm"></i> Export
                    </a>

                    <!-- Theme Toggle -->
                    <button type="button" id="themeToggle" 
                            class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition"
                            title="Toggle Light/Dark Theme">
                        <i id="themeIcon" class="bx bx-moon text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Row 2: Navigation Tabs & Filter Bar -->
            <div class="flex flex-wrap items-center justify-between gap-3 py-2.5">
                <!-- Navigation Tabs -->
                <nav class="flex items-center gap-1 overflow-x-auto no-scrollbar">
                    <button type="button" class="cf-tab-btn active" data-target="#tab-requests">Requests</button>
                    <button type="button" class="cf-tab-btn" data-target="#tab-visitors">Visitors</button>
                    <button type="button" class="cf-tab-btn" data-target="#tab-performance">Performance</button>
                    <button type="button" class="cf-tab-btn" data-target="#tab-security">Security</button>
                    <button type="button" class="cf-tab-btn" data-target="#tab-live">Live Stream</button>
                </nav>

                <!-- Filter & Range Controls -->
                <div class="flex items-center gap-2">
                    <!-- Add Filter Button -->
                    <button type="button" onclick="document.getElementById('quickFilterInput').classList.toggle('hidden'); document.getElementById('quickFilterInput').focus();"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm transition">
                        <i class="bx bx-filter text-sm text-slate-400"></i> Add filter
                    </button>

                    <input type="text" id="quickFilterInput" placeholder="Filter path or IP..." 
                           class="hidden text-xs px-2.5 py-1.5 border border-slate-300 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-blue-500 w-44">

                    <!-- Time Range Dropdown -->
                    <div class="relative inline-block text-left" id="rangeDropdown">
                        <button type="button" onclick="document.getElementById('rangeMenu').classList.toggle('hidden')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm transition">
                            <i class="bx bx-calendar text-sm text-slate-400"></i>
                            <span id="rangeLabel">
                                @if(($timeRange ?? '24h') === '1h') Last 1 hour
                                @elseif(($timeRange ?? '24h') === '6h') Last 6 hours
                                @elseif(($timeRange ?? '24h') === '7d') Last 7 days
                                @elseif(($timeRange ?? '24h') === '30d') Last 30 days
                                @else Last 24 hours
                                @endif
                            </span>
                            <i class="bx bx-chevron-down text-xs text-slate-400"></i>
                        </button>

                        <div id="rangeMenu" class="hidden absolute right-0 mt-1 w-44 rounded-md bg-white dark:bg-slate-800 shadow-lg border border-slate-200 dark:border-slate-700 z-50 py-1">
                            <a href="{{ route('lwa_dashboard', ['range' => '1h']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last 1 hour</a>
                            <a href="{{ route('lwa_dashboard', ['range' => '6h']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last 6 hours</a>
                            <a href="{{ route('lwa_dashboard', ['range' => '24h']) }}" class="block px-3 py-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-700">Last 24 hours</a>
                            <a href="{{ route('lwa_dashboard', ['range' => '7d']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last 7 days</a>
                            <a href="{{ route('lwa_dashboard', ['range' => '30d']) }}" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700">Last 30 days</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ============ MAIN CONTENT ============ -->
    <main class="flex-1 max-w-[1440px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <!-- ============================================== -->
        <!-- TAB 1: REQUESTS                                -->
        <!-- ============================================== -->
        <div id="tab-requests" class="tab-pane active space-y-6">
            <!-- 5 KPI Cards Row (Cloudflare Style) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                <!-- Total requests -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="text-[12px] font-medium text-slate-500 dark:text-slate-400">Total requests</div>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpiRequests">{{ $analytics['summary']['total_requests_fmt'] ?? '0' }}</span>
                            <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                                <i class="bx bx-down-arrow-alt text-xs"></i> 0.0%
                            </span>
                        </div>
                    </div>
                    <div id="sparklineRequests" class="mt-2 h-7 w-full"></div>
                </div>

                <!-- Total data transfer -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="text-[12px] font-medium text-slate-500 dark:text-slate-400">Total data transfer</div>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpiBytes">{{ $analytics['summary']['total_bytes_fmt'] ?? '0 B' }}</span>
                            <span class="text-[11px] font-medium text-rose-500 dark:text-rose-400 flex items-center">
                                <i class="bx bx-down-arrow-alt text-xs"></i> 92.7%
                            </span>
                        </div>
                    </div>
                    <div id="sparklineBytes" class="mt-2 h-7 w-full"></div>
                </div>

                <!-- Total visits -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="text-[12px] font-medium text-slate-500 dark:text-slate-400">Total visits (Unique)</div>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpiVisits">{{ $analytics['summary']['unique_visitors_fmt'] ?? '0' }}</span>
                            <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                                <i class="bx bx-up-arrow-alt text-xs"></i> 100.0%
                            </span>
                        </div>
                    </div>
                    <div id="sparklineVisits" class="mt-2 h-7 w-full"></div>
                </div>

                <!-- Success rate -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="text-[12px] font-medium text-slate-500 dark:text-slate-400">Success rate (2xx)</div>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpiSuccessRate">{{ $analytics['summary']['success_rate'] ?? '100' }}%</span>
                            <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                                <i class="bx bx-up-arrow-alt text-xs"></i> 99.8%
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 h-7 flex items-center text-[11px] text-slate-400">
                        {{ $analytics['summary']['status_counts']['2xx'] ?? 0 }} successful hits
                    </div>
                </div>

                <!-- 5xx error rate -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="text-[12px] font-medium text-slate-500 dark:text-slate-400">5xx error rate</div>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpi5xxRate">
                                {{ number_format(($analytics['summary']['total_requests'] ?? 0) > 0 ? (($analytics['summary']['status_counts']['5xx'] ?? 0) / $analytics['summary']['total_requests']) * 100 : 0, 2) }}%
                            </span>
                            <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                                <i class="bx bx-down-arrow-alt text-xs"></i> 0.0%
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 h-7 flex items-center text-[11px] text-slate-400">
                        {{ $analytics['summary']['status_counts']['5xx'] ?? 0 }} server failures
                    </div>
                </div>
            </div>

            <!-- Charts Grid Row 1 (Requests over time & Data transfer) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Requests over time -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Requests over time</div>
                        <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#0051c3]"></span>
                            Requests: <strong class="text-slate-700 dark:text-slate-300">{{ $analytics['summary']['total_requests_fmt'] ?? 0 }}</strong>
                        </div>
                    </div>
                    <div id="chartRequestsOverTime" class="h-64 w-full"></div>
                </div>

                <!-- Data transfer over time -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Data transfer</div>
                        <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#0051c3]"></span>
                            Data transfer: <strong class="text-slate-700 dark:text-slate-300">{{ $analytics['summary']['total_bytes_fmt'] ?? '0 B' }}</strong>
                        </div>
                    </div>
                    <div id="chartDataTransfer" class="h-64 w-full"></div>
                </div>
            </div>

            <!-- Charts Grid Row 2 (Requests by status code & Requests by method) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Requests by status code -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Requests by status code</div>
                        <div class="flex items-center gap-3 text-[11px]">
                            <span class="flex items-center gap-1 text-slate-500"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> 2xx: {{ $analytics['summary']['status_counts']['2xx'] ?? 0 }}</span>
                            <span class="flex items-center gap-1 text-slate-500"><span class="w-2 h-2 rounded-full bg-cyan-500"></span> 3xx: {{ $analytics['summary']['status_counts']['3xx'] ?? 0 }}</span>
                            <span class="flex items-center gap-1 text-slate-500"><span class="w-2 h-2 rounded-full bg-rose-500"></span> 4xx: {{ $analytics['summary']['status_counts']['4xx'] ?? 0 }}</span>
                            <span class="flex items-center gap-1 text-slate-500"><span class="w-2 h-2 rounded-full bg-purple-500"></span> 5xx: {{ $analytics['summary']['status_counts']['5xx'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div id="chartStatusCodeBars" class="h-64 w-full"></div>
                </div>

                <!-- Requests by method -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Requests by HTTP method</div>
                        <div class="text-[11px] text-slate-400">GET, POST, PUT, DELETE breakdown</div>
                    </div>
                    <div id="chartMethodBars" class="h-64 w-full"></div>
                </div>
            </div>

            <!-- Breakdown Lists Grid (2 Columns, Cloudflare Progress Bar Style) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Top paths -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top paths</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths'] ?? []])
                </div>

                <!-- Top countries -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top countries</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? []])
                </div>

                <!-- Top client IPs -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top client IPs</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_ips'] ?? []])
                </div>

                <!-- Top operating systems -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top operating systems</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_os'] ?? []])
                </div>

                <!-- Top browsers -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top browsers</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_browsers'] ?? []])
                </div>

                <!-- Top device types -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top device types</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_devices'] ?? []])
                </div>

                <!-- Top status codes -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top status codes</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_status_codes'] ?? []])
                </div>

                <!-- Top referrers -->
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top referrers</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_referrers'] ?? []])
                </div>
            </div>
        </div>


        <!-- ============================================== -->
        <!-- TAB 2: VISITORS                                -->
        <!-- ============================================== -->
        <div id="tab-visitors" class="tab-pane space-y-6">
            <!-- Top Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Unique Visitors</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $analytics['summary']['unique_visitors_fmt'] ?? '0' }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Calculated via hybrid visitor sessions</div>
                </div>

                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Active Live Visitors (Last 5m)</div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="live-dot"></span>
                        <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400" id="liveVisitorTabCounter">{{ $analytics['summary']['live_visitors_fmt'] ?? '1' }}</span>
                    </div>
                    <div class="text-[11px] text-slate-400 mt-1">Real-time active users currently browsing</div>
                </div>
            </div>

            <!-- Geography -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Geography</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Countries</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? []])
                    </div>
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Client IPs</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_ips'] ?? []])
                    </div>
                </div>
            </div>

            <!-- Technology -->
            <div class="space-y-2">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Technology</h3>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Device Types</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_devices'] ?? []])
                    </div>
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Operating Systems</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_os'] ?? []])
                    </div>
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                        <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Browsers</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_browsers'] ?? []])
                    </div>
                </div>
            </div>
        </div>


        <!-- ============================================== -->
        <!-- TAB 3: PERFORMANCE                             -->
        <!-- ============================================== -->
        <div id="tab-performance" class="tab-pane space-y-6">
            <!-- 2 KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Origin response (Avg)</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $analytics['summary']['avg_response_time'] ?? 0 }} ms</div>
                    <div class="text-[11px] text-slate-400 mt-1">Average execution latency per request</div>
                </div>

                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Bandwidth Transferred</div>
                    <div class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ $analytics['summary']['total_bytes_fmt'] ?? '0 B' }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Total payload egress sent to clients</div>
                </div>
            </div>

            <!-- Performance Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Origin response time over time</div>
                    <div id="chartLatency" class="h-64 w-full"></div>
                </div>

                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-5 shadow-sm">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 mb-3">Top paths by data transfer</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths_bandwidth'] ?? []])
                </div>
            </div>
        </div>


        <!-- ============================================== -->
        <!-- TAB 4: SECURITY & ERRORS                       -->
        <!-- ============================================== -->
        <div id="tab-security" class="tab-pane space-y-6">
            <!-- 3 Error KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Error Requests</div>
                    <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $analytics['summary']['error_count_fmt'] ?? 0 }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Error rate: {{ $analytics['summary']['error_rate'] ?? 0 }}%</div>
                </div>

                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">4xx Client Errors</div>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($analytics['summary']['status_counts']['4xx'] ?? 0) }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">404 Not Found, 401 Unauthorized, etc.</div>
                </div>

                <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400">5xx Server Errors</div>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ number_format($analytics['summary']['status_counts']['5xx'] ?? 0) }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">500 Internal Error, 502 Bad Gateway</div>
                </div>
            </div>

            <!-- Top Error URLs Table -->
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="text-sm font-semibold text-slate-800 dark:text-slate-200">Top Error URLs (4xx / 5xx)</div>
                    <span class="text-xs text-slate-400">Sorted by failure count</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 border-b border-slate-200 dark:border-slate-700/60">
                            <tr>
                                <th class="py-2.5 px-4 font-semibold">Status</th>
                                <th class="py-2.5 px-4 font-semibold">Method</th>
                                <th class="py-2.5 px-4 font-semibold">Path</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Count</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Last Seen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($analytics['top_error_paths'] ?? [] as $err)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                    <td class="py-2.5 px-4">
                                        <span class="px-2 py-0.5 rounded font-mono font-bold {{ $err['status_code'] >= 500 ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' }}">
                                            {{ $err['status_code'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-4 font-mono font-medium text-slate-600 dark:text-slate-300">{{ $err['method'] }}</td>
                                    <td class="py-2.5 px-4 font-mono text-slate-700 dark:text-slate-200 truncate max-w-sm" title="{{ $err['path'] }}">{{ $err['path'] }}</td>
                                    <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ $err['count_fmt'] }}</td>
                                    <td class="py-2.5 px-4 text-right text-slate-400">{{ $err['last_seen'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">All server requests are succeeding. Zero errors recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <!-- ============================================== -->
        <!-- TAB 5: REAL-TIME LIVE STREAM (SSE)             -->
        <!-- ============================================== -->
        <div id="tab-live" class="tab-pane space-y-4">
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="live-dot"></span>
                    <div>
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">Real-Time Traffic Feed</div>
                        <div class="text-xs text-slate-400">Streaming live requests via Server-Sent Events (SSE)</div>
                    </div>
                </div>
                <div class="text-xs text-slate-500 font-mono" id="lastUpdatedClock">Listening for traffic...</div>
            </div>

            <!-- Live Table -->
            <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 border-b border-slate-200 dark:border-slate-700/60">
                            <tr>
                                <th class="py-2.5 px-4 font-semibold">Status</th>
                                <th class="py-2.5 px-4 font-semibold">Method</th>
                                <th class="py-2.5 px-4 font-semibold">User</th>
                                <th class="py-2.5 px-4 font-semibold">Path</th>
                                <th class="py-2.5 px-4 font-semibold">IP Address</th>
                                <th class="py-2.5 px-4 font-semibold">Duration</th>
                                <th class="py-2.5 px-4 font-semibold">Payload</th>
                                <th class="py-2.5 px-4 font-semibold">Client</th>
                                <th class="py-2.5 px-4 font-semibold text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody id="tableLiveFeed" class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($analytics['recent_logs'] ?? [] as $log)
                                <tr id="log-row-{{ $log['id'] }}" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition">
                                    <td class="py-2.5 px-4">
                                        <span class="px-2 py-0.5 rounded font-mono font-bold {{ $log['status_code'] >= 500 ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400' : ($log['status_code'] >= 400 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' : ($log['status_code'] >= 300 ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400')) }}">
                                            {{ $log['status_code'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-4 font-mono font-semibold {{ $log['method'] === 'POST' ? 'text-sky-600' : 'text-emerald-600' }}">{{ $log['method'] }}</td>
                                    <td class="py-2.5 px-4">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] {{ $log['is_auth'] ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-400 font-medium' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                            {{ $log['user_name'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-4 font-mono text-slate-700 dark:text-slate-200 truncate max-w-[220px]" title="{{ $log['path'] }}">{{ $log['path'] }}</td>
                                    <td class="py-2.5 px-4 font-mono text-slate-500">{{ $log['ip_address'] }}</td>
                                    <td class="py-2.5 px-4 font-mono text-slate-600 dark:text-slate-300">{{ $log['response_time_ms'] ?? 0 }} ms</td>
                                    <td class="py-2.5 px-4 text-slate-500">{{ $log['bytes_sent_fmt'] ?? '-' }}</td>
                                    <td class="py-2.5 px-4 text-slate-500 dark:text-slate-400 truncate max-w-[140px]">{{ $log['browser'] }} on {{ $log['os'] }}</td>
                                    <td class="py-2.5 px-4 text-right text-slate-400">{{ $log['time_ago'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-6 text-center text-slate-400">No requests recorded yet. Incoming hits will stream here automatically.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

    <!-- ============ CLOUDFLARE STYLE FOOTER ============ -->
    <footer class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] py-5 mt-auto">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
            <div class="flex flex-wrap items-center gap-4">
                <a href="https://github.com/nafiur/laravel-web-analytics" target="_blank" class="hover:text-slate-900 dark:hover:text-white transition">Documentation</a>
                <a href="#" class="hover:text-slate-900 dark:hover:text-white transition">System Status</a>
                <a href="#" class="hover:text-slate-900 dark:hover:text-white transition">Privacy & GDPR</a>
            </div>
            <div>
                © {{ date('Y') }} Laravel Web Analytics • Real-Time Stream Engine
            </div>
        </div>
    </footer>

    <!-- ============ APEXCHARTS & JAVASCRIPT ============ -->
    <script>
        // Tab Switcher
        document.querySelectorAll('.cf-tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-target');
                document.querySelectorAll('.cf-tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                
                btn.classList.add('active');
                const pane = document.querySelector(target);
                if (pane) pane.classList.add('active');

                // Trigger chart resize on tab reveal
                window.dispatchEvent(new Event('resize'));
            });
        });

        // Theme Toggle (Light / Dark)
        (function() {
            const toggle = document.getElementById('themeToggle');
            const icon = document.getElementById('themeIcon');
            const html = document.documentElement;

            function applyTheme(isDark) {
                if (isDark) {
                    html.classList.add('dark');
                    icon.className = 'bx bx-sun text-lg text-amber-400';
                } else {
                    html.classList.remove('dark');
                    icon.className = 'bx bx-moon text-lg';
                }
            }

            const saved = localStorage.getItem('lwa_theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme(saved === 'dark' || (!saved && prefersDark));

            toggle.addEventListener('click', () => {
                const isNowDark = !html.classList.contains('dark');
                applyTheme(isNowDark);
                localStorage.setItem('lwa_theme', isNowDark ? 'dark' : 'light');
            });
        })();

        // Chart Data From PHP
        const timeLabels = @json($analytics['time_series']['labels'] ?? []);
        const requestsData = @json($analytics['time_series']['requests'] ?? []);
        const bandwidthData = @json($analytics['time_series']['bandwidth_mb'] ?? []);
        const latencyData = @json($analytics['time_series']['latency'] ?? []);
        const s2xxData = @json($analytics['time_series']['status_2xx'] ?? []);
        const s3xxData = @json($analytics['time_series']['status_3xx'] ?? []);
        const s4xxData = @json($analytics['time_series']['status_4xx'] ?? []);
        const s5xxData = @json($analytics['time_series']['status_5xx'] ?? []);

        // Base Chart Options (Cloudflare Style)
        const isDarkTheme = document.documentElement.classList.contains('dark');
        const gridColor = isDarkTheme ? '#1f2937' : '#f1f5f9';
        const labelColor = isDarkTheme ? '#94a3b8' : '#64748b';

        // 1. Requests Over Time (Area Chart)
        const chartRequests = new ApexCharts(document.querySelector("#chartRequestsOverTime"), {
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false },
                sparkline: { enabled: false },
                animations: { enabled: true, easing: 'easeinout' }
            },
            series: [{ name: 'Requests', data: requestsData }],
            xaxis: {
                categories: timeLabels,
                labels: { style: { colors: labelColor, fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { colors: labelColor, fontSize: '11px' } }
            },
            colors: ['#0051c3'],
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.02, stops: [0, 90, 100] }
            },
            grid: { borderColor: gridColor, strokeDashArray: 3 },
            dataLabels: { enabled: false }
        });
        chartRequests.render();

        // 2. Data Transfer Over Time (Area Chart)
        const chartTransfer = new ApexCharts(document.querySelector("#chartDataTransfer"), {
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false }
            },
            series: [{ name: 'Bandwidth (MB)', data: bandwidthData }],
            xaxis: {
                categories: timeLabels,
                labels: { style: { colors: labelColor, fontSize: '11px' } },
                axisBorder: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: labelColor, fontSize: '11px' },
                    formatter: (val) => val + ' MB'
                }
            },
            colors: ['#0284c7'],
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0.02, stops: [0, 90, 100] }
            },
            grid: { borderColor: gridColor, strokeDashArray: 3 },
            dataLabels: { enabled: false }
        });
        chartTransfer.render();

        // 3. Status Code Stacked Bars
        const chartStatusCodes = new ApexCharts(document.querySelector("#chartStatusCodeBars"), {
            chart: {
                type: 'bar',
                height: 250,
                stacked: true,
                toolbar: { show: false }
            },
            series: [
                { name: '2xx OK', data: s2xxData },
                { name: '3xx Redirect', data: s3xxData },
                { name: '4xx Client Err', data: s4xxData },
                { name: '5xx Server Err', data: s5xxData }
            ],
            xaxis: {
                categories: timeLabels,
                labels: { style: { colors: labelColor, fontSize: '11px' } },
                axisBorder: { show: false }
            },
            yaxis: { labels: { style: { colors: labelColor, fontSize: '11px' } } },
            colors: ['#10b981', '#06b6d4', '#f43f5e', '#8b5cf6'],
            grid: { borderColor: gridColor, strokeDashArray: 3 },
            dataLabels: { enabled: false },
            legend: { show: false }
        });
        chartStatusCodes.render();

        // 4. HTTP Methods Bars
        const methodCategories = @json(array_column($analytics['breakdowns']['top_methods'] ?? [], 'name'));
        const methodCounts = @json(array_column($analytics['breakdowns']['top_methods'] ?? [], 'count'));
        const chartMethods = new ApexCharts(document.querySelector("#chartMethodBars"), {
            chart: { type: 'bar', height: 250, toolbar: { show: false } },
            series: [{ name: 'Hits', data: methodCounts }],
            xaxis: {
                categories: methodCategories,
                labels: { style: { colors: labelColor, fontSize: '11px' } }
            },
            yaxis: { labels: { style: { colors: labelColor, fontSize: '11px' } } },
            colors: ['#f59e0b'],
            grid: { borderColor: gridColor, strokeDashArray: 3 },
            dataLabels: { enabled: false }
        });
        chartMethods.render();

        // 5. Latency Over Time (Performance Tab)
        const chartLatency = new ApexCharts(document.querySelector("#chartLatency"), {
            chart: { type: 'line', height: 250, toolbar: { show: false } },
            series: [{ name: 'Avg Response Time (ms)', data: latencyData }],
            xaxis: {
                categories: timeLabels,
                labels: { style: { colors: labelColor, fontSize: '11px' } }
            },
            yaxis: {
                labels: {
                    style: { colors: labelColor, fontSize: '11px' },
                    formatter: (val) => val + ' ms'
                }
            },
            colors: ['#0051c3'],
            stroke: { curve: 'smooth', width: 2.5 },
            grid: { borderColor: gridColor, strokeDashArray: 3 }
        });
        chartLatency.render();

        // Top Mini Sparklines
        function renderSparkline(selector, data, color) {
            new ApexCharts(document.querySelector(selector), {
                chart: { type: 'area', height: 28, sparkline: { enabled: true } },
                series: [{ data: data && data.length ? data : [0, 0] }],
                stroke: { curve: 'smooth', width: 1.5 },
                colors: [color],
                fill: { opacity: 0.15 }
            }).render();
        }
        renderSparkline('#sparklineRequests', requestsData, '#0051c3');
        renderSparkline('#sparklineBytes', bandwidthData, '#0284c7');
        renderSparkline('#sparklineVisits', requestsData, '#10b981');

        // ============ REAL-TIME SSE STREAM ============
        (function() {
            let lastTrafficId = {{ \Nafiur\WebAnalytics\Models\LwaLogs::max('id') ?? 0 }};
            let sseSource = null;

            function startSSE() {
                if (!window.EventSource) return;
                if (sseSource) sseSource.close();

                sseSource = new EventSource("{{ route('lwa_stream') }}?last_id=" + lastTrafficId);

                sseSource.addEventListener('traffic', function(e) {
                    try {
                        let parsed = JSON.parse(e.data);
                        if (parsed.last_id) lastTrafficId = parsed.last_id;
                        if (parsed.logs && parsed.logs.length > 0) {
                            parsed.logs.forEach(prependLiveFeedRow);
                        }
                    } catch (err) {}
                });

                sseSource.addEventListener('stats', function(e) {
                    try {
                        let stats = JSON.parse(e.data);
                        if (stats.live_visitors !== undefined) {
                            let el = document.getElementById('liveVisitorTabCounter');
                            if (el) el.textContent = stats.live_visitors;
                        }
                        if (stats.total_requests) {
                            let el = document.getElementById('kpiRequests');
                            if (el) el.textContent = Number(stats.total_requests).toLocaleString();
                        }
                    } catch (err) {}
                });

                sseSource.addEventListener('ping', function(e) {
                    try {
                        let p = JSON.parse(e.data);
                        if (p.last_id) lastTrafficId = p.last_id;
                    } catch (err) {}
                });
            }

            startSSE();

            function prependLiveFeedRow(log) {
                let tbody = document.getElementById('tableLiveFeed');
                if (!tbody) return;

                let rowId = 'log-row-' + log.id;
                if (document.getElementById(rowId)) return;

                let st = parseInt(log.status_code || 200);
                let stBadge = st >= 500 ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400' 
                            : (st >= 400 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400'
                            : (st >= 300 ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-400'
                            : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400'));

                let tr = document.createElement('tr');
                tr.id = rowId;
                tr.className = 'bg-blue-50/50 dark:bg-blue-950/20 transition-all duration-1000';
                tr.innerHTML = `
                    <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded font-mono font-bold ${stBadge}">${log.status_code || 200}</span></td>
                    <td class="py-2.5 px-4 font-mono font-semibold ${log.method === 'POST' ? 'text-sky-600' : 'text-emerald-600'}">${log.method}</td>
                    <td class="py-2.5 px-4"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] ${log.is_auth ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-400 font-medium' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'}">${log.user_name}</span></td>
                    <td class="py-2.5 px-4 font-mono text-slate-700 dark:text-slate-200 truncate max-w-[220px]" title="${log.path}">${log.path}</td>
                    <td class="py-2.5 px-4 font-mono text-slate-500">${log.ip_address}</td>
                    <td class="py-2.5 px-4 font-mono text-slate-600 dark:text-slate-300">${log.response_time_ms || 0} ms</td>
                    <td class="py-2.5 px-4 text-slate-500">${log.bytes_sent_fmt || '-'}</td>
                    <td class="py-2.5 px-4 text-slate-500 dark:text-slate-400 truncate max-w-[140px]">${log.browser || 'Other'} on ${log.os || 'Other'}</td>
                    <td class="py-2.5 px-4 text-right text-slate-400">just now</td>
                `;

                tbody.insertBefore(tr, tbody.firstChild);
                setTimeout(() => { tr.className = 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-all duration-1000'; }, 1500);

                while (tbody.children.length > 30) {
                    tbody.removeChild(tbody.lastChild);
                }

                const clock = document.getElementById('lastUpdatedClock');
                if (clock) clock.textContent = 'Hit: ' + log.path + ' (' + new Date().toLocaleTimeString() + ')';
            }
        })();
    </script>
</body>
</html>
