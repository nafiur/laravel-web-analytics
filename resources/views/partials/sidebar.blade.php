        <!-- ============ LEFT SIDEBAR ============ -->
        <aside id="appSidebar" class="w-64 bg-white dark:bg-[#111827] border-r border-slate-200 dark:border-slate-800 flex flex-col justify-between shrink-0 transition-transform duration-200 lg:translate-x-0 -translate-x-full fixed lg:static inset-y-14 left-0 z-30">
            <div class="flex-1 overflow-y-auto py-4 px-3">
                <nav class="space-y-1 text-xs">
                    <!-- Overview -->
                    <a href="{{ route('lwa_dashboard') }}" id="sidebarOverview"
                       class="sidebar-link active flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i class="bx bx-grid-alt text-sm text-slate-400"></i> <span data-i18n="overview">Overview</span>
                    </a>

                    <!-- Requests -->
                    <a href="#tab-requests" data-sidebar-tab="#tab-requests"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i class="bx bx-file text-sm text-slate-400"></i> <span data-i18n="requests">Requests</span>
                    </a>

                    <!-- Visitors -->
                    <a href="#tab-visitors" data-sidebar-tab="#tab-visitors"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i class="bx bx-group text-sm text-slate-400"></i> <span data-i18n="visitors">Visitors</span>
                    </a>

                    <!-- Performance -->
                    <a href="#tab-performance" data-sidebar-tab="#tab-performance"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i class="bx bx-line-chart text-sm text-slate-400"></i> <span data-i18n="performance">Performance</span>
                    </a>

                    <!-- Live Stream -->
                    <a href="#tab-live" data-sidebar-tab="#tab-live"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <span class="live-dot"></span> <span data-i18n="liveStream">Live Stream</span>
                    </a>

                    <!-- Setting -->
                    <a href="#tab-settings" data-sidebar-tab="#tab-settings"
                       class="sidebar-link flex items-center gap-2.5 px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 hover:text-slate-900 dark:hover:text-white transition">
                        <i class="bx bx-cog text-sm text-slate-400"></i> <span data-i18n="settings">Setting</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Mobile Backdrop -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 z-20 hidden lg:hidden"></div>
