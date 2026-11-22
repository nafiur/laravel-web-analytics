    <!-- ============ TOP NAVBAR ============ -->
    <header class="lwa-surface border-b lwa-border sticky top-0 z-40">
        <div class="px-4 sm:px-6 flex items-center justify-between h-14">
            <!-- Left: Brand /  Style Logo & Project Switcher -->
            <div class="flex items-center gap-3">
                <button type="button" id="sidebarMobileToggle"
                    class="lg:hidden text-slate-500 hover:text-slate-700 dark:text-slate-400 p-1 rounded-md"
                    aria-label="Toggle analytics navigation" title="Analytics navigation">
                    <i data-lucide="activity" class="text-xl text-[#f6821f]"></i>
                </button>
                <div class="flex items-center gap-2.5">
                    <!-- Cloud icon-->
                    <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#f38020] to-[#faae40] flex items-center justify-center text-white shadow-sm shadow-orange-500/20">
                        <i data-lucide="cloud" class=" text-xl"></i>
                    </div>
                    <span class="font-bold text-sm tracking-tight text-slate-900 dark:text-white hidden sm:inline">
                        {{ config('app.name', 'Laravel Web Analytics') }}
                    </span>
                    <button type="button"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs">
                        <i data-lucide="repeat" class=" rotate-90"></i>
                    </button>
                </div>

                <div class="h-4 w-px bg-slate-200 dark:bg-slate-700 mx-1 hidden sm:block"></div>

                <!-- Domain Pill with star and free badge -->
                <div class="flex items-center gap-2">
                    <button type="button" class="text-slate-300 hover:text-amber-400 transition" title="Favorite">
                        <i data-lucide="star" class=" text-base hover:text-amber-400"></i>
                    </button>
                    <span
                        class="font-semibold text-xs text-slate-800 dark:text-slate-200 flex items-center gap-1.5 cursor-pointer hover:underline">
                        {{ request()->getHost() ?: 'web-analytics.local' }}
                    </span>
                    <span
                        class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 flex items-center gap-0.5">
                        free <i data-lucide="chevron-down" class=" text-[10px] text-slate-400"></i>
                    </span>
                    @if (!empty($tenantId))
                        <span
                            class="px-2 py-0.5 text-[10px] font-semibold rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 flex items-center gap-1 shadow-sm"
                            title="Scoped to Tenant">
                            <i data-lucide="building-2" class=" text-xs"></i> Tenant: {{ $tenantId }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Right: Search, Ask AI, Support, Theme & User -->
            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Ask AI Button ( Style) -->
                <button type="button"
                    class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                    <i data-lucide="sparkles" class=" text-sm text-[#f6821f]"></i> Ask AI
                </button>

                <!-- Support Link -->
                <a href="https://github.com/nafiur/laravel-web-analytics" target="_blank"
                    class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition">
                    <i data-lucide="circle-help" class=" text-sm"></i> Support
                </a>
            </div>
        </div>
    </header>
