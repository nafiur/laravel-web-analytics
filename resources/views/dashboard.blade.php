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

    <style>
        /* ---------- Design tokens ---------- */
        :root {
            color-scheme: light;
            --lwa-bg: #f7f8fc;
            --lwa-surface: #ffffff;
            --lwa-surface-muted: #f1f4f9;
            --lwa-text: #172033;
            --lwa-text-muted: #667085;
            --lwa-border: #e4e8f0;
            --lwa-primary: #4f46e5;
            --lwa-success: #15966b;
            --lwa-warning: #d97706;
            --lwa-danger: #dc5252;
            --lwa-radius: 14px;
        }

        [data-theme="dark"] {
            color-scheme: dark;
            --lwa-bg: #0b1120;
            --lwa-surface: #111a2b;
            --lwa-surface-muted: #182338;
            --lwa-text: #edf2fb;
            --lwa-text-muted: #9aa8bd;
            --lwa-border: #26344b;
            --lwa-primary: #818cf8;
            --lwa-success: #34d399;
            --lwa-warning: #fbbf24;
            --lwa-danger: #fb7185;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--lwa-bg);
            color: var(--lwa-text);
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Token-backed surfaces (replaces hardcoded Tailwind color pairs) */
        .lwa-surface {
            background-color: var(--lwa-surface);
            border-color: var(--lwa-border);
        }

        .lwa-border {
            border-color: var(--lwa-border);
        }

        .lwa-muted-text {
            color: var(--lwa-text-muted);
        }

        /* Accessible focus states */
        :where(a, button, input, select, textarea, [tabindex]):focus-visible {
            outline: 2px solid var(--lwa-primary);
            outline-offset: 2px;
            border-radius: 6px;
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
            background: #cbd5e1;
            border-radius: 4px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        /*  Nav Tabs */
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

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        /* -style sidebar links */
        .sidebar-link.active {
            color: #0f172a;
            background-color: #f1f5f9;
            font-weight: 600;
        }

        .dark .sidebar-link.active {
            color: #f8fafc;
            background-color: #1e293b;
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
            color: #0f172a;
            background-color: #f1f5f9;
            font-weight: 600;
        }

        .dark .settings-nav-link.active {
            color: #f8fafc;
            background-color: #1e293b;
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
