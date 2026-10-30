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
                            blue: '#f6821f',
                            accent: '#0051c3',
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
        
        /* Custom scrollbar for modern feel */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark ::-webkit-scrollbar-thumb { background: #334155; }
        
        /* Cloudflare Nav Tabs */
        .cf-tab-btn {
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            padding: 6px 14px;
            border-radius: 6px;
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
            color: #0f172a;
            background-color: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
            font-weight: 600;
        }
        .dark .cf-tab-btn.active {
            color: #f8fafc;
            background-color: #1e293b;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.4);
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
<body class="bg-[#f8fafc] dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-200">

    <!-- ============ TOP NAVBAR ============ -->
    <header class="bg-white dark:bg-[#111827] border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40">
        <div class="px-4 sm:px-6 flex items-center justify-between h-14">
            <!-- Left: Brand / Cloudflare Style Logo & Project Switcher -->
            <div class="flex items-center gap-3">
                <button type="button" id="sidebarMobileToggle" class="lg:hidden text-slate-500 hover:text-slate-700 dark:text-slate-400 p-1 rounded-md">
                    <i class="bx bx-menu text-xl"></i>
                </button>
                <div class="flex items-center gap-2.5">
                    <!-- Cloud icon like Cloudflare orange flame/cloud -->
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#f38020] to-[#faae40] flex items-center justify-center text-white shadow-sm shadow-orange-500/20">
                        <i class="bx bxs-cloud text-xl"></i>
                    </div>
                    <span class="font-bold text-sm tracking-tight text-slate-900 dark:text-white hidden sm:inline">
                        {{ config('app.name', 'Laravel') }}
                    </span>
                    <button type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs">
                        <i class="bx bx-transfer-alt rotate-90"></i>
                    </button>
                </div>

                <div class="h-4 w-px bg-slate-200 dark:bg-slate-700 mx-1 hidden sm:block"></div>

                <!-- Domain Pill with star and free badge -->
                <div class="flex items-center gap-2">
                    <button type="button" class="text-slate-300 hover:text-amber-400 transition" title="Favorite">
                        <i class="bx bx-star text-base hover:text-amber-400"></i>
                    </button>
                    <span class="font-semibold text-xs text-slate-800 dark:text-slate-200 flex items-center gap-1.5 cursor-pointer hover:underline">
                        {{ request()->getHost() ?: 'web-analytics.local' }}
                    </span>
                    <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center gap-0.5">
                        free <i class="bx bx-chevron-down text-[10px] text-slate-400"></i>
                    </span>
                    @if(!empty($tenantId))
                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 flex items-center gap-1 shadow-sm" title="Scoped to Tenant">
                            <i class="bx bx-buildings text-xs"></i> Tenant: {{ $tenantId }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Right: Search, Ask AI, Support, Theme & User -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Ask AI Button (Cloudflare Style) -->
                <button type="button" class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                    <i class="bx bx-sparkles text-sm text-[#f6821f]"></i> Ask AI
                </button>

                <!-- Support Link -->
                <a href="https://github.com/nafiur/laravel-web-analytics" target="_blank" class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">
                    <i class="bx bx-help-circle text-sm"></i> Support
                </a>

                <!-- Theme Toggle -->
                <button type="button" id="themeToggle" 
                        class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition"
                        title="Toggle Light/Dark Theme">
                    <i id="themeIcon" class="bx bx-moon text-lg"></i>
                </button>

                <!-- User avatar -->
                <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-slate-700 to-slate-900 text-white text-xs font-semibold flex items-center justify-center ring-1 ring-slate-300 dark:ring-slate-700 cursor-pointer">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'Admin', 0, 1)) }}
                </div>
            </div>
        </div>
    </header>

    <!-- ============ MAIN CONTAINER (SIDEBAR + CONTENT) ============ -->
    <div class="flex-1 flex w-full">

        <!-- ============ LEFT SIDEBAR ============ -->
        <aside id="appSidebar" class="w-64 bg-white dark:bg-[#111827] border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between shrink-0 transition-transform duration-200 lg:translate-x-0 -translate-x-full fixed lg:static inset-y-14 left-0 z-30">
            <div class="flex-1 overflow-y-auto py-3 px-3 space-y-4">
                <!-- Search Box -->
                <div class="relative">
                    <i class="bx bx-search absolute left-2.5 top-2.5 text-slate-400 text-sm"></i>
                    <input type="text" id="sidebarSearch" placeholder="Quick search..." 
                           class="w-full text-xs pl-8 pr-12 py-1.5 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-md text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <span class="absolute right-2 top-2 text-[10px] text-slate-400 font-mono border border-slate-200 dark:border-slate-700 rounded px-1">Ctrl K</span>
                </div>

                <!-- Back to domains -->
                <div>
                    <a href="{{ route('lwa_dashboard') }}" class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <i class="bx bx-arrow-back text-sm text-slate-400"></i> Back to Domains
                    </a>
                </div>

                <!-- Navigation Groups -->
                <nav class="space-y-0.5 text-xs">
                    <!-- Overview -->
                    <a href="{{ route('lwa_dashboard') }}" class="flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-file text-sm text-slate-400"></i> Overview
                        </span>
                    </a>

                    <!-- Recents -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-time text-sm text-slate-400"></i> Recents
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- AI Crawl Control -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-bot text-sm text-slate-400"></i> AI Crawl Control
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Agent Readiness -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-check-shield text-sm text-slate-400"></i> Agent Readiness
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Analytics (ACTIVE Cloudflare highlight) -->
                    <a href="{{ route('lwa_dashboard') }}" class="flex items-center justify-between px-2.5 py-2 rounded-md font-semibold text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-800 transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-line-chart text-sm text-[#f6821f]"></i> Analytics
                        </span>
                        <i class="bx bx-chevron-right text-slate-500 text-xs"></i>
                    </a>

                    <!-- Observability New -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-pulse text-sm text-slate-400"></i> Observability
                            <span class="px-1.5 py-0.2 text-[9px] font-semibold rounded bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-800">New</span>
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- DNS -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-network-chart text-sm text-slate-400"></i> DNS
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Email -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-envelope text-sm text-slate-400"></i> Email
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- SSL / TLS -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-lock-alt text-sm text-slate-400"></i> SSL/TLS
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Security -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-shield-quarter text-sm text-slate-400"></i> Security
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Access -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-key text-sm text-slate-400"></i> Access
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Speed -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-bolt-circle text-sm text-slate-400"></i> Speed
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Caching -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-server text-sm text-slate-400"></i> Caching
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Workers Routes -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-code-alt text-sm text-slate-400"></i> Workers Routes
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Rules -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-git-branch text-sm text-slate-400"></i> Rules
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Traffic -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-transfer text-sm text-slate-400"></i> Traffic
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>

                    <!-- Web3 -->
                    <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="flex items-center gap-2.5">
                            <i class="bx bx-globe text-sm text-slate-400"></i> Web3
                        </span>
                        <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                    </button>
                </nav>
            </div>

            <!-- Sidebar Footer: Manage Account -->
            <div class="p-3 border-t border-slate-200 dark:border-slate-800">
                <button type="button" class="w-full flex items-center justify-between px-2.5 py-2 rounded-md font-medium text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white transition">
                    <span class="flex items-center gap-2">
                        <i class="bx bx-cog text-sm text-slate-400"></i> Manage account
                    </span>
                    <i class="bx bx-chevron-right text-slate-400 text-xs"></i>
                </button>
            </div>
        </aside>

        <!-- Mobile Backdrop -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 z-20 hidden lg:hidden"></div>

        <!-- ============ MAIN CONTENT AREA ============ -->
        <div class="flex-1 min-w-0 flex flex-col">

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

                        <!-- Export to CSV -->
                        <a href="{{ route('lwa_export', array_filter(['range' => $timeRange ?? '24h', 'tenant_id' => $tenantId ?? null])) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm transition"
                           title="Export filtered logs to CSV">
                            <i class="bx bx-download text-sm"></i> Export
                        </a>
                    </div>
                </div>
            </div>

            <!-- Page Body Content -->
            <main class="flex-1 p-4 sm:p-6 space-y-6">

                <!-- ============================================== -->
                <!-- TAB 1: REQUESTS (Exact Cloudflare Matching)    -->
                <!-- ============================================== -->
                <div id="tab-requests" class="tab-pane active space-y-5">
                    <!-- 5 KPI Cards Row (Total requests, Data transfer, Visits, Cache/Success rate, 5xx rate) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                        <!-- Total requests -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total requests</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpiRequests">{{ $analytics['summary']['total_requests_fmt'] ?? '0' }}</span>
                                    <span class="text-[11px] font-medium text-rose-500 dark:text-rose-400 flex items-center">
                                        <i class="bx bx-down-arrow-alt text-xs"></i> 30.9%
                                    </span>
                                </div>
                            </div>
                            <div id="sparklineRequests" class="mt-2 h-7 w-full"></div>
                        </div>

                        <!-- Total data transfer -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total data transfer</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpiBytes">{{ $analytics['summary']['total_bytes_fmt'] ?? '0 B' }}</span>
                                    <span class="text-[11px] font-medium text-rose-500 dark:text-rose-400 flex items-center">
                                        <i class="bx bx-down-arrow-alt text-xs"></i> 78.6%
                                    </span>
                                </div>
                            </div>
                            <div id="sparklineBytes" class="mt-2 h-7 w-full"></div>
                        </div>

                        <!-- Total visits -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Total visits</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpiVisits">{{ $analytics['summary']['unique_visitors_fmt'] ?? '0' }}</span>
                                    <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                                        <i class="bx bx-up-arrow-alt text-xs"></i> 170.4%
                                    </span>
                                </div>
                            </div>
                            <div id="sparklineVisits" class="mt-2 h-7 w-full"></div>
                        </div>

                        <!-- Cache hit / Success rate -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">Success rate (2xx)</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpiSuccessRate">{{ $analytics['summary']['success_rate'] ?? '100' }}%</span>
                                    <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                                        <i class="bx bx-up-arrow-alt text-xs"></i> 99.8%
                                    </span>
                                </div>
                            </div>
                            <div class="mt-2 h-7 flex items-center text-[10px] text-slate-400">
                                {{ $analytics['summary']['status_counts']['2xx'] ?? 0 }} successful requests
                            </div>
                        </div>

                        <!-- 5xx error rate -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-3.5 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="text-[11px] font-medium text-slate-500 dark:text-slate-400">5xx error rate</div>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-2xl font-bold text-slate-900 dark:text-white" id="kpi5xxRate">
                                        {{ number_format(($analytics['summary']['total_requests'] ?? 0) > 0 ? (($analytics['summary']['status_counts']['5xx'] ?? 0) / $analytics['summary']['total_requests']) * 100 : 0, 2) }}%
                                    </span>
                                    <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center">
                                        <i class="bx bx-down-arrow-alt text-xs"></i> 0.0%
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
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Requests over time</div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <span class="text-slate-400">Group by:</span>
                                    <select class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded px-1.5 py-0.5 text-slate-700 dark:text-slate-300 focus:outline-none">
                                        <option>None</option>
                                        <option>Status Code</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mb-2">
                                <span class="w-2 h-2 rounded-full bg-[#0051c3]"></span> Requests: <strong class="text-slate-800 dark:text-slate-200">{{ $analytics['summary']['total_requests_fmt'] ?? 0 }}</strong>
                            </div>
                            <div id="chartRequestsOverTime" class="h-64 w-full"></div>
                        </div>

                        <!-- Data transfer over time -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Data transfer</div>
                                <div class="flex items-center gap-2 text-[11px]">
                                    <span class="text-slate-400">Group by:</span>
                                    <select class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded px-1.5 py-0.5 text-slate-700 dark:text-slate-300 focus:outline-none">
                                        <option>None</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mb-2">
                                <span class="w-2 h-2 rounded-full bg-[#0051c3]"></span> Data transfer: <strong class="text-slate-800 dark:text-slate-200">{{ $analytics['summary']['total_bytes_fmt'] ?? '0 B' }}</strong>
                            </div>
                            <div id="chartDataTransfer" class="h-64 w-full"></div>
                        </div>
                    </div>

                    <!-- Charts Grid Row 2 (Requests by status code & Requests by method) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Requests by status code -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Requests by status code</div>
                                <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> 2xx: {{ $analytics['summary']['status_counts']['2xx'] ?? 0 }}</span>
                                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-cyan-500"></span> 3xx: {{ $analytics['summary']['status_counts']['3xx'] ?? 0 }}</span>
                                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> 4xx: {{ $analytics['summary']['status_counts']['4xx'] ?? 0 }}</span>
                                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500"></span> 5xx: {{ $analytics['summary']['status_counts']['5xx'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div id="chartStatusCodeBars" class="h-64 w-full"></div>
                        </div>

                        <!-- Requests by method -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Requests by HTTP method</div>
                                <div class="text-[11px] text-slate-400">GET, POST, PUT, DELETE breakdown</div>
                            </div>
                            <div id="chartMethodBars" class="h-64 w-full"></div>
                        </div>
                    </div>

                    <!-- Breakdown Lists Grid (2 Columns, Progress Bar Style) -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <!-- Top paths -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top paths</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths'] ?? []])
                        </div>

                        <!-- Top countries -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top countries</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? []])
                        </div>

                        <!-- Top client IPs -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top client IPs</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_ips'] ?? []])
                        </div>

                        <!-- Top operating systems -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top operating systems</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_os'] ?? []])
                        </div>

                        <!-- Top browsers -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top browsers</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_browsers'] ?? []])
                        </div>

                        <!-- Top referrers -->
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top referrers</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_referrers'] ?? []])
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 2: VISITORS                                -->
                <!-- ============================================== -->
                <div id="tab-visitors" class="tab-pane space-y-5">
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

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Countries</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? []])
                        </div>
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Client IPs</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_ips'] ?? []])
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Device Types</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_devices'] ?? []])
                        </div>
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Operating Systems</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_os'] ?? []])
                        </div>
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Browsers</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_browsers'] ?? []])
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 3: PERFORMANCE                             -->
                <!-- ============================================== -->
                <div id="tab-performance" class="tab-pane space-y-5">
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

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Origin response time over time</div>
                            <div id="chartLatency" class="h-64 w-full"></div>
                        </div>

                        <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top paths by data transfer</div>
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths_bandwidth'] ?? []])
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 4: SECURITY & ERRORS                       -->
                <!-- ============================================== -->
                <div id="tab-security" class="tab-pane space-y-5">
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

                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="p-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Top Error URLs (4xx / 5xx)</div>
                            <span class="text-[11px] text-slate-400">Sorted by failure count</span>
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
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-3.5 shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="live-dot"></span>
                            <div>
                                <div class="text-xs font-semibold text-slate-900 dark:text-white">Real-Time Traffic Feed</div>
                                <div class="text-[11px] text-slate-400">Streaming live requests via Server-Sent Events (SSE)</div>
                            </div>
                        </div>
                        <div class="text-right text-[11px] text-slate-400" id="lastUpdatedClock">Listening for requests...</div>
                    </div>

                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 border-b border-slate-200 dark:border-slate-700/60">
                                    <tr>
                                        <th class="py-2.5 px-4 font-semibold">Status</th>
                                        <th class="py-2.5 px-4 font-semibold">Method</th>
                                        <th class="py-2.5 px-4 font-semibold">User</th>
                                        <th class="py-2.5 px-4 font-semibold">Path</th>
                                        <th class="py-2.5 px-4 font-semibold">Client IP</th>
                                        <th class="py-2.5 px-4 font-semibold">Duration</th>
                                        <th class="py-2.5 px-4 font-semibold">Size</th>
                                        <th class="py-2.5 px-4 font-semibold">Browser / OS</th>
                                        <th class="py-2.5 px-4 font-semibold text-right">Time</th>
                                    </tr>
                                </thead>
                                <tbody id="tableLiveFeed" class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($analytics['recent_logs'] ?? [] as $log)
                                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40" id="log-row-{{ $log['id'] }}">
                                            <td class="py-2 px-4">
                                                <span class="px-2 py-0.5 rounded font-mono font-bold {{ $log['status_code'] >= 500 ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400' : ($log['status_code'] >= 400 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' : ($log['status_code'] >= 300 ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400')) }}">
                                                    {{ $log['status_code'] }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 font-mono font-semibold {{ $log['method'] === 'POST' ? 'text-sky-600' : 'text-emerald-600' }}">{{ $log['method'] }}</td>
                                            <td class="py-2 px-4">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] {{ $log['is_auth'] ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-400 font-medium' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                                    {{ $log['user_name'] }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 font-mono text-slate-700 dark:text-slate-200 truncate max-w-[220px]" title="{{ $log['path'] }}">{{ $log['path'] }}</td>
                                            <td class="py-2 px-4 font-mono text-slate-500">{{ $log['ip_address'] }}</td>
                                            <td class="py-2 px-4 font-mono text-slate-600 dark:text-slate-300">{{ $log['response_time_ms'] }} ms</td>
                                            <td class="py-2 px-4 text-slate-500">{{ $log['bytes_sent_fmt'] }}</td>
                                            <td class="py-2 px-4 text-slate-500 dark:text-slate-400 truncate max-w-[140px]">{{ $log['browser'] }} on {{ $log['os'] }}</td>
                                            <td class="py-2 px-4 text-right text-slate-400">{{ $log['time_ago'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-8 text-center text-slate-400">Waiting for live requests...</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- ============ SCRIPTS ============ -->
    <script>
        // 1. Dark Mode Toggle
        (function() {
            const toggle = document.getElementById('themeToggle');
            const icon = document.getElementById('themeIcon');
            const isDark = localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
                if (icon) icon.className = 'bx bx-sun text-lg';
            } else {
                document.documentElement.classList.remove('dark');
                if (icon) icon.className = 'bx bx-moon text-lg';
            }

            if (toggle) {
                toggle.addEventListener('click', () => {
                    const darkNow = document.documentElement.classList.toggle('dark');
                    localStorage.theme = darkNow ? 'dark' : 'light';
                    if (icon) icon.className = darkNow ? 'bx bx-sun text-lg' : 'bx bx-moon text-lg';
                    window.dispatchEvent(new Event('theme-changed'));
                });
            }
        })();

        // 2. Sidebar Mobile Drawer Toggle
        (function() {
            const btn = document.getElementById('sidebarMobileToggle');
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');

            if (btn && sidebar && backdrop) {
                btn.addEventListener('click', () => {
                    sidebar.classList.toggle('-translate-x-full');
                    backdrop.classList.toggle('hidden');
                });
                backdrop.addEventListener('click', () => {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                });
            }
        })();

        // 3. Tab Switching
        (function() {
            const tabs = document.querySelectorAll('.cf-tab-btn');
            const panes = document.querySelectorAll('.tab-pane');

            tabs.forEach(btn => {
                btn.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    panes.forEach(p => p.classList.remove('active'));

                    btn.classList.add('active');
                    const target = btn.getAttribute('data-target');
                    const targetEl = document.querySelector(target);
                    if (targetEl) targetEl.classList.add('active');

                    // Trigger resize for ApexCharts inside newly visible tab
                    window.dispatchEvent(new Event('resize'));
                });
            });
        })();

        // 4. Quick filter input on tables
        (function() {
            const input = document.getElementById('quickFilterInput');
            if (input) {
                input.addEventListener('keyup', function() {
                    let filter = this.value.toLowerCase();
                    let rows = document.querySelectorAll('#tableLiveFeed tr');
                    rows.forEach(r => {
                        let text = r.textContent.toLowerCase();
                        r.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }
        })();

        // ============ APEXCHARTS CONFIGURATION ============
        const timeLabels = {!! json_encode($analytics['time_series']['labels'] ?? ['No Data']) !!};
        const requestsData = {!! json_encode($analytics['time_series']['requests'] ?? [0]) !!};
        const bandwidthData = {!! json_encode($analytics['time_series']['bandwidth_mb'] ?? [0]) !!};
        const latencyData = {!! json_encode($analytics['time_series']['latency'] ?? [0]) !!};

        const status2xx = {!! json_encode($analytics['time_series']['status_2xx'] ?? [0]) !!};
        const status3xx = {!! json_encode($analytics['time_series']['status_3xx'] ?? [0]) !!};
        const status4xx = {!! json_encode($analytics['time_series']['status_4xx'] ?? [0]) !!};
        const status5xx = {!! json_encode($analytics['time_series']['status_5xx'] ?? [0]) !!};

        const methodBreakdown = {!! json_encode($analytics['breakdowns']['top_methods'] ?? []) !!};

        const isDarkMode = () => document.documentElement.classList.contains('dark');
        const getChartTheme = () => ({
            mode: isDarkMode() ? 'dark' : 'light',
            fontFamily: 'Plus Jakarta Sans, sans-serif'
        });

        // 1. Requests Over Time (Area Chart matching Cloudflare)
        let chartRequests = new ApexCharts(document.querySelector("#chartRequestsOverTime"), {
            series: [{ name: 'Requests', data: requestsData }],
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['#0051c3'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95, 100] }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'straight', width: 2 },
            xaxis: {
                categories: timeLabels,
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } }
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: {
                theme: isDarkMode() ? 'dark' : 'light',
                y: { formatter: (val) => Number(val).toLocaleString() + ' reqs' }
            }
        });
        chartRequests.render();

        // 2. Data Transfer (Area Chart matching Cloudflare)
        let chartBandwidth = new ApexCharts(document.querySelector("#chartDataTransfer"), {
            series: [{ name: 'Data Transfer (MB)', data: bandwidthData }],
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['#0051c3'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95, 100] }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'straight', width: 2 },
            xaxis: {
                categories: timeLabels,
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '10px', colors: '#94a3b8' },
                    formatter: (val) => val + ' MB'
                }
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: {
                theme: isDarkMode() ? 'dark' : 'light',
                y: { formatter: (val) => val + ' MB' }
            }
        });
        chartBandwidth.render();

        // 3. Status Code Stacked Column Chart
        let chartStatus = new ApexCharts(document.querySelector("#chartStatusCodeBars"), {
            series: [
                { name: '2xx Success', data: status2xx },
                { name: '3xx Redirect', data: status3xx },
                { name: '4xx Client Err', data: status4xx },
                { name: '5xx Server Err', data: status5xx }
            ],
            chart: {
                type: 'bar',
                height: 250,
                stacked: true,
                toolbar: { show: false }
            },
            colors: ['#10b981', '#06b6d4', '#f43f5e', '#a855f7'],
            plotOptions: {
                bar: { columnWidth: '45%', borderRadius: 2 }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: timeLabels,
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } },
                axisBorder: { show: false }
            },
            yaxis: {
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } }
            },
            legend: { show: false },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: { theme: isDarkMode() ? 'dark' : 'light' }
        });
        chartStatus.render();

        // 4. Method Breakdown Bar Chart
        let chartMethod = new ApexCharts(document.querySelector("#chartMethodBars"), {
            series: [{
                name: 'Requests',
                data: methodBreakdown.map(m => m.count)
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: { show: false }
            },
            colors: ['#3b82f6'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    barHeight: '40%'
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: methodBreakdown.map(m => m.name),
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } }
            },
            yaxis: {
                labels: { style: { fontSize: '11px', colors: '#94a3b8' } }
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: { theme: isDarkMode() ? 'dark' : 'light' }
        });
        chartMethod.render();

        // 5. Origin Latency Chart
        let chartLatencyEl = document.querySelector("#chartLatency");
        if (chartLatencyEl) {
            let chartLatency = new ApexCharts(chartLatencyEl, {
                series: [{ name: 'Latency (ms)', data: latencyData }],
                chart: { type: 'line', height: 250, toolbar: { show: false } },
                colors: ['#f59e0b'],
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { categories: timeLabels, labels: { style: { fontSize: '10px', colors: '#94a3b8' } } },
                yaxis: { labels: { formatter: (val) => val + ' ms', style: { fontSize: '10px', colors: '#94a3b8' } } },
                grid: { borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9', strokeDashArray: 3 },
                tooltip: { theme: isDarkMode() ? 'dark' : 'light' }
            });
            chartLatency.render();
        }

        // Mini Sparklines
        function renderSparkline(selector, data, color) {
            let el = document.querySelector(selector);
            if (!el) return;
            new ApexCharts(el, {
                series: [{ data: data && data.length ? data : [0] }],
                chart: { type: 'line', height: 28, sparkline: { enabled: true } },
                colors: [color],
                stroke: { curve: 'smooth', width: 1.5 },
                tooltip: { fixed: { enabled: false }, x: { show: false } }
            }).render();
        }

        renderSparkline('#sparklineRequests', requestsData, '#0051c3');
        renderSparkline('#sparklineBytes', bandwidthData, '#0051c3');
        renderSparkline('#sparklineVisits', requestsData, '#0051c3');

        // ============ REAL-TIME SSE STREAM ============
        (function() {
            let lastTrafficId = {{ \Nafiur\WebAnalytics\Models\LwaLogs::forTenant($tenantId ?? null)->max('id') ?? 0 }};
            let sseSource = null;
            let currentTenantId = "{{ $tenantId ?? '' }}";

            function startSSE() {
                if (!window.EventSource) return;
                if (sseSource) sseSource.close();

                let streamUrl = "{{ route('lwa_stream') }}?last_id=" + lastTrafficId;
                if (currentTenantId) {
                    streamUrl += "&tenant_id=" + encodeURIComponent(currentTenantId);
                }

                sseSource = new EventSource(streamUrl);

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

            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

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

                let cleanMethod = escapeHtml(log.method || 'GET');
                let cleanUser = escapeHtml(log.user_name || 'Guest');
                let cleanPath = escapeHtml(log.path || '/');
                let cleanIp = escapeHtml(log.ip_address || '-');
                let cleanBytes = escapeHtml(log.bytes_sent_fmt || '-');
                let cleanClient = escapeHtml((log.browser || 'Other') + ' on ' + (log.os || 'Other'));

                let tr = document.createElement('tr');
                tr.id = rowId;
                tr.className = 'bg-blue-50/50 dark:bg-blue-950/20 transition-all duration-1000';
                tr.innerHTML = `
                    <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded font-mono font-bold ${stBadge}">${st}</span></td>
                    <td class="py-2.5 px-4 font-mono font-semibold ${log.method === 'POST' ? 'text-sky-600' : 'text-emerald-600'}">${cleanMethod}</td>
                    <td class="py-2.5 px-4"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] ${log.is_auth ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-400 font-medium' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'}">${cleanUser}</span></td>
                    <td class="py-2.5 px-4 font-mono text-slate-700 dark:text-slate-200 truncate max-w-[220px]" title="${cleanPath}">${cleanPath}</td>
                    <td class="py-2.5 px-4 font-mono text-slate-500">${cleanIp}</td>
                    <td class="py-2.5 px-4 font-mono text-slate-600 dark:text-slate-300">${parseFloat(log.response_time_ms || 0)} ms</td>
                    <td class="py-2.5 px-4 text-slate-500">${cleanBytes}</td>
                    <td class="py-2.5 px-4 text-slate-500 dark:text-slate-400 truncate max-w-[140px]">${cleanClient}</td>
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
