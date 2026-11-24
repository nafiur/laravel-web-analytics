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
            --lwa-bg: #f5f6f8;
            --lwa-surface: #ffffff;
            --lwa-surface-muted: #f7f8fa;
            --lwa-text: #1d1f2a;
            --lwa-text-muted: #737a8a;
            --lwa-border: #e4e6ec;
            --lwa-primary: #4f5bd5;
            --lwa-primary-soft: #eef0fc;
            --lwa-accent: #0f9bd7;
            --lwa-success: #12855f;
            --lwa-warning: #b7791f;
            --lwa-danger: #cf3b50;
            --lwa-radius: 8px;
            --lwa-radius-sm: 6px;
            --lwa-shadow: none;
            --lwa-shadow-hover: none;
            --lwa-ring: 0 0 0 2px rgba(79, 91, 213, .25);
        }

        [data-theme="dark"] {
            color-scheme: dark;
            --lwa-bg: #151619;
            --lwa-surface: #1c1e24;
            --lwa-surface-muted: #23262e;
            --lwa-text: #f3f4f7;
            --lwa-text-muted: #9aa1ae;
            --lwa-border: #31343d;
            --lwa-primary: #8b93f7;
            --lwa-primary-soft: #262a45;
            --lwa-accent: #4bb8e8;
            --lwa-success: #48c79b;
            --lwa-warning: #e6b357;
            --lwa-danger: #f07187;
            --lwa-shadow: none;
            --lwa-shadow-hover: none;
            --lwa-ring: 0 0 0 2px rgba(139, 147, 247, .35);
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--lwa-text);
            background-color: var(--lwa-bg);
            background-image: none;
            -webkit-font-smoothing: antialiased;
        }

        [data-theme="dark"] body {
            background-color: var(--lwa-bg);
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Flat, border-only surfaces — no shadow, no lift */
        .lwa-surface {
            background-color: var(--lwa-surface);
            border-color: var(--lwa-border);
            border-radius: var(--lwa-radius);
            box-shadow: none;
            transition: border-color .15s ease;
        }

        .lwa-surface:hover {
            border-color: var(--lwa-text-muted);
        }

        .lwa-border {
            border-color: var(--lwa-border);
        }

        .lwa-muted-text {
            color: var(--lwa-text-muted);
        }

        /* Page headings — plain, strong, no gradient */
        main h1, main h2 {
            letter-spacing: -.015em;
        }

        .lwa-page-title {
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: -.015em;
            color: var(--lwa-text);
        }

        /* KPI cards — dense, no accent bar, tabular values */
        .lwa-kpi {
            position: relative;
        }

        .lwa-kpi > .text-lg,
        .lwa-kpi > .text-xl,
        .lwa-kpi > .text-2xl {
            font-variant-numeric: tabular-nums;
            letter-spacing: -.02em;
            color: var(--lwa-text);
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

        /* Primary actions — flat, sharp */
        .lwa-btn-primary {
            background: var(--lwa-primary);
            color: #fff;
            box-shadow: none;
            transition: background-color .12s ease;
        }

        .lwa-btn-primary:hover {
            background: var(--lwa-primary);
            filter: brightness(.92);
        }

        /* Focus ring only */
        :where(a, button, input, select, textarea, [tabindex]):focus-visible {
            outline: 2px solid var(--lwa-primary);
            outline-offset: 1px;
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
            background-color: var(--lwa-primary-soft);
            box-shadow: none;
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
            width: 2px;
            border-radius: 2px;
            background: var(--lwa-primary);
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