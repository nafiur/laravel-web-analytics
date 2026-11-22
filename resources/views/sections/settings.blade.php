                <!-- ============================================== -->
                <!-- TAB 6: SETTINGS                                -->
                <!-- ============================================== -->
                <div id="tab-settings" class="tab-pane space-y-5">
                    <div class="grid grid-cols-1 lg:grid-cols-[210px_minmax(0,1fr)] gap-6 items-start">

                        <!-- Settings sub-navigation -->
                        <nav class="space-y-1 text-xs lg:sticky lg:top-32">
                            <div
                                class="px-2.5 pt-1 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                Preferences</div>
                            <a href="#settings-general"
                                class="settings-nav-link active block px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">General</a>
                            <a href="#settings-dashboard"
                                class="settings-nav-link block px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Dashboard</a>
                            <a href="#settings-collection"
                                class="settings-nav-link block px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Collection</a>
                            <a href="#settings-alerts"
                                class="settings-nav-link block px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Alerts</a>
                            <a href="#settings-export"
                                class="settings-nav-link block px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Export</a>

                            <div
                                class="px-2.5 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                Data</div>
                            <a href="#settings-maintenance"
                                class="settings-nav-link block px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">Maintenance</a>

                            <div
                                class="px-2.5 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                System</div>
                            <a href="#settings-about"
                                class="settings-nav-link block px-2.5 py-2 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">About</a>
                        </nav>

                        <div class="space-y-6 min-w-0">

                            @if (session('lwa_status'))
                                <div
                                    class="px-4 py-3 rounded-md text-xs bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-900">
                                    {{ session('lwa_status') }}
                                </div>
                            @endif
                            @if ($errors->any())
                                <div
                                    class="px-4 py-3 rounded-md text-xs bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <!-- General -->
                            <section id="settings-general"
                                class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                                <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white"
                                        data-i18n="settings">Settings</h2>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Analytics preferences for this browser
                                    </p>
                                </div>
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-600 dark:text-slate-300"
                                            data-i18n="language">Language</span>
                                        <select id="settingLanguage"
                                            class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="en">🇺🇸 English</option>
                                            <option value="bn">🇧🇩 বাংলা</option>
                                            <option value="hi">🇮🇳 हिन्दी</option>
                                            <option value="es">🇪🇸 Español</option>
                                            <option value="fr">🇫🇷 Français</option>
                                            <option value="de">🇩🇪 Deutsch</option>
                                            <option value="pt">🇵🇹 Português</option>
                                            <option value="ar">🇸🇦 العربية</option>
                                            <option value="zh">🇨🇳 中文</option>
                                            <option value="ja">🇯🇵 日本語</option>
                                            <option value="ur">🇵🇰 اردو</option>
                                            <option value="id">🇮🇩 Indonesian</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-600 dark:text-slate-300"
                                            data-i18n="appearance">Appearance</span>
                                        <select id="settingAppearance"
                                            class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            <option value="system">System</option>
                                            <option value="light">Light</option>
                                            <option value="dark">Dark</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-600 dark:text-slate-300"
                                            data-i18n="timezone">Timezone</span>
                                        <select id="settingTimezone"
                                            class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                            @php
                                                $groupedTimezones = collect($timezones)->groupBy(function ($tz) {
                                                    return str_contains($tz, '/') ? explode('/', $tz, 2)[0] : 'UTC';
                                                });
                                            @endphp
                                            @foreach($groupedTimezones as $region => $zones)
                                                <optgroup label="{{ $region }}">
                                                    @foreach($zones as $tz)
                                                        <option value="{{ $tz }}">
                                                            {{ $region === 'UTC' ? $tz : str_replace('_', ' ', str_replace($region . '/', '', $tz)) }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-600 dark:text-slate-300">Current time</span>
                                        <span id="settingsClock" data-utc="{{ now()->toIso8601String() }}"
                                            class="font-mono text-slate-500 dark:text-slate-400">{{ now()->format('Y-m-d H:i:s') }}</span>
                                    </div>
                                </div>
                            </section>

                            <!-- Dashboard preferences -->
                            <section id="settings-dashboard"
                                class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                                <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white"
                                        data-i18n="dashboard">Dashboard</h2>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Defaults applied to every visit</p>
                                </div>
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-600 dark:text-slate-300"
                                            data-i18n="defaultRange">Default time range</span>
                                        <select id="settingDefaultRange"
                                            class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                            @foreach (['1h' => 'Last 1 hour', '6h' => 'Last 6 hours', '24h' => 'Last 24 hours', 'today' => 'Today', '7d' => 'Last 7 days', '30d' => 'Last 30 days', 'all' => 'All time'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ ($timeRange ?? '24h') === $value ? 'selected' : '' }}>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-600 dark:text-slate-300">Data source</span>
                                        <select onchange="switchDataSource(this.value)"
                                            class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                            <option value="database"
                                                {{ ($source ?? 'database') === 'database' ? 'selected' : '' }}>Database
                                                table</option>
                                            <option value="file"
                                                {{ ($source ?? 'database') === 'file' ? 'selected' : '' }}>Server log
                                                file</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-600 dark:text-slate-300">Compact numbers</span>
                                        <input type="checkbox" id="settingCompact"
                                            class="w-8 h-4 accent-[#0051c3] cursor-pointer">
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-600 dark:text-slate-300">Keyboard shortcuts</span>
                                        <div class="flex items-center gap-3">
                                            <button type="button"
                                                onclick="document.getElementById('shortcutsHelp').classList.remove('hidden')"
                                                class="text-[11px] text-blue-600 dark:text-blue-400 hover:underline">Show
                                                shortcuts</button>
                                            <input type="checkbox" id="settingShortcuts"
                                                class="w-8 h-4 accent-[#0051c3] cursor-pointer">
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Collection (read-only) -->
                            <section id="settings-collection"
                                class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                                <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white"
                                        data-i18n="collection">Collection</h2>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Defined in config — read-only</p>
                                </div>
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @php
                                        $collectionRows = [
                                            'Tracking' => config('laravel-web-analytics.enabled', true)
                                                ? 'Enabled'
                                                : 'Disabled',
                                            'Capture web' => config('laravel-web-analytics.capture.web', true)
                                                ? 'Enabled'
                                                : 'Disabled',
                                            'Capture API / AJAX' => config('laravel-web-analytics.capture.api', true)
                                                ? 'Enabled'
                                                : 'Disabled',
                                            'Capture bots' => config('laravel-web-analytics.capture.bots', false)
                                                ? 'Enabled'
                                                : 'Disabled',
                                            'Ignored paths' => count(config('laravel-web-analytics.ignored_paths', [])),
                                            'GeoIP' => config('laravel-web-analytics.geoip.enabled', true)
                                                ? config('laravel-web-analytics.geoip.provider', '')
                                                : 'Disabled',
                                            'Active visitor window' =>
                                                config('laravel-web-analytics.active_visitor_window', 5) . ' minutes',
                                            'Stream interval' =>
                                                config('laravel-web-analytics.stream_interval', 2) . ' seconds',
                                            'Multi-tenancy' => config(
                                                'laravel-web-analytics.multi_tenancy.enabled',
                                                false,
                                            )
                                                ? 'Enabled'
                                                : 'Disabled',
                                        ];
                                    @endphp
                                    @foreach ($collectionRows as $label => $value)
                                        <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                                            <span class="text-slate-500 dark:text-slate-400">{{ $label }}</span>
                                            <span
                                                class="font-mono text-slate-800 dark:text-slate-200">{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            <!-- Alerts -->
                            <section id="settings-alerts"
                                class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                                <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Alerts</h2>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Webhook alerts for 5xx errors and
                                        error-rate spikes</p>
                                </div>
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @foreach ([
        'Status' => config('laravel-web-analytics.alerts.enabled', false) ? 'Enabled' : 'Disabled',
        'Channel' => ucfirst(config('laravel-web-analytics.alerts.channel', 'telegram')),
        'Error threshold' => config('laravel-web-analytics.alerts.error_rate_threshold_percentage', 10) . '%',
        'Webhook configured' => config('laravel-web-analytics.alerts.webhook_url') ? 'Yes' : 'No',
        'Telegram credentials' => config('laravel-web-analytics.alerts.telegram_bot_token') ? 'Yes' : 'No',
    ] as $label => $value)
                                        <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                                            <span
                                                class="text-slate-500 dark:text-slate-400">{{ $label }}</span>
                                            <span
                                                class="font-mono text-slate-800 dark:text-slate-200">{{ $value }}</span>
                                        </div>
                                    @endforeach
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-500 dark:text-slate-400">Test notification</span>
                                        <form method="POST" action="{{ route('lwa_settings_test_alert') }}">
                                            @csrf
                                            <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md">Send
                                                test alert</button>
                                        </form>
                                    </div>
                                </div>
                            </section>

                            <!-- Export defaults -->
                            <section id="settings-export"
                                class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                                <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Export defaults
                                    </h2>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Applied to every CSV export link</p>
                                </div>
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-500 dark:text-slate-400">Default export range</span>
                                        <select id="settingExportRange"
                                            class="text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                            @foreach (['1h' => 'Last 1 hour', '6h' => 'Last 6 hours', '24h' => 'Last 24 hours', 'today' => 'Today', '7d' => 'Last 7 days', '30d' => 'Last 30 days', 'all' => 'All time'] as $value => $label)
                                                <option value="{{ $value }}"
                                                    {{ ($timeRange ?? '24h') === $value ? 'selected' : '' }}>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                                        <span class="text-slate-500 dark:text-slate-400">Include tenant in
                                            export</span>
                                        <input type="checkbox" id="settingExportTenant"
                                            class="w-8 h-4 accent-[#0051c3] cursor-pointer">
                                    </div>
                                </div>
                            </section>

                            <!-- Data maintenance -->
                            <section id="settings-maintenance"
                                class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                                <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white"
                                        data-i18n="maintenance">Data maintenance</h2>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $logPath ?? 'Database table' }} —
                                        scoped to {{ $tenantId ? 'tenant ' . $tenantId : 'all tenants' }}</p>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-4 text-xs">
                                    <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-800/60">
                                        <div class="text-[10px] text-slate-400">Rows</div>
                                        <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5">
                                            {{ number_format($tableStats['rows'] ?? 0) }}</div>
                                    </div>
                                    <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-800/60">
                                        <div class="text-[10px] text-slate-400">Oldest record</div>
                                        <div class="text-[11px] font-mono text-slate-700 dark:text-slate-200 mt-1">
                                            {{ $tableStats['oldest'] ?? '—' }}</div>
                                    </div>
                                    <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-800/60">
                                        <div class="text-[10px] text-slate-400">Newest record</div>
                                        <div class="text-[11px] font-mono text-slate-700 dark:text-slate-200 mt-1">
                                            {{ $tableStats['newest'] ?? '—' }}</div>
                                    </div>
                                    <div class="p-3 rounded-md bg-slate-50 dark:bg-slate-800/60">
                                        <div class="text-[10px] text-slate-400">Retention</div>
                                        <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5">
                                            {{ config('laravel-web-analytics.retention_days', 30) }}d</div>
                                    </div>
                                </div>

                                @if (config('laravel-web-analytics.settings.destructive_actions_enabled', true))
                                    <div
                                        class="border-t border-slate-100 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800">
                                        <form method="POST" action="{{ route('lwa_settings_prune') }}"
                                            class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                            @csrf
                                            <div>
                                                <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                                    Prune old records</div>
                                                <div class="text-[11px] text-slate-400 mt-0.5">Deletes telemetry older
                                                    than the chosen number of days.</div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="number" name="days" min="1" max="3650"
                                                    value="{{ config('laravel-web-analytics.retention_days', 30) }}"
                                                    class="w-20 text-xs px-2 py-1.5 border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                                <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-950/70 border border-amber-200 dark:border-amber-900 rounded-md">Prune</button>
                                            </div>
                                        </form>

                                        <form method="POST" action="{{ route('lwa_settings_empty') }}"
                                            onsubmit="return confirm('Delete all telemetry records? This cannot be undone.')"
                                            class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                            @csrf
                                            <div>
                                                <div class="text-xs font-semibold text-rose-600 dark:text-rose-400">
                                                    Empty table</div>
                                                <div class="text-[11px] text-slate-400 mt-0.5">Permanently removes all
                                                    telemetry rows.</div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input type="text" name="confirm" placeholder="Type DELETE"
                                                    autocomplete="off"
                                                    class="w-28 text-xs px-2 py-1.5 border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                                <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium text-white bg-rose-600 hover:bg-rose-700 rounded-md">Empty
                                                    table</button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <div
                                        class="px-4 py-3 text-xs text-slate-400 border-t border-slate-100 dark:border-slate-800">
                                        Destructive actions are disabled by configuration.</div>
                                @endif
                            </section>

                            <!-- About -->
                            <section id="settings-about"
                                class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                                <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                                    <h2 class="text-sm font-semibold text-slate-900 dark:text-white">About</h2>
                                </div>
                                <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @foreach ([
        'Package' => 'nafiur/laravel-web-analytics',
        'Laravel' => app()->version(),
        'PHP' => PHP_VERSION,
        'Storage' => config('laravel-web-analytics.table_name', 'lwa_logs'),
        'Server' => $serverSoftware,
        'OS' => $serverOs,
    ] as $label => $value)
                                        <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                                            <span
                                                class="text-slate-500 dark:text-slate-400">{{ $label }}</span>
                                            <span
                                                class="font-mono text-slate-700 dark:text-slate-200 truncate max-w-[60%]">{{ $value }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                        </div>
                    </div>
                </div>

                <!-- Keyboard shortcuts overlay -->
                <div id="shortcutsHelp"
                    class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-900/50 p-4">
                    <div
                        class="w-full max-w-sm lwa-surface border border-slate-200 dark:border-slate-700 rounded-lg shadow-xl p-5 text-xs">
                        <div class="flex items-center justify-between mb-3">
                            <span class="font-semibold text-slate-900 dark:text-white">Keyboard shortcuts</span>
                            <button type="button"
                                onclick="document.getElementById('shortcutsHelp').classList.add('hidden')"
                                class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="space-y-2 text-slate-600 dark:text-slate-300">
                            <div class="flex justify-between"><span>Show this help</span><kbd
                                    class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 font-mono">?</kbd>
                            </div>
                            <div class="flex justify-between"><span>Close dialog</span><kbd
                                    class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 font-mono">Esc</kbd>
                            </div>
                            <div class="flex justify-between"><span>Toggle theme</span><kbd
                                    class="px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-700 font-mono">T</kbd>
                            </div>
                        </div>
                    </div>
                </div>
