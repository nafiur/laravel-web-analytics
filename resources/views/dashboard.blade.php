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

        /* Cloudflare-style sidebar links */
        .sidebar-link.active {
            color: #0f172a;
            background-color: #f1f5f9;
            font-weight: 600;
        }
        .dark .sidebar-link.active {
            color: #f8fafc;
            background-color: #1e293b;
        }
        .settings-nav-link.active {
            color: #0f172a;
            background-color: #f1f5f9;
            font-weight: 600;
        }
        .dark .settings-nav-link.active {
            color: #f8fafc;
            background-color: #1e293b;
        }
        .lwa-compact .tab-pane .text-2xl { font-size: 1.15rem; }

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

    @include('web-analytics::partials.header')

    <!-- ============ MAIN CONTAINER (SIDEBAR + CONTENT) ============ -->
    <div class="flex-1 flex w-full">

        @include('web-analytics::partials.sidebar')

        <!-- ============ MAIN CONTENT AREA ============ -->
        <div class="flex-1 min-w-0 flex flex-col">

            @include('web-analytics::partials.subheader')

            <!-- Page Body Content -->
            <main class="flex-1 p-4 sm:p-6 space-y-6">

                @include('web-analytics::sections.requests')
                @include('web-analytics::sections.visitors')
                @include('web-analytics::sections.performance')
                @include('web-analytics::sections.security')
                @include('web-analytics::sections.live')
                @include('web-analytics::sections.settings')

            </main>
        </div>
    </div>

    @include('web-analytics::partials.scripts')
</body>
</html>
