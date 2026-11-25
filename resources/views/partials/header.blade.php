    <!-- ============ TOP NAVBAR ============ -->
    <header class="lwa-app-header sticky top-0 z-40">
        <div class="px-4 sm:px-6 flex items-center justify-between h-14 gap-3">
            <!-- Left: brand, project switcher -->
            <div class="flex items-center gap-1.5 sm:gap-2.5 min-w-0">
                <button type="button" id="sidebarMobileToggle"
                    class="lg:hidden lwa-btn-ghost p-1.5"
                    aria-label="Toggle analytics navigation" title="Analytics navigation">
                    <i data-lucide="menu" class="text-lg"></i>
                </button>

                <!-- Desktop sidebar collapse -->
                <button type="button" id="sidebarCollapseToggle"
                    class="hidden lg:inline-flex lwa-btn-ghost p-1.5"
                    aria-label="Collapse sidebar" title="Collapse / expand sidebar">
                    <i data-lucide="panel-left-close" class="text-lg"></i>
                </button>

                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="lwa-brand-mark" aria-hidden="true">
                        <i data-lucide="activity" class="text-base"></i>
                    </div>
                    <span class="text-[13px] font-semibold tracking-tight text-slate-900 dark:text-white hidden sm:inline truncate max-w-[220px]">
                        {{ config('app.name', 'Laravel Web Analytics') }}
                    </span>
                </div>

                <span class="hidden sm:block h-4 w-px bg-slate-200 dark:bg-slate-700 mx-1"></span>

                <!-- Active property -->
                <div class="hidden md:flex items-center gap-2 min-w-0">
                    <button type="button" class="text-slate-300 hover:text-amber-400 transition" title="Favorite">
                        <i data-lucide="star" class="text-base"></i>
                    </button>
                    <span class="text-xs font-medium text-slate-800 dark:text-slate-200 truncate max-w-[240px]">
                        {{ request()->getHost() ?: 'web-analytics.local' }}
                    </span>
                    <span class="lwa-chip">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: var(--lwa-success)"></span>
                        Self-hosted
                    </span>
                    @if (!empty($tenantId))
                        <span class="lwa-chip" style="color: var(--lwa-primary); border-color: var(--lwa-primary-soft); background: var(--lwa-primary-soft)">
                            <i data-lucide="building-2" class="text-[11px]"></i> {{ $tenantId }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Right: property switch, support, theme, user -->
            <div class="flex items-center gap-1 sm:gap-1.5 shrink-0">
                @if(!empty($websites) && $websites->count() > 0)
                    <form method="POST" action="{{ route('lwa_websites_switch') }}" class="hidden sm:block">
                        @csrf
                        <select name="website" onchange="this.form.submit()" aria-label="Switch website"
                            class="lwa-control max-w-[180px]">
                            @foreach($websites as $website)
                                <option value="{{ $website->id }}" {{ ($activeWebsite?->id ?? $website->id) === $website->id ? 'selected' : '' }}>
                                    {{ $website->domain }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <a href="https://github.com/nafiur/laravel-web-analytics" target="_blank"
                    class="hidden sm:inline-flex lwa-btn-ghost">
                    <i data-lucide="circle-help" class="text-sm"></i> Support
                </a>

                <button type="button" id="themeToggle"
                    class="lwa-btn-ghost p-1.5"
                    aria-label="Toggle light/dark theme" title="Toggle Light/Dark Theme">
                    <span id="themeIconWrapper"><i id="themeIcon" data-lucide="moon" class="text-lg"></i></span>
                </button>

                <div class="lwa-avatar" title="{{ auth()->user()?->name ?? 'Admin' }}">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'Admin', 0, 1)) }}
                </div>
            </div>
        </div>
    </header>
