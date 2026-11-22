        <!-- ============ LEFT SIDEBAR ============ -->
        <aside id="appSidebar" class="w-64 lwa-surface border-r lwa-border flex flex-col justify-between shrink-0 transition-transform duration-200 lg:translate-x-0 -translate-x-full fixed lg:static inset-y-14 left-0 z-30">
            <div class="flex-1 overflow-y-auto py-4 px-3">
                <nav class="space-y-1 text-xs">
                    @php($current = $section ?? 'overview')
                    <a href="{{ route('lwa_overview') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'overview' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="layout-dashboard" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="overview">Overview</span>
                    </a>

                    <a href="{{ route('lwa_realtime') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'realtime' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <span class="live-dot"></span> <span class="sidebar-label" data-i18n="realtime">Real-time</span>
                    </a>

                    <a href="{{ route('lwa_visitors') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'visitors' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="users" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="visitors">Visitors</span>
                    </a>

                    <a href="{{ route('lwa_sources') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'sources' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="share-2" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="trafficSources">Traffic Sources</span>
                    </a>

                    <a href="{{ route('lwa_pages') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'pages' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="file-text" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="pages">Pages &amp; Screens</span>
                    </a>

                    <a href="{{ route('lwa_geography') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'geography' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="globe-2" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="geography">Geography</span>
                    </a>

                    <a href="{{ route('lwa_devices') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'devices' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="monitor-smartphone" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="devices">Devices &amp; Browsers</span>
                    </a>

                    <a href="{{ route('lwa_campaigns') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'campaigns' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="megaphone" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="campaigns">Campaigns</span>
                    </a>

                    <a href="{{ route('lwa_events') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-400 dark:text-slate-600 transition {{ $current === 'events' ? 'active' : 'hover:bg-slate-100 dark:hover:bg-slate-800/80' }}"
                       title="Requires custom event tracking">
                        <i data-lucide="target" class="text-sm"></i> <span class="sidebar-label" data-i18n="events">Events &amp; Goals</span>
                    </a>

                    <a href="{{ route('lwa_conversions') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-400 dark:text-slate-600 transition {{ $current === 'conversions' ? 'active' : 'hover:bg-slate-100 dark:hover:bg-slate-800/80' }}"
                       title="Requires configured conversion goals">
                        <i data-lucide="badge-check" class="text-sm"></i> <span class="sidebar-label" data-i18n="conversions">Conversions</span>
                    </a>

                    <a href="{{ route('lwa_performance') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'performance' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="gauge" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="performance">Performance</span>
                    </a>

                    <a href="{{ route('lwa_errors') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'errors' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="shield-alert" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="errors">Error Monitoring</span>
                    </a>

                    <a href="{{ route('lwa_settings') }}"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium transition {{ $current === 'settings' ? 'active' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white' }}">
                        <i data-lucide="settings" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="settings">Settings</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Mobile Backdrop -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 z-20 hidden lg:hidden"></div>
