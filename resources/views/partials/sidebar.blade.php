        <!-- ============ LEFT SIDEBAR ============ -->
        <aside id="appSidebar" class="w-64 lwa-surface border-r lwa-border flex flex-col justify-between shrink-0 transition-transform duration-200 lg:translate-x-0 -translate-x-full fixed lg:static inset-y-14 left-0 z-30">
            <div class="flex-1 overflow-y-auto py-4 px-3">
                <nav class="space-y-1 text-xs">
                    <!-- Main -->
                    <a href="{{ route('lwa_dashboard') }}" id="sidebarOverview"
                       class="sidebar-link active flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="layout-dashboard" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="overview">Overview</span>
                    </a>

                    <a href="#tab-live" data-sidebar-tab="#tab-live"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="live-dot"></span> <span class="sidebar-label" data-i18n="realtime">Real-time</span>
                    </a>

                    <!-- Audience -->
                    <a href="#tab-visitors" data-sidebar-tab="#tab-visitors"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="users" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="visitors">Visitors</span>
                    </a>

                    <a href="#section-traffic-sources" data-sidebar-tab="#tab-requests" data-sidebar-scroll="#section-traffic-sources"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="share-2" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="trafficSources">Traffic Sources</span>
                    </a>

                    <a href="#section-top-pages" data-sidebar-tab="#tab-requests" data-sidebar-scroll="#section-top-pages"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="file-text" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="pages">Pages &amp; Screens</span>
                    </a>

                    <a href="#section-countries" data-sidebar-tab="#tab-visitors" data-sidebar-scroll="#section-countries"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="globe-2" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="geography">Geography</span>
                    </a>

                    <a href="#section-devices" data-sidebar-tab="#tab-visitors" data-sidebar-scroll="#section-devices"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="monitor-smartphone" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="devices">Devices &amp; Browsers</span>
                    </a>

                    <a href="#section-campaigns" data-sidebar-tab="#tab-visitors" data-sidebar-scroll="#section-campaigns"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="megaphone" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="campaigns">Campaigns</span>
                    </a>

                    <!-- Not available without an event collector -->
                    <span class="sidebar-unavailable flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-400 dark:text-slate-600 cursor-not-allowed"
                          title="Requires custom event tracking (not yet collected)" aria-disabled="true">
                        <i data-lucide="target" class="text-sm"></i> <span class="sidebar-label" data-i18n="events">Events &amp; Goals</span>
                    </span>

                    <span class="sidebar-unavailable flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-400 dark:text-slate-600 cursor-not-allowed"
                          title="Requires configured conversion goals" aria-disabled="true">
                        <i data-lucide="badge-check" class="text-sm"></i> <span class="sidebar-label" data-i18n="conversions">Conversions</span>
                    </span>

                    <!-- Operations -->
                    <a href="#tab-performance" data-sidebar-tab="#tab-performance"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="gauge" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="performance">Performance</span>
                    </a>

                    <a href="#tab-security" data-sidebar-tab="#tab-security"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="shield-alert" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="errors">Error Monitoring</span>
                    </a>

                    <!-- Administration -->
                    <a href="#tab-settings" data-sidebar-tab="#tab-settings"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i data-lucide="settings" class="text-sm text-slate-400"></i> <span class="sidebar-label" data-i18n="settings">Settings</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Mobile Backdrop -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 z-20 hidden lg:hidden"></div>
