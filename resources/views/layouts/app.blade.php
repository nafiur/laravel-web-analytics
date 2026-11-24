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
        /* =========================================================
           Laravel Web Analytics — design system
           Light: warm neutral. Dark: deep neutral.
           Flat surfaces, 1px borders, compact developer-tool density.
           ========================================================= */
        :root {
            color-scheme: light;
            --lwa-bg: #fafaf8;
            --lwa-surface: #ffffff;
            --lwa-surface-muted: #f4f4f1;
            --lwa-sidebar: #f3f3f0;
            --lwa-text: #1d1f24;
            --lwa-text-muted: #6f727b;
            --lwa-border: #e3e4e0;
            --lwa-border-strong: #d3d5d0;
            --lwa-primary: #4f5bd5;
            --lwa-primary-soft: #eceefe;
            --lwa-accent: #0d8fb8;
            --lwa-success: #12805d;
            --lwa-warning: #a86613;
            --lwa-danger: #c4364e;
            --lwa-radius: 6px;
            --lwa-radius-sm: 5px;
            --lwa-ring: 0 0 0 2px rgba(79, 91, 213, .28);
        }

        [data-theme="dark"] {
            color-scheme: dark;
            --lwa-bg: #141518;
            --lwa-surface: #1b1d22;
            --lwa-surface-muted: #23252b;
            --lwa-sidebar: #181a1e;
            --lwa-text: #f0f1f4;
            --lwa-text-muted: #9ea2ac;
            --lwa-border: #2f323a;
            --lwa-border-strong: #454952;
            --lwa-primary: #8b93f7;
            --lwa-primary-soft: #262a45;
            --lwa-accent: #4bb8e8;
            --lwa-success: #48c79b;
            --lwa-warning: #e6b357;
            --lwa-danger: #f07187;
            --lwa-ring: 0 0 0 2px rgba(139, 147, 247, .35);
        }

        /* ---------- Base ---------- */
        body {
            background-color: var(--lwa-bg);
            color: var(--lwa-text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            -webkit-font-smoothing: antialiased;
        }

        .font-mono { font-family: 'JetBrains Mono', monospace; font-variant-numeric: tabular-nums; }
        .lwa-muted-text { color: var(--lwa-text-muted); }
        .lwa-border { border-color: var(--lwa-border); }

        :where(a, button, input, select, textarea, [tabindex]):focus-visible {
            outline: 2px solid var(--lwa-primary);
            outline-offset: 1px;
        }

        /* ---------- Shell ---------- */
        .lwa-app-sidebar { background-color: var(--lwa-sidebar); }
        .lwa-app-subheader { background-color: var(--lwa-surface); border-bottom: 1px solid var(--lwa-border); }
        main { background-color: var(--lwa-bg); }

        .lwa-page-title {
            font-size: 1.35rem;
            font-weight: 750;
            letter-spacing: -.025em;
            line-height: 1.2;
            color: var(--lwa-text);
        }

        /* ---------- Surfaces ---------- */
        .lwa-surface {
            background: var(--lwa-surface);
            border: 1px solid var(--lwa-border);
            border-radius: var(--lwa-radius);
            box-shadow: none;
            transition: border-color .12s ease;
        }

        .lwa-surface:hover { border-color: var(--lwa-border-strong); }

        /* ---------- Controls ---------- */
        :where(input, select, textarea) {
            border: 1px solid var(--lwa-border);
            border-radius: var(--lwa-radius-sm);
            background: var(--lwa-surface);
            color: var(--lwa-text);
        }

        :where(input, select, textarea)::placeholder { color: var(--lwa-text-muted); }

        .lwa-btn-primary {
            background: var(--lwa-primary);
            color: #fff;
            border-radius: var(--lwa-radius-sm);
            font-weight: 650;
            box-shadow: none;
            transition: filter .12s ease;
        }

        .lwa-btn-primary:hover { filter: brightness(.92); }

        .lwa-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .1rem .5rem;
            border-radius: 999px;
            background: var(--lwa-primary-soft);
            color: var(--lwa-primary);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        /* ---------- KPI ---------- */
        .lwa-kpi {
            font-variant-numeric: tabular-nums;
        }

        .lwa-kpi > .text-lg,
        .lwa-kpi > .text-xl,
        .lwa-kpi > .text-2xl {
            color: var(--lwa-text);
            font-weight: 700;
            letter-spacing: -.02em;
        }

        /* ---------- Tables ---------- */
        .lwa-table { border-collapse: separate; border-spacing: 0; }
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
            border-bottom: 1px solid var(--lwa-border);
        }
        .lwa-table td { vertical-align: middle; }
        .lwa-table tbody tr { transition: background-color .1s ease; }
        .lwa-table tbody tr:hover { background: var(--lwa-primary-soft); }

        /* ---------- Navigation ---------- */
        .cf-tab-btn {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--lwa-text-muted);
            padding: 6px 13px;
            border-radius: var(--lwa-radius-sm);
            white-space: nowrap;
            transition: background-color .12s ease, color .12s ease;
        }
        .cf-tab-btn:hover { color: var(--lwa-text); background: var(--lwa-surface-muted); }
        .cf-tab-btn.active {
            color: var(--lwa-primary);
            background: var(--lwa-primary-soft);
            font-weight: 700;
        }

        .sidebar-link {
            position: relative;
            border-radius: var(--lwa-radius-sm);
            transition: background-color .12s ease, color .12s ease;
        }
        .sidebar-link:hover { background: var(--lwa-surface); }
        .sidebar-link.active {
            color: var(--lwa-primary);
            background: var(--lwa-primary-soft);
            font-weight: 700;
        }
        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 22%;
            bottom: 22%;
            width: 2px;
            background: var(--lwa-primary);
        }
        .settings-nav-link.active {
            color: var(--lwa-primary);
            background: var(--lwa-primary-soft);
            font-weight: 700;
            border-radius: var(--lwa-radius-sm);
        }

        @media (min-width: 1024px) {
            body.lwa-sidebar-collapsed #appSidebar { width: 4rem; }
            body.lwa-sidebar-collapsed #appSidebar .sidebar-label { display: none; }
            body.lwa-sidebar-collapsed #appSidebar .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; }
        }

        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .lwa-compact .tab-pane .text-2xl { font-size: 1.15rem; }

        /* ---------- Live indicator ---------- */
        .live-dot {
            width: 7px; height: 7px;
            background: var(--lwa-success);
            border-radius: 50%;
            display: inline-block;
            animation: lwa-pulse 1.8s infinite;
        }
        @keyframes lwa-pulse {
            0% { box-shadow: 0 0 0 0 rgba(18, 128, 93, .55); }
            70% { box-shadow: 0 0 0 6px rgba(18, 128, 93, 0); }
            100% { box-shadow: 0 0 0 0 rgba(18, 128, 93, 0); }
        }

        /* ---------- Skeleton ---------- */
        .lwa-skeleton {
            position: relative;
            overflow: hidden;
            background: var(--lwa-surface-muted);
            border-radius: var(--lwa-radius-sm);
        }
        .lwa-skeleton::after {
            content: '';
            position: absolute;
            inset: 0;
            transform: translateX(-100%);
            background: linear-gradient(90deg, transparent, rgba(148, 163, 184, .16), transparent);
            animation: lwa-shimmer 1.4s infinite;
        }
        @keyframes lwa-shimmer { 100% { transform: translateX(100%); } }

        /* ---------- Icons & scrollbar ---------- */
        svg.lucide { width: 1em; height: 1em; stroke-width: 1.75; flex-shrink: 0; }
        ::-webkit-scrollbar { width: 7px; height: 7px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--lwa-border-strong); border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--lwa-text-muted); }

        /* ---------- Charts ---------- */
        .apexcharts-tooltip { border-radius: var(--lwa-radius-sm) !important; border: 1px solid var(--lwa-border) !important; box-shadow: none !important; }
        .apexcharts-xaxistooltip, .apexcharts-yaxistooltip { display: none !important; }

        /* ---------- Card state toggles ---------- */
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