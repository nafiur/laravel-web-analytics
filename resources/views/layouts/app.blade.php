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
           Laravel Web Analytics — interface
           Calm neutral surfaces, one accent, hairline borders,
           compact developer-tool density. Light + dark parity.
           ========================================================= */
        :root {
            color-scheme: light;
            --lwa-bg: #f7f7f8;
            --lwa-surface: #ffffff;
            --lwa-surface-muted: #f3f3f5;
            --lwa-surface-sunken: #fafafb;
            --lwa-sidebar: #fbfbfc;
            --lwa-text: #18191d;
            --lwa-text-body: #3c3f46;
            --lwa-text-muted: #6c7079;
            --lwa-text-faint: #9a9ea7;
            --lwa-border: #e6e6ea;
            --lwa-border-strong: #d5d6db;
            --lwa-primary: #5b5bd6;
            --lwa-primary-soft: #eeeefc;
            --lwa-solid: #5b5bd6;
            --lwa-solid-hover: #4b4bc4;
            --lwa-accent: #0e9bb5;
            --lwa-success: #12805d;
            --lwa-warning: #a86613;
            --lwa-danger: #c4364e;
            --lwa-radius: 10px;
            --lwa-radius-sm: 7px;
            --lwa-radius-xs: 5px;
            --lwa-shadow-card: 0 1px 2px rgba(20, 22, 30, .04);
            --lwa-shadow-pop: 0 16px 40px rgba(20, 22, 30, .13), 0 2px 6px rgba(20, 22, 30, .05);
            --lwa-ring: 0 0 0 3px rgba(91, 91, 214, .18);
            --lwa-chart-grid: #ededf1;
            --lwa-chart-label: #969aa3;
        }

        [data-theme="dark"] {
            color-scheme: dark;
            --lwa-bg: #0e0f11;
            --lwa-surface: #16171a;
            --lwa-surface-muted: #1e2024;
            --lwa-surface-sunken: #131416;
            --lwa-sidebar: #121316;
            --lwa-text: #eef0f3;
            --lwa-text-body: #cfd3da;
            --lwa-text-muted: #989da7;
            --lwa-text-faint: #6b7079;
            --lwa-border: #26282d;
            --lwa-border-strong: #3a3d44;
            --lwa-primary: #9d9df7;
            --lwa-primary-soft: #23233a;
            --lwa-solid: #6466e9;
            --lwa-solid-hover: #7476f5;
            --lwa-accent: #4bc0d8;
            --lwa-success: #4ecf9f;
            --lwa-warning: #e8b75e;
            --lwa-danger: #f0778c;
            --lwa-shadow-card: none;
            --lwa-shadow-pop: 0 18px 44px rgba(0, 0, 0, .55), 0 2px 8px rgba(0, 0, 0, .4);
            --lwa-ring: 0 0 0 3px rgba(157, 157, 247, .22);
            --lwa-chart-grid: #26282d;
            --lwa-chart-label: #8b909a;
        }

        /* ---------- Base ---------- */
        html { scrollbar-gutter: stable; }

        body {
            background-color: var(--lwa-bg);
            color: var(--lwa-text);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            font-size: 13px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        ::selection { background: var(--lwa-primary); color: #fff; }

        .font-mono {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-variant-numeric: tabular-nums;
        }

        .lwa-muted-text { color: var(--lwa-text-muted); }
        .lwa-faint-text { color: var(--lwa-text-faint); }
        .lwa-border { border-color: var(--lwa-border); }
        .lwa-hidden { display: none; }

        :where(a, button, input, select, textarea, [tabindex]):focus-visible {
            outline: 2px solid var(--lwa-primary);
            outline-offset: 1px;
        }

        /* ---------- Shell ---------- */
        .lwa-app-header {
            background: var(--lwa-surface);
            border-bottom: 1px solid var(--lwa-border);
        }

        @supports (backdrop-filter: blur(1px)) {
            .lwa-app-header {
                background: color-mix(in srgb, var(--lwa-surface) 86%, transparent);
                backdrop-filter: blur(12px) saturate(140%);
            }
            .lwa-app-subheader {
                background: color-mix(in srgb, var(--lwa-bg) 84%, transparent);
                backdrop-filter: blur(12px) saturate(140%);
            }
        }

        .lwa-app-sidebar { background: var(--lwa-sidebar); }
        .lwa-app-subheader { background: var(--lwa-bg); border-bottom: 1px solid var(--lwa-border); }
        main { background: var(--lwa-bg); }

        .lwa-page-title {
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -.03em;
            line-height: 1.15;
            color: var(--lwa-text);
        }

        .lwa-subtitle {
            font-size: 11.5px;
            line-height: 1.45;
            color: var(--lwa-text-muted);
        }

        .lwa-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--lwa-text-faint);
        }

        /* ---------- Surfaces ---------- */
        .lwa-card,
        .lwa-surface {
            background: var(--lwa-surface);
            border: 1px solid var(--lwa-border);
            border-radius: var(--lwa-radius);
            box-shadow: var(--lwa-shadow-card);
            transition: border-color .12s ease, box-shadow .12s ease;
        }

        .lwa-card:hover,
        .lwa-surface:hover { border-color: var(--lwa-border-strong); }

        .lwa-card-title {
            font-size: 12.5px;
            font-weight: 650;
            letter-spacing: -.012em;
            color: var(--lwa-text);
        }

        .lwa-icon-tile {
            width: 27px;
            height: 27px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--lwa-surface-muted);
            border: 1px solid var(--lwa-border);
            color: var(--lwa-text-muted);
            flex-shrink: 0;
        }

        .lwa-brand-mark {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--lwa-solid);
            color: #fff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .14);
            flex-shrink: 0;
        }

        .lwa-avatar {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--lwa-surface-muted);
            border: 1px solid var(--lwa-border);
            color: var(--lwa-text-body);
            font-size: 11px;
            font-weight: 650;
            letter-spacing: .02em;
            cursor: pointer;
            user-select: none;
        }

        /* ---------- Controls ---------- */
        .lwa-control,
        :where(main, .lwa-app-subheader, .lwa-app-header) :where(input:not([type="checkbox"]):not([type="radio"]):not([type="range"]), select, textarea) {
            background: var(--lwa-surface);
            border: 1px solid var(--lwa-border);
            border-radius: var(--lwa-radius-sm);
            color: var(--lwa-text-body);
            font-size: 12px;
            padding: 5px 9px;
            transition: border-color .12s ease, box-shadow .12s ease, background-color .12s ease;
        }

        .lwa-control:hover,
        :where(main, .lwa-app-subheader, .lwa-app-header) :where(input:not([type="checkbox"]):not([type="radio"]), select, textarea):hover {
            border-color: var(--lwa-border-strong);
        }

        .lwa-control:focus,
        :where(main, .lwa-app-subheader, .lwa-app-header) :where(input:not([type="checkbox"]):not([type="radio"]), select, textarea):focus {
            outline: none;
            border-color: var(--lwa-primary);
            box-shadow: var(--lwa-ring);
        }

        :where(input, textarea)::placeholder { color: var(--lwa-text-faint); }

        .lwa-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 5px 11px;
            border-radius: var(--lwa-radius-sm);
            border: 1px solid var(--lwa-border);
            background: var(--lwa-surface);
            color: var(--lwa-text-body);
            font-size: 12px;
            font-weight: 550;
            white-space: nowrap;
            transition: background-color .12s ease, border-color .12s ease, color .12s ease;
        }

        .lwa-btn:hover {
            background: var(--lwa-surface-muted);
            border-color: var(--lwa-border-strong);
            color: var(--lwa-text);
        }

        .lwa-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: var(--lwa-radius-sm);
            border: 1px solid transparent;
            background: var(--lwa-solid);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .12);
            transition: background-color .12s ease;
        }

        .lwa-btn-primary:hover { background: var(--lwa-solid-hover); filter: none; }

        .lwa-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: var(--lwa-radius-sm);
            border: 1px solid transparent;
            background: transparent;
            color: var(--lwa-text-muted);
            font-size: 12px;
            font-weight: 550;
            transition: background-color .12s ease, color .12s ease;
        }

        .lwa-btn-ghost:hover { background: var(--lwa-surface-muted); color: var(--lwa-text); }

        .lwa-input,
        .lwa-select {
            width: 100%;
            padding: 6px 9px;
            border-radius: var(--lwa-radius-sm);
            border: 1px solid var(--lwa-border);
            background: var(--lwa-surface);
            color: var(--lwa-text-body);
            font-size: 12px;
            line-height: 1.4;
            transition: border-color .12s ease, box-shadow .12s ease;
        }

        .lwa-input:focus,
        .lwa-select:focus {
            outline: none;
            border-color: var(--lwa-primary);
            box-shadow: 0 0 0 2px var(--lwa-primary-soft);
        }

        .lwa-field-label {
            display: block;
            margin-bottom: 5px;
            color: var(--lwa-text-muted);
            font-size: 10px;
            font-weight: 650;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        /* ---------- Chips & pills ---------- */
        .lwa-pill {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: 1px 8px;
            border-radius: 999px;
            background: var(--lwa-primary-soft);
            color: var(--lwa-primary);
            font-size: 10px;
            font-weight: 650;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .lwa-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 7px;
            border-radius: var(--lwa-radius-xs);
            border: 1px solid var(--lwa-border);
            background: var(--lwa-surface);
            color: var(--lwa-text-muted);
            font-size: 10.5px;
            font-weight: 600;
        }

        .lwa-divider { height: 1px; background: var(--lwa-border); border: 0; }

        /* ---------- KPI ---------- */
        .lwa-kpi {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px;
            font-variant-numeric: tabular-nums;
            position: relative;
            overflow: hidden;
        }

        .lwa-kpi-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .lwa-kpi-label {
            font-size: 11px;
            font-weight: 550;
            color: var(--lwa-text-muted);
            letter-spacing: -.005em;
        }

        .lwa-kpi-value {
            font-size: 1.5rem;
            line-height: 1.05;
            font-weight: 700;
            letter-spacing: -.035em;
            color: var(--lwa-text);
        }

        .lwa-kpi-value > .text-lg,
        .lwa-kpi-value > .text-xl,
        .lwa-kpi-value > .text-2xl {
            color: inherit;
            font-weight: inherit;
            letter-spacing: inherit;
        }

        /* ---------- Tables ---------- */
        .lwa-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 12px;
        }

        .lwa-table thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: var(--lwa-surface-sunken);
            color: var(--lwa-text-faint);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            white-space: nowrap;
            padding: 8px 12px;
            border-bottom: 1px solid var(--lwa-border);
        }

        .lwa-table tbody td { vertical-align: middle; }
        .lwa-table tbody tr { transition: background-color .1s ease; }
        .lwa-table tbody tr:hover { background: var(--lwa-surface-sunken); }
        .lwa-table tbody tr:last-child td { border-bottom: 0; }

        /* ---------- Navigation ---------- */
        .lwa-segmented {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 3px;
            background: var(--lwa-surface-muted);
            border: 1px solid var(--lwa-border);
            border-radius: var(--lwa-radius-sm);
            overflow-x: auto;
            scrollbar-width: none;
        }

        .lwa-segmented::-webkit-scrollbar { display: none; }

        .cf-tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 550;
            color: var(--lwa-text-muted);
            padding: 5px 12px;
            border-radius: var(--lwa-radius-xs);
            white-space: nowrap;
            transition: background-color .12s ease, color .12s ease, box-shadow .12s ease;
        }

        .cf-tab-btn:hover { color: var(--lwa-text); }

        .cf-tab-btn.active {
            color: var(--lwa-text);
            background: var(--lwa-surface);
            font-weight: 650;
            box-shadow: 0 1px 2px rgba(20, 22, 30, .08);
        }

        [data-theme="dark"] .cf-tab-btn.active { box-shadow: 0 1px 2px rgba(0, 0, 0, .45); }

        .lwa-nav-label {
            padding: 0 9px;
            margin: 16px 0 5px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--lwa-text-faint);
        }

        .lwa-nav-label:first-of-type { margin-top: 2px; }

        .sidebar-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 9px;
            border-radius: var(--lwa-radius-sm);
            font-size: 12.5px;
            font-weight: 500;
            color: var(--lwa-text-muted);
            transition: background-color .12s ease, color .12s ease;
        }

        .sidebar-link svg,
        .sidebar-link .live-dot { flex-shrink: 0; }
        .sidebar-link svg { opacity: .8; }

        .sidebar-link:hover {
            background: var(--lwa-surface-muted);
            color: var(--lwa-text);
        }

        .sidebar-link.active {
            background: var(--lwa-primary-soft);
            color: var(--lwa-primary);
            font-weight: 650;
        }

        .sidebar-link.active svg { opacity: 1; color: var(--lwa-primary); }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 26%;
            bottom: 26%;
            width: 2px;
            border-radius: 2px;
            background: var(--lwa-primary);
        }

        .sidebar-link.is-disabled { color: var(--lwa-text-faint); }
        .sidebar-link.is-disabled:hover { color: var(--lwa-text-muted); }

        .settings-nav-link.active {
            color: var(--lwa-primary);
            background: var(--lwa-primary-soft);
            font-weight: 650;
            border-radius: var(--lwa-radius-sm);
        }

        .lwa-sidebar-footer {
            border-top: 1px solid var(--lwa-border);
            padding: 10px 12px;
        }

        @media (min-width: 1024px) {
            body.lwa-sidebar-collapsed #appSidebar { width: 4rem; }
            body.lwa-sidebar-collapsed #appSidebar .sidebar-label,
            body.lwa-sidebar-collapsed #appSidebar .lwa-nav-label,
            body.lwa-sidebar-collapsed #appSidebar .lwa-sidebar-footer { display: none; }
            body.lwa-sidebar-collapsed #appSidebar .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; }
        }

        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .lwa-compact .tab-pane .text-2xl { font-size: 1.15rem; }

        /* ---------- Breakdown rows ---------- */
        .lwa-list-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 5px 6px;
            border-radius: var(--lwa-radius-xs);
            transition: background-color .1s ease;
        }

        .lwa-list-row + .lwa-list-row { box-shadow: inset 0 1px 0 var(--lwa-border); }
        .lwa-list-row:hover { background: var(--lwa-surface-muted); }

        .lwa-list-name {
            display: flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 11.5px;
            color: var(--lwa-text-body);
        }

        .lwa-list-metric {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            width: 132px;
        }

        @media (min-width: 640px) { .lwa-list-metric { width: 176px; } }

        .lwa-progress {
            flex: 1;
            height: 5px;
            border-radius: 999px;
            background: var(--lwa-surface-muted);
            overflow: hidden;
            box-shadow: inset 0 0 0 1px var(--lwa-border);
        }

        .lwa-progress > span {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: var(--lwa-primary);
            transition: width .45s ease;
        }

        .lwa-list-count {
            width: 52px;
            text-align: right;
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 11.5px;
            font-weight: 550;
            color: var(--lwa-text-muted);
        }

        /* ---------- Live indicator ---------- */
        .live-dot {
            width: 7px;
            height: 7px;
            background: var(--lwa-success);
            border-radius: 50%;
            display: inline-block;
            position: relative;
        }

        .live-dot::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: var(--lwa-success);
            opacity: .22;
            animation: lwa-pulse 1.9s ease-out infinite;
        }

        @keyframes lwa-pulse {
            0% { transform: scale(.7); opacity: .35; }
            70% { transform: scale(1.35); opacity: 0; }
            100% { transform: scale(1.35); opacity: 0; }
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
            background: linear-gradient(90deg, transparent, rgba(150, 154, 163, .14), transparent);
            animation: lwa-shimmer 1.5s infinite;
        }

        @keyframes lwa-shimmer { 100% { transform: translateX(100%); } }

        /* ---------- Icons & scrollbar ---------- */
        svg.lucide { width: 1em; height: 1em; stroke-width: 1.7; flex-shrink: 0; }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb {
            background: var(--lwa-border-strong);
            border-radius: 999px;
            border: 2px solid transparent;
            background-clip: content-box;
        }
        ::-webkit-scrollbar-thumb:hover { background: var(--lwa-text-faint); background-clip: content-box; }

        /* ---------- Charts ---------- */
        .apexcharts-tooltip {
            background: var(--lwa-surface) !important;
            border: 1px solid var(--lwa-border) !important;
            border-radius: var(--lwa-radius-sm) !important;
            box-shadow: var(--lwa-shadow-pop) !important;
            color: var(--lwa-text) !important;
        }
        .apexcharts-tooltip-title {
            background: var(--lwa-surface-muted) !important;
            border-bottom: 1px solid var(--lwa-border) !important;
            font-weight: 600 !important;
        }
        .apexcharts-xaxistooltip,
        .apexcharts-yaxistooltip { display: none !important; }
        .apexcharts-legend-text { color: var(--lwa-text-muted) !important; }

        /* ---------- Card state toggles ---------- */
        .lwa-breakdown-card [data-view="trend"] { display: none; }
        .lwa-breakdown-card[data-mode="trend"] [data-view="proportion"] { display: none; }
        .lwa-breakdown-card[data-mode="trend"] [data-view="trend"] { display: block; }
        .lwa-view-option [data-view-check] { visibility: hidden; }
        .lwa-breakdown-card[data-mode="proportion"] [data-view-mode="proportion"] [data-view-check],
        .lwa-breakdown-card[data-mode="trend"] [data-view-mode="trend"] [data-view-check] { visibility: visible; }
        .lwa-card-expanded { grid-column: 1 / -1; }

        .lwa-dashboard-widget.lwa-kpi { gap: 6px; }

        /* =========================================================
           Legacy utility bridge
           Older view markup still uses Tailwind palette classes.
           These rules keep every screen on the token palette
           without editing each template.
           ========================================================= */
        body .text-slate-900,
        body .text-slate-800,
        body .text-slate-700 { color: var(--lwa-text) !important; }
        body .text-slate-600,
        body .text-slate-500 { color: var(--lwa-text-muted) !important; }
        body .text-slate-400,
        body .text-slate-300 { color: var(--lwa-text-faint) !important; }

        html.dark body .text-white,
        html.dark body .text-slate-100,
        html.dark body .text-slate-200 { color: var(--lwa-text) !important; }
        html.dark body .text-slate-300,
        html.dark body .text-slate-400,
        html.dark body .text-slate-500 { color: var(--lwa-text-muted) !important; }

        body .text-blue-600,
        body .text-blue-500,
        body .text-indigo-600 { color: var(--lwa-primary) !important; }
        html.dark body .text-blue-400,
        html.dark body .text-blue-500,
        html.dark body .text-indigo-400 { color: var(--lwa-primary) !important; }

        body .bg-white { background-color: var(--lwa-surface) !important; }
        body .bg-slate-50,
        body .bg-slate-100 { background-color: var(--lwa-surface-muted) !important; }
        html.dark body .bg-slate-700,
        html.dark body .bg-slate-800,
        html.dark body .bg-slate-900 { background-color: var(--lwa-surface) !important; }
        html.dark body [class*="bg-slate-800/"],
        html.dark body [class*="bg-slate-900/"] { background-color: var(--lwa-surface) !important; }

        body .hover\:bg-slate-50:hover,
        body .hover\:bg-slate-100:hover { background-color: var(--lwa-surface-muted) !important; }
        html.dark body .hover\:bg-slate-700:hover,
        html.dark body .hover\:bg-slate-800:hover,
        html.dark body [class*="hover:bg-slate-800/"]:hover { background-color: var(--lwa-surface-muted) !important; }

        body .hover\:text-slate-900:hover,
        body .hover\:text-slate-700:hover,
        body .hover\:text-slate-600:hover { color: var(--lwa-text) !important; }
        body .hover\:text-blue-600:hover,
        body .hover\:text-blue-400:hover { color: var(--lwa-primary) !important; }

        body .border-slate-100,
        body .border-slate-200,
        body .border-slate-300 { border-color: var(--lwa-border) !important; }
        html.dark body .border-slate-700,
        html.dark body .border-slate-800,
        html.dark body [class*="border-slate-800"] { border-color: var(--lwa-border) !important; }

        body .divide-slate-100 > :not([hidden]) ~ :not([hidden]),
        html.dark body .divide-slate-800 > :not([hidden]) ~ :not([hidden]) { border-color: var(--lwa-border) !important; }

        body .shadow-sm { box-shadow: var(--lwa-shadow-card) !important; }
        body .shadow-lg,
        body .shadow-xl { box-shadow: var(--lwa-shadow-pop) !important; }
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