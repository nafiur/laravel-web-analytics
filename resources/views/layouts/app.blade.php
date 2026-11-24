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

    <!-- Lucide icons & Google Fonts -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">

    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Vector world map for the offline GeoIP live view (map data is bundled, no tile API calls) -->
    <link rel="stylesheet" href="https://unpkg.com/jsvectormap@1.5.3/dist/css/jsvectormap.min.css">
    <script src="https://unpkg.com/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
    <script src="https://unpkg.com/jsvectormap@1.5.3/dist/maps/world.js"></script>

    <style>
        /* ---------- Design tokens ---------- */
        :root {
            color-scheme: light;
            --lwa-bg: #f6f8fc;
            --lwa-surface: #ffffff;
            --lwa-surface-muted: #f2f5fb;
            --lwa-text: #141c2f;
            --lwa-text-muted: #6b7a90;
            --lwa-border: #e6eaf2;
            --lwa-primary: #4f46e5;
            --lwa-primary-soft: #eef2ff;
            --lwa-accent: #0ea5e9;
            --lwa-success: #10b981;
            --lwa-warning: #f59e0b;
            --lwa-danger: #f43f5e;
            --lwa-radius: 16px;
            --lwa-radius-sm: 10px;
            --lwa-shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 8px 24px rgba(16, 24, 40, .05);
            --lwa-shadow-hover: 0 2px 4px rgba(16, 24, 40, .05), 0 16px 36px rgba(16, 24, 40, .09);
            --lwa-ring: 0 0 0 3px rgba(79, 70, 229, .12);
        }

        [data-theme="dark"] {
            color-scheme: dark;
            --lwa-bg: #0a0f1c;
            --lwa-surface: #111827;
            --lwa-surface-muted: #182236;
            --lwa-text: #eef2fb;
            --lwa-text-muted: #94a3b8;
            --lwa-border: #23304a;
            --lwa-primary: #818cf8;
            --lwa-primary-soft: #1e293b;
            --lwa-accent: #38bdf8;
            --lwa-success: #34d399;
            --lwa-warning: #fbbf24;
            --lwa-danger: #fb7185;
            --lwa-shadow: 0 1px 2px rgba(0, 0, 0, .3), 0 10px 30px rgba(0, 0, 0, .25);
            --lwa-shadow-hover: 0 4px 8px rgba(0, 0, 0, .35), 0 20px 44px rgba(0, 0, 0, .4);
            --lwa-ring: 0 0 0 3px rgba(129, 140, 248, .18);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--lwa-text);
            background-color: var(--lwa-bg);
            background-image:
                radial-gradient(1000px 420px at 8% -10%, rgba(79, 70, 229, .07), transparent 70%),
                radial-gradient(900px 380px at 95% -5%, rgba(14, 165, 233, .06), transparent 70%);
            background-attachment: fixed;
            -webkit-font-smoothing: antialiased;
        }

        [data-theme="dark"] body {
            background-color: var(--lwa-bg);
            background-image:
                radial-gradient(1000px 420px at 8% -10%, rgba(99, 102, 241, .12), transparent 70%),
                radial-gradient(900px 380px at 95% -5%, rgba(56, 189, 248, .08), transparent 70%);
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Token-backed surfaces — shared by every report card */
        .lwa-surface {
            background-color: var(--lwa-surface);
            border-color: var(--lwa-border);
            border-radius: var(--lwa-radius);
            box-shadow: var(--lwa-shadow);
            transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
        }

        .lwa-surface:hover {
            box-shadow: var(--lwa-shadow-hover);
        }

        .lwa-border {
            border-color: var(--lwa-border);
        }

        .lwa-muted-text {
            color: var(--lwa-text-muted);
        }

        /* Page headings */
        main h1, main h2 {
            letter-spacing: -.02em;
        }

        .lwa-page-title {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: -.025em;
            background: linear-gradient(100deg, var(--lwa-text), var(--lwa-primary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* KPI cards */
        .lwa-kpi {
            position: relative;
            overflow: hidden;
        }

        .lwa-kpi::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 3px;
            background: linear-gradient(180deg, var(--lwa-primary), var(--lwa-accent));
            opacity: .85;
        }

        .lwa-kpi > .text-xl,
        .lwa-kpi > .text-2xl {
            font-variant-numeric: tabular-nums;
            letter-spacing: -.03em;
            background: linear-gradient(120deg, var(--lwa-text), var(--lwa-primary));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Tables */
        .lwa-table thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: var(--lwa-surface-muted);
            color: var(--lwa-text-muted);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .lwa-table tbody tr {
            transition: background-color .15s ease;
        }

        .lwa-table tbody tr:hover {
            background: var(--lwa-primary-soft);
        }

        .lwa-table td {
            vertical-align: middle;
        }

        /* Pills / chips */
        .lwa-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .15rem .55rem;
            border-radius: 999px;
            background: var(--lwa-primary-soft);
            color: var(--lwa-primary);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        /* Primary actions */
        .lwa-btn-primary {
            background: linear-gradient(120deg, var(--lwa-primary), #6366f1);
            color: #fff;
            box-shadow: 0 6px 16px rgba(79, 70, 229, .25);
            transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
        }

        .lwa-btn-primary:hover {
            filter: brightness(1.06);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(79, 70, 229, .32);
        }

        [data-theme="dark"] .lwa-btn-primary {
            box-shadow: 0 6px 18px rgba(0, 0, 0, .4);
        }

        /* Modern focus ring */
        :where(a, button, input, select, textarea, [tabindex]):focus-visible {
            outline: none;
            box-shadow: var(--lwa-ring);
            border-radius: 8px;
        }

        /* Loading skeletons preserve the final layout */
        .lwa-skeleton {
            position: relative;
            overflow: hidden;
            background: var(--lwa-surface-muted);
            border-radius: 8px;
        }

        .lwa-skeleton::after {
            content: '';
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(90deg, transparent, rgba(148, 163, 184, .18), transparent);
            animation: lwa-shimmer 1.4s infinite;
        }

        @keyframes lwa-shimmer {
            100% {
                transform: translateX(100%);
            }
        }

        /* Lucide icons inherit current color and size */
        svg.lucide {
            width: 1em;
            height: 1em;
            stroke-width: 1.75;
            flex-shrink: 0;
        }

        /* Custom scrollbar for modern feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--lwa-border);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--lwa-text-muted);
        }

        /* Nav tabs — soft pill style */
        .cf-tab-btn {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--lwa-text-muted);
            padding: 7px 15px;
            border-radius: 999px;
            transition: all .18s ease;
            cursor: pointer;
            white-space: nowrap;
        }

        .cf-tab-btn:hover {
            color: var(--lwa-text);
            background-color: var(--lwa-surface-muted);
        }

        .cf-tab-btn.active {
            color: var(--lwa-primary);
            background-color: var(--lwa-surface);
            box-shadow: var(--lwa-shadow);
            font-weight: 700;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        /* Sidebar navigation */
        .sidebar-link {
            position: relative;
            border-radius: 10px;
            transition: background-color .18s ease, color .18s ease, transform .18s ease;
        }

        .sidebar-link:hover {
            background-color: var(--lwa-surface-muted);
            transform: translateX(2px);
        }

        .sidebar-link.active {
            color: var(--lwa-primary);
            background-color: var(--lwa-primary-soft);
            font-weight: 700;
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            bottom: 20%;
            width: 3px;
            border-radius: 999px;
            background: linear-gradient(180deg, var(--lwa-primary), var(--lwa-accent));
        }

        /* Collapsible desktop sidebar */
        @media (min-width: 1024px) {
            body.lwa-sidebar-collapsed #appSidebar { width: 4rem; }
            body.lwa-sidebar-collapsed #appSidebar .sidebar-label { display: none; }
            body.lwa-sidebar-collapsed #appSidebar .sidebar-link {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }
        }

        .settings-nav-link.active {
            color: var(--lwa-primary);
            background-color: var(--lwa-primary-soft);
            font-weight: 700;
            border-radius: 10px;
        }

        .lwa-compact .tab-pane .text-2xl {
            font-size: 1.15rem;
        }

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
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
            }
        }

        /* ApexCharts Overrides */
        .apexcharts-tooltip {
            border-radius: 8px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
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

        .apexcharts-xaxistooltip,
        .apexcharts-yaxistooltip {
            display: none !important;
        }

        /* Card menu: Proportion / Trend views and expand state */
        .lwa-breakdown-card [data-view="trend"] { display: none; }
        .lwa-breakdown-card[data-mode="trend"] [data-view="proportion"] { display: none; }
        .lwa-breakdown-card[data-mode="trend"] [data-view="trend"] { display: block; }
        .lwa-view-option [data-view-check] { visibility: hidden; }
        .lwa-breakdown-card[data-mode="proportion"] [data-view-mode="proportion"] [data-view-check],
        .lwa-breakdown-card[data-mode="trend"] [data-view-mode="trend"] [data-view-check] { visibility: visible; }
        .lwa-card-expanded { grid-column: 1 / -1; }
    </style>
</head>

<body class="lwa-bg text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-200">

    @include('web-analytics::partials.header')

    <!-- ============ MAIN CONTAINER (SIDEBAR + CONTENT) ============ -->
    <div class="flex-1 flex w-full">

        @include('web-analytics::partials.sidebar')

        <!-- ============ MAIN CONTENT AREA ============ -->
        <div class="flex-1 min-w-0 flex flex-col">

            @include('web-analytics::partials.subheader')

            <!-- Page Body Content -->
            <main class="flex-1 p-4 sm:p-6 space-y-6">

            @yield('content')

            </main>
        </div>
    </div>

    @include('web-analytics::partials.breakdown-detail-modal')
    @include('web-analytics::partials.scripts')
</body>

</html>