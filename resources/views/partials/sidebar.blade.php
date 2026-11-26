        <!-- ============ LEFT SIDEBAR ============ -->
        <aside id="appSidebar" class="w-64 lwa-app-sidebar border-r lwa-border flex flex-col shrink-0 transition-transform duration-200 lg:translate-x-0 -translate-x-full fixed lg:static inset-y-14 left-0 z-30">
            <div class="flex-1 overflow-y-auto py-3 px-3">
                <nav class="text-xs" aria-label="Analytics sections">
                    @php($current = $section ?? 'overview')

                    <a href="{{ route('lwa_overview') }}"
                       class="sidebar-link {{ $current === 'overview' ? 'active' : '' }}">
                        <i data-lucide="layout-dashboard" class="text-sm"></i> <span class="sidebar-label" data-i18n="overview">Overview</span>
                    </a>

                    <a href="{{ route('lwa_realtime') }}"
                       class="sidebar-link {{ $current === 'realtime' ? 'active' : '' }}">
                        <span class="live-dot"></span> <span class="sidebar-label" data-i18n="realtime">Real-time</span>
                    </a>

                    <a href="{{ route('lwa_logs') }}"
                       class="sidebar-link {{ $current === 'logs' ? 'active' : '' }}">
                        <i data-lucide="scroll-text" class="text-sm"></i> <span class="sidebar-label">Logs</span>
                    </a>

                    <a href="{{ route('lwa_dashboards') }}"
                       class="sidebar-link {{ $current === 'dashboards' || $current === 'dashboard-view' ? 'active' : '' }}">
                        <i data-lucide="layout-grid" class="text-sm"></i> <span class="sidebar-label">Dashboards</span>
                    </a>

                    <div class="lwa-nav-label">Audience</div>

                    <a href="{{ route('lwa_visitors') }}"
                       class="sidebar-link {{ $current === 'visitors' ? 'active' : '' }}">
                        <i data-lucide="users" class="text-sm"></i> <span class="sidebar-label" data-i18n="visitors">Visitors</span>
                    </a>

                    <a href="{{ route('lwa_sources') }}"
                       class="sidebar-link {{ $current === 'sources' ? 'active' : '' }}">
                        <i data-lucide="share-2" class="text-sm"></i> <span class="sidebar-label" data-i18n="trafficSources">Traffic Sources</span>
                    </a>

                    @if(config('laravel-web-analytics.page_reports.enabled'))
                        <a href="{{ route('lwa_page_reports') }}"
                           class="sidebar-link {{ $current === 'page-reports' ? 'active' : '' }}">
                            <i data-lucide="file-chart-column" class="text-sm"></i> <span class="sidebar-label">Page Reports</span>
                        </a>
                    @endif

                    <a href="{{ route('lwa_pages') }}"
                       class="sidebar-link {{ $current === 'pages' ? 'active' : '' }}">
                        <i data-lucide="file-text" class="text-sm"></i> <span class="sidebar-label" data-i18n="pages">Pages &amp; Screens</span>
                    </a>

                    <a href="{{ route('lwa_geography') }}"
                       class="sidebar-link {{ $current === 'geography' ? 'active' : '' }}">
                        <i data-lucide="globe-2" class="text-sm"></i> <span class="sidebar-label" data-i18n="geography">Geography</span>
                    </a>

                    <a href="{{ route('lwa_devices') }}"
                       class="sidebar-link {{ $current === 'devices' ? 'active' : '' }}">
                        <i data-lucide="monitor-smartphone" class="text-sm"></i> <span class="sidebar-label" data-i18n="devices">Devices &amp; Browsers</span>
                    </a>

                    <a href="{{ route('lwa_campaigns') }}"
                       class="sidebar-link {{ $current === 'campaigns' ? 'active' : '' }}">
                        <i data-lucide="megaphone" class="text-sm"></i> <span class="sidebar-label" data-i18n="campaigns">Campaigns</span>
                    </a>

                    <div class="lwa-nav-label">Insights</div>

                    <a href="{{ route('lwa_funnels') }}"
                       class="sidebar-link {{ $current === 'funnels' ? 'active' : '' }}">
                        <i data-lucide="filter" class="text-sm"></i> <span class="sidebar-label">Funnels</span>
                    </a>

                    <a href="{{ route('lwa_retention') }}"
                       class="sidebar-link {{ $current === 'retention' ? 'active' : '' }}">
                        <i data-lucide="users-round" class="text-sm"></i> <span class="sidebar-label">Retention</span>
                    </a>

                    <a href="{{ route('lwa_paths') }}"
                       class="sidebar-link {{ $current === 'paths' ? 'active' : '' }}">
                        <i data-lucide="route" class="text-sm"></i> <span class="sidebar-label">User Paths</span>
                    </a>

                    <a href="{{ route('lwa_trends') }}"
                       class="sidebar-link {{ $current === 'trends' ? 'active' : '' }}">
                        <i data-lucide="chart-no-axes-combined" class="text-sm"></i> <span class="sidebar-label">Trends</span>
                    </a>

                    <a href="{{ route('lwa_explorer') }}"
                       class="sidebar-link {{ $current === 'explorer' ? 'active' : '' }}">
                        <i data-lucide="scan-search" class="text-sm"></i> <span class="sidebar-label">Event Explorer</span>
                    </a>

                    @if(config('laravel-web-analytics.replay.enabled') && app(\Illuminate\Contracts\Auth\Access\Gate::class)->allows(config('laravel-web-analytics.replay.gate')))
                        <a href="{{ route('lwa_replay') }}"
                           class="sidebar-link {{ $current === 'replay' ? 'active' : '' }}">
                            <i data-lucide="circle-dot" class="text-sm"></i> <span class="sidebar-label">Session Replay</span>
                        </a>
                        <a href="{{ route('lwa_heatmaps') }}"
                           class="sidebar-link {{ $current === 'heatmaps' ? 'active' : '' }}">
                            <i data-lucide="grid-2x2" class="text-sm"></i> <span class="sidebar-label">Heatmaps</span>
                        </a>
                        <a href="{{ route('lwa_forms') }}"
                           class="sidebar-link {{ $current === 'forms' ? 'active' : '' }}">
                            <i data-lucide="text-cursor-input" class="text-sm"></i> <span class="sidebar-label">Forms</span>
                        </a>
                    @endif

                    @if(config('laravel-web-analytics.flags.enabled'))
                        <a href="{{ route('lwa_flags') }}"
                           class="sidebar-link {{ $current === 'flags' ? 'active' : '' }}">
                            <i data-lucide="toggle-right" class="text-sm"></i> <span class="sidebar-label">Feature Flags</span>
                        </a>
                        <a href="{{ route('lwa_experiments') }}"
                           class="sidebar-link {{ $current === 'experiments' ? 'active' : '' }}">
                            <i data-lucide="flask-conical" class="text-sm"></i> <span class="sidebar-label">Experiments</span>
                        </a>
                    @endif

                    <a href="{{ route('lwa_events') }}"
                       class="sidebar-link is-disabled {{ $current === 'events' ? 'active' : '' }}"
                       title="Requires custom event tracking">
                        <i data-lucide="target" class="text-sm"></i> <span class="sidebar-label" data-i18n="events">Events &amp; Goals</span>
                    </a>

                    <a href="{{ route('lwa_conversions') }}"
                       class="sidebar-link is-disabled {{ $current === 'conversions' ? 'active' : '' }}"
                       title="Requires configured conversion goals">
                        <i data-lucide="badge-check" class="text-sm"></i> <span class="sidebar-label" data-i18n="conversions">Conversions</span>
                    </a>

                    @if(config('laravel-web-analytics.web_vitals.enabled'))
                        <a href="{{ route('lwa_web_vitals') }}"
                           class="sidebar-link {{ $current === 'web-vitals' ? 'active' : '' }}">
                            <i data-lucide="gauge-circle" class="text-sm"></i> <span class="sidebar-label">Web Vitals</span>
                        </a>
                    @endif

                    <a href="{{ route('lwa_performance') }}"
                       class="sidebar-link {{ $current === 'performance' ? 'active' : '' }}">
                        <i data-lucide="gauge" class="text-sm"></i> <span class="sidebar-label" data-i18n="performance">Performance</span>
                    </a>

                    @if(config('laravel-web-analytics.observability.enabled') && app(\Illuminate\Contracts\Auth\Access\Gate::class)->allows(config('laravel-web-analytics.observability.gate')))
                        <a href="{{ route('lwa_observability') }}"
                           class="sidebar-link {{ $current === 'observability' ? 'active' : '' }}">
                            <i data-lucide="bug" class="text-sm"></i> <span class="sidebar-label">Observability</span>
                        </a>
                    @endif

                    <a href="{{ route('lwa_errors') }}"
                       class="sidebar-link {{ $current === 'errors' ? 'active' : '' }}">
                        <i data-lucide="shield-alert" class="text-sm"></i> <span class="sidebar-label" data-i18n="errors">Error Monitoring</span>
                    </a>
                </nav>
            </div>

            <div class="lwa-sidebar-footer space-y-1">
                @if(config('laravel-web-analytics.health.enabled'))
                    <a href="{{ route('lwa_health') }}"
                       class="sidebar-link {{ $current === 'health' ? 'active' : '' }}">
                        <i data-lucide="heart-pulse" class="text-sm"></i> <span class="sidebar-label">Installation Health</span>
                    </a>
                @endif
                <a href="{{ route('lwa_settings') }}"
                   class="sidebar-link {{ $current === 'settings' ? 'active' : '' }}">
                    <i data-lucide="settings" class="text-sm"></i> <span class="sidebar-label" data-i18n="settings">Settings</span>
                </a>
                <div class="flex items-center justify-between px-2 pt-1.5 text-[10px] text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: var(--lwa-success)"></span>
                        Self-hosted
                    </span>
                    <span>MIT</span>
                </div>
            </div>
        </aside>

        <!-- Mobile Backdrop -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 z-20 hidden lg:hidden"></div>
