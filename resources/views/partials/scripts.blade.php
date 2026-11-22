    <!-- ============ SCRIPTS ============ -->
    <script>
        const lwaStreamMode = {!! json_encode(config('laravel-web-analytics.stream.mode', 'sse')) !!};
        const lwaPollingInterval = {!! json_encode(config('laravel-web-analytics.stream.polling_interval', 5)) !!};
        const lwaDataUrl = "{{ route('lwa_data') }}";

        function lwaChart(selector, options) {
            const el = document.querySelector(selector);
            if (!el || !window.ApexCharts) return null;
            const chart = new ApexCharts(el, options);
            chart.render();
            return chart;
        }

        // 1. Dark Mode Toggle (synced with Settings > Appearance)
        (function() {
            const toggle = document.getElementById('themeToggle');

            window.lwaRenderIcons = function() {
                if (window.lucide) window.lucide.createIcons();
            };

            function setThemeIcon(dark) {
                const wrapper = document.getElementById('themeIconWrapper');
                if (!wrapper) return;
                wrapper.innerHTML = '<i data-lucide="' + (dark ? 'sun' : 'moon') + '" class="text-lg"></i>';
                window.lwaRenderIcons();
            }

            window.lwaApplyAppearance = function(appearance) {
                const dark = appearance === 'dark' ||
                    (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
                setThemeIcon(dark);
                window.dispatchEvent(new Event('theme-changed'));
            };

            const saved = localStorage.getItem('lwa_appearance') ||
                (localStorage.theme === 'dark' ? 'dark' : (localStorage.theme === 'light' ? 'light' : 'system'));
            window.lwaApplyAppearance(saved);

            if (toggle) {
                toggle.addEventListener('click', () => {
                    const darkNow = !document.documentElement.classList.contains('dark');
                    const choice = darkNow ? 'dark' : 'light';
                    window.lwaApplyAppearance(choice);
                    localStorage.setItem('lwa_appearance', choice);
                    localStorage.setItem('theme', choice);
                    const select = document.getElementById('settingAppearance');
                    if (select) select.value = choice;
                });
            }
        })();

        // 2. Sidebar Mobile Drawer Toggle
        (function() {
            const btn = document.getElementById('sidebarMobileToggle');
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');

            if (btn && sidebar && backdrop) {
                btn.addEventListener('click', () => {
                    sidebar.classList.toggle('-translate-x-full');
                    backdrop.classList.toggle('hidden');
                });
                backdrop.addEventListener('click', () => {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                });
            }
        })();

        // 2b. Desktop sidebar collapse (persisted preference)
        (function() {
            const btn = document.getElementById('sidebarCollapseToggle');

            function applySidebarState(collapsed) {
                document.body.classList.toggle('lwa-sidebar-collapsed', collapsed);
                if (!btn) return;
                btn.innerHTML = '<i data-lucide="' + (collapsed ? 'panel-left-open' : 'panel-left-close') + '" class="text-lg"></i>';
                btn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
                window.lwaRenderIcons();
                window.dispatchEvent(new Event('resize'));
            }

            applySidebarState(localStorage.getItem('lwa_sidebar_collapsed') === '1');

            if (btn) {
                btn.addEventListener('click', () => {
                    const collapsed = !document.body.classList.contains('lwa-sidebar-collapsed');
                    localStorage.setItem('lwa_sidebar_collapsed', collapsed ? '1' : '0');
                    applySidebarState(collapsed);
                });
            }
        })();

        // 3. Navigation helpers (each section is its own route/page)
        (function() {
            function scrollToHash() {
                if (!window.location.hash) return;
                try {
                    document.querySelector(window.location.hash)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch (e) { /* invalid hash */ }
            }
            window.addEventListener('hashchange', scrollToHash);
            scrollToHash();

            // Close the mobile drawer after any navigation click.
            document.querySelectorAll('.sidebar-link, .cf-tab-btn').forEach(link => {
                link.addEventListener('click', () => {
                    document.getElementById('appSidebar')?.classList.add('-translate-x-full');
                    document.getElementById('sidebarBackdrop')?.classList.add('hidden');
                });
            });
        })();

        // 4. Quick filter input on tables
        (function() {
            const input = document.getElementById('quickFilterInput');
            if (input) {
                input.addEventListener('keyup', function() {
                    let filter = this.value.toLowerCase();
                    let rows = document.querySelectorAll('#tableLiveFeed tr');
                    rows.forEach(r => {
                        let text = r.textContent.toLowerCase();
                        r.style.display = text.includes(filter) ? '' : 'none';
                    });
                });
            }
        })();

        // 5. Custom range & source switchers
        function applyCustomRange() {
            const start = document.getElementById('customStartDate')?.value;
            const end = document.getElementById('customEndDate')?.value;
            const url = new URL("{{ route('lwa_dashboard') }}", window.location.origin);
            url.searchParams.set('range', 'custom');
            if (start) url.searchParams.set('start_date', start);
            if (end) url.searchParams.set('end_date', end);
            window.location.href = url.toString();
        }

        function switchDataSource(source) {
            const url = new URL(window.location.href);
            url.searchParams.set('source', source);
            window.location.href = url.toString();
        }

        // ============ APEXCHARTS CONFIGURATION ============
        const timeLabels = {!! json_encode($analytics['time_series']['labels'] ?? ['No Data']) !!};
        const timeStamps = {!! json_encode($analytics['time_series']['timestamps'] ?? []) !!};
        const timeGranularity = {!! json_encode($analytics['time_series']['granularity'] ?? 'hour') !!};
        const requestsData = {!! json_encode($analytics['time_series']['requests'] ?? [0]) !!};
        const bandwidthData = {!! json_encode($analytics['time_series']['bandwidth_mb'] ?? [0]) !!};
        const latencyData = {!! json_encode($analytics['time_series']['latency'] ?? [0]) !!};
        const visitorsData = {!! json_encode($analytics['sparklines']['visitors'] ?? ($analytics['time_series']['visitors'] ?? [0])) !!};

        const status2xx = {!! json_encode($analytics['time_series']['status_2xx'] ?? [0]) !!};
        const status3xx = {!! json_encode($analytics['time_series']['status_3xx'] ?? [0]) !!};
        const status4xx = {!! json_encode($analytics['time_series']['status_4xx'] ?? [0]) !!};
        const status5xx = {!! json_encode($analytics['time_series']['status_5xx'] ?? [0]) !!};

        const methodBreakdown = {!! json_encode($analytics['breakdowns']['top_methods'] ?? []) !!};

        const isDarkMode = () => document.documentElement.classList.contains('dark');
        const getChartTheme = () => ({
            mode: isDarkMode() ? 'dark' : 'light',
            fontFamily: 'Plus Jakarta Sans, sans-serif'
        });

        // 1. Requests Over Time (Area Chart matching )
        let chartRequests = lwaChart("#chartRequestsOverTime", {
            series: [{
                name: 'Requests',
                data: requestsData
            }],
            chart: {
                type: 'area',
                height: 250,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                }
            },
            colors: ['#0051c3'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 95, 100]
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight',
                width: 2
            },
            xaxis: {
                categories: timeLabels,
                labels: {
                    style: {
                        fontSize: '10px',
                        colors: '#94a3b8'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '10px',
                        colors: '#94a3b8'
                    }
                }
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: {
                theme: isDarkMode() ? 'dark' : 'light',
                y: {
                    formatter: (val) => Number(val).toLocaleString() + ' reqs'
                }
            }
        });


        // Overview traffic chart (same series, separate container)
        let chartOverviewTraffic = null;
        const overviewChartEl = document.querySelector("#chartOverviewTraffic");
        if (overviewChartEl) {
            chartOverviewTraffic = new ApexCharts(overviewChartEl, {
                series: [{ name: 'Requests', data: requestsData }],
                chart: { type: 'area', height: 250, toolbar: { show: false }, zoom: { enabled: false } },
                colors: ['#4f46e5'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95, 100] } },
                dataLabels: { enabled: false },
                stroke: { curve: 'straight', width: 2 },
                xaxis: { categories: timeLabels, labels: { style: { fontSize: '10px', colors: '#94a3b8' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { style: { fontSize: '10px', colors: '#94a3b8' } } },
                grid: { borderColor: isDarkMode() ? '#26344b' : '#e4e8f0', strokeDashArray: 3 },
                tooltip: { theme: isDarkMode() ? 'dark' : 'light', y: { formatter: (val) => Number(val).toLocaleString() + ' reqs' } }
            });
            chartOverviewTraffic.render();
        }

        // 2. Data Transfer (Area Chart matching )
        let chartBandwidth = lwaChart("#chartDataTransfer", {
            series: [{
                name: 'Data Transfer (MB)',
                data: bandwidthData
            }],
            chart: {
                type: 'area',
                height: 250,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                }
            },
            colors: ['#0051c3'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 95, 100]
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'straight',
                width: 2
            },
            xaxis: {
                categories: timeLabels,
                labels: {
                    style: {
                        fontSize: '10px',
                        colors: '#94a3b8'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '10px',
                        colors: '#94a3b8'
                    },
                    formatter: (val) => val + ' MB'
                }
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: {
                theme: isDarkMode() ? 'dark' : 'light',
                y: {
                    formatter: (val) => val + ' MB'
                }
            }
        });


        // 3. Status Code Stacked Column Chart
        let chartStatus = lwaChart("#chartStatusCodeBars", {
            series: [{
                    name: '2xx Success',
                    data: status2xx
                },
                {
                    name: '3xx Redirect',
                    data: status3xx
                },
                {
                    name: '4xx Client Err',
                    data: status4xx
                },
                {
                    name: '5xx Server Err',
                    data: status5xx
                }
            ],
            chart: {
                type: 'bar',
                height: 250,
                stacked: true,
                toolbar: {
                    show: false
                }
            },
            colors: ['#10b981', '#06b6d4', '#f43f5e', '#a855f7'],
            plotOptions: {
                bar: {
                    columnWidth: '45%',
                    borderRadius: 2
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: timeLabels,
                labels: {
                    style: {
                        fontSize: '10px',
                        colors: '#94a3b8'
                    }
                },
                axisBorder: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '10px',
                        colors: '#94a3b8'
                    }
                }
            },
            legend: {
                show: false
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: {
                theme: isDarkMode() ? 'dark' : 'light'
            }
        });


        // 4. Method Breakdown Bar Chart
        let chartMethod = lwaChart("#chartMethodBars", {
            series: [{
                name: 'Requests',
                data: methodBreakdown.map(m => m.count)
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: {
                    show: false
                }
            },
            colors: ['#3b82f6'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    barHeight: '40%'
                }
            },
            dataLabels: {
                enabled: false
            },
            xaxis: {
                categories: methodBreakdown.map(m => m.name),
                labels: {
                    style: {
                        fontSize: '10px',
                        colors: '#94a3b8'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '11px',
                        colors: '#94a3b8'
                    }
                }
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: {
                theme: isDarkMode() ? 'dark' : 'light'
            }
        });


        // 5. Origin Latency Chart
        let chartLatency = null;
        let chartLatencyEl = document.querySelector("#chartLatency");
        if (chartLatencyEl) {
            chartLatency = new ApexCharts(chartLatencyEl, {
                series: [{
                    name: 'Latency (ms)',
                    data: latencyData
                }],
                chart: {
                    type: 'line',
                    height: 250,
                    toolbar: {
                        show: false
                    }
                },
                colors: ['#f59e0b'],
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                xaxis: {
                    categories: timeLabels,
                    labels: {
                        style: {
                            fontSize: '10px',
                            colors: '#94a3b8'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => val + ' ms',
                        style: {
                            fontSize: '10px',
                            colors: '#94a3b8'
                        }
                    }
                },
                grid: {
                    borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                    strokeDashArray: 3
                },
                tooltip: {
                    theme: isDarkMode() ? 'dark' : 'light'
                }
            });
            chartLatency.render();
        }

        // Mini Sparklines
        function renderSparkline(selector, data, color) {
            let el = document.querySelector(selector);
            if (!el) return;
            new ApexCharts(el, {
                series: [{
                    data: data && data.length ? data : [0]
                }],
                chart: {
                    type: 'line',
                    height: 28,
                    sparkline: {
                        enabled: true
                    }
                },
                colors: [color],
                stroke: {
                    curve: 'smooth',
                    width: 1.5
                },
                tooltip: {
                    fixed: {
                        enabled: false
                    },
                    x: {
                        show: false
                    }
                }
            }).render();
        }

        renderSparkline('#sparklineRequests', requestsData, '#0051c3');
        renderSparkline('#sparklineBytes', bandwidthData, '#0051c3');
        renderSparkline('#sparklineVisits', visitorsData, '#0051c3');

        // Charts whose x-axis is the shared time series (used by timezone settings)
        window.lwaTimeCharts = [chartOverviewTraffic, chartRequests, chartBandwidth, chartStatus, chartLatency].filter(Boolean);

        // ============ OFFLINE GEOIP LIVE MAP ============
        (function() {
            let map = null;
            let located = 0;

            window.lwaInitLiveMap = function() {
                if (!window.jsVectorMap || !document.getElementById('lwaLiveMap') || map) return;
                try {
                    map = new jsVectorMap({
                        selector: '#lwaLiveMap',
                        map: 'world',
                        zoomButtons: true,
                        regionStyle: {
                            initial: { fill: '#cbd5e1', stroke: '#ffffff', strokeWidth: 0.5 },
                            hover: { fill: '#94a3b8' }
                        },
                        markerStyle: { initial: { fill: '#4f46e5', stroke: '#ffffff', strokeWidth: 1, r: 5 } },
                        markers: []
                    });
                } catch (err) { /* map library unavailable */ }
            };

            window.lwaAddMapMarker = function(lat, lng, label) {
                if (!map || !lat || !lng) return;
                try {
                    map.addMarker({ latLng: [Number(lat), Number(lng)], name: label || 'Visitor' });
                    located++;
                    const count = document.getElementById('lwaMapCount');
                    const empty = document.getElementById('lwaMapEmpty');
                    if (count) count.textContent = located + ' located';
                    if (empty) empty.classList.add('hidden');
                } catch (err) { /* ignore bad coordinates */ }
            };
        })();

        // ============ REAL-TIME SSE STREAM ============
        (function() {
            let lastTrafficId = {{ \Nafiur\WebAnalytics\Models\LwaLogs::forTenant($tenantId ?? null)->max('id') ?? 0 }};
            let sseSource = null;
            let currentTenantId = {!! json_encode((string) ($tenantId ?? '')) !!};
            let connectionFailures = 0;

            function setConnectionState(state) {
                const dot = document.getElementById('lwaConnectionDot');
                const text = document.getElementById('lwaConnectionText');
                const retry = document.getElementById('lwaReconnectBtn');
                const styles = {
                    connected:    ['bg-emerald-500', 'text-emerald-600 dark:text-emerald-400', 'Connected'],
                    reconnecting: ['bg-amber-500', 'text-amber-600 dark:text-amber-400', 'Reconnecting…'],
                    disconnected: ['bg-rose-500', 'text-rose-600 dark:text-rose-400', 'Disconnected'],
                    polling:      ['bg-sky-500', 'text-sky-600 dark:text-sky-400', 'Polling']
                };
                const [dotClass, textClass, label] = styles[state];
                if (dot) dot.className = 'w-2 h-2 rounded-full ' + dotClass;
                if (text) {
                    text.className = textClass;
                    text.textContent = label;
                }
                if (retry) retry.classList.toggle('hidden', state !== 'disconnected');
            }

            window.lwaStartSSE = function() {
                connectionFailures = 0;
                startSSE();
            };

            function startSSE() {
                // Polling fallback: no persistent EventSource connection, safe
                // for shared hosting with a very small PHP-FPM worker pool.
                if (lwaStreamMode === 'polling') {
                    if (sseSource) { sseSource.close(); sseSource = null; }
                    setConnectionState('polling');
                    if (!window.lwaPollingStarted) {
                        window.lwaPollingStarted = true;
                        setInterval(async function() {
                            try {
                                const url = new URL(lwaDataUrl, window.location.origin);
                                const current = new URLSearchParams(window.location.search);
                                current.forEach(function(value, key) { url.searchParams.set(key, value); });
                                const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                                if (!response.ok) throw new Error('HTTP ' + response.status);
                                const payload = await response.json();
                                const logs = (payload.analytics && payload.analytics.recent_logs) || [];
                                logs.forEach(function(log) {
                                    if (log.id > lastTrafficId) {
                                        prependLiveFeedRow(log);
                                        if (log.latitude && log.longitude && window.lwaAddMapMarker) {
                                            window.lwaAddMapMarker(log.latitude, log.longitude, log.city || log.country || log.ip_address);
                                        }
                                        lastTrafficId = log.id;
                                    }
                                });
                                setConnectionState('polling');
                            } catch (err) {
                                setConnectionState('reconnecting');
                            }
                        }, Math.max(2, Number(lwaPollingInterval) || 5) * 1000);
                    }
                    return;
                }

                if (!window.EventSource) {
                    setConnectionState('disconnected');
                    return;
                }
                if (sseSource) sseSource.close();
                setConnectionState('reconnecting');

                let streamUrl = "{{ route('lwa_stream') }}?last_id=" + lastTrafficId;
                if (currentTenantId) {
                    streamUrl += "&tenant_id=" + encodeURIComponent(currentTenantId);
                }

                sseSource = new EventSource(streamUrl);

                sseSource.addEventListener('traffic', function(e) {
                    try {
                        let parsed = JSON.parse(e.data);
                        if (parsed.last_id) lastTrafficId = parsed.last_id;
                        if (parsed.logs && parsed.logs.length > 0) {
                            parsed.logs.forEach(function(log) {
                                prependLiveFeedRow(log);
                                if (log.latitude && log.longitude && window.lwaAddMapMarker) {
                                    window.lwaAddMapMarker(log.latitude, log.longitude, log.city || log.country || log.ip_address);
                                }
                            });
                        }
                    } catch (err) {}
                });

                sseSource.addEventListener('stats', function(e) {
                    try {
                        let stats = JSON.parse(e.data);
                        if (stats.live_visitors !== undefined) {
                            let el = document.getElementById('liveVisitorTabCounter');
                            if (el) el.textContent = stats.live_visitors;
                        }
                        if (stats.total_requests) {
                            let el = document.getElementById('kpiRequests');
                            if (el) el.textContent = Number(stats.total_requests).toLocaleString();
                        }
                    } catch (err) {}
                });

                sseSource.addEventListener('ping', function(e) {
                    try {
                        let p = JSON.parse(e.data);
                        if (p.last_id) lastTrafficId = p.last_id;
                    } catch (err) {}
                });

                sseSource.onopen = function() {
                    connectionFailures = 0;
                    setConnectionState('connected');
                };

                sseSource.onerror = function() {
                    connectionFailures++;
                    // EventSource retries automatically; surface a manual retry
                    // after repeated failures so shared hosting stays usable.
                    setConnectionState(connectionFailures >= 3 ? 'disconnected' : 'reconnecting');
                };
            }

            startSSE();

            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function prependLiveFeedRow(log) {
                let tbody = document.getElementById('tableLiveFeed');
                if (!tbody) return;

                let rowId = 'log-row-' + log.id;
                if (document.getElementById(rowId)) return;

                let st = parseInt(log.status_code || 200);
                let stBadge = st >= 500 ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400' :
                    (st >= 400 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' :
                        (st >= 300 ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-400' :
                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400'));

                let cleanMethod = escapeHtml(log.method || 'GET');
                let cleanUser = escapeHtml(log.user_name || 'Guest');
                let cleanPath = escapeHtml(log.path || '/');
                let cleanIp = escapeHtml(log.ip_address || '-');
                let ipFlag = escapeHtml(log.flag || '');
                let cleanBytes = escapeHtml(log.bytes_sent_fmt || '-');
                let cleanClient = escapeHtml((log.browser || 'Other') + ' on ' + (log.os || 'Other'));

                let tr = document.createElement('tr');
                tr.id = rowId;
                tr.className = 'bg-blue-50/50 dark:bg-blue-950/20 transition-all duration-1000';
                tr.innerHTML = `
                    <td class="py-2.5 px-4"><span class="px-2 py-0.5 rounded font-mono font-bold ${stBadge}">${st}</span></td>
                    <td class="py-2.5 px-4 font-mono font-semibold ${log.method === 'POST' ? 'text-sky-600' : 'text-emerald-600'}">${cleanMethod}</td>
                    <td class="py-2.5 px-4"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] ${log.is_auth ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-400 font-medium' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'}">${cleanUser}</span></td>
                    <td class="py-2.5 px-4 font-mono text-slate-700 dark:text-slate-200 truncate max-w-[220px]" title="${cleanPath}">${cleanPath}</td>
                    <td class="py-2.5 px-4 font-mono text-slate-500">${ipFlag ? '<span class="mr-1" aria-hidden="true">' + ipFlag + '</span>' : ''}${cleanIp}</td>
                    <td class="py-2.5 px-4 font-mono text-slate-600 dark:text-slate-300">${parseFloat(log.response_time_ms || 0)} ms</td>
                    <td class="py-2.5 px-4 text-slate-500">${cleanBytes}</td>
                    <td class="py-2.5 px-4 text-slate-500 dark:text-slate-400 truncate max-w-[140px]">${cleanClient}</td>
                    <td class="py-2.5 px-4 text-right text-slate-400" data-utc="${escapeHtml(log.created_at_iso || '')}">just now</td>
                `;

                tbody.insertBefore(tr, tbody.firstChild);
                if (window.lwaApplyTimezone) window.lwaApplyTimezone();
                setTimeout(() => {
                    tr.className =
                        'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-all duration-1000';
                }, 1500);

                while (tbody.children.length > 30) {
                    tbody.removeChild(tbody.lastChild);
                }

                const clock = document.getElementById('lastUpdatedClock');
                if (clock) clock.textContent = 'Hit: ' + log.path + ' (' + new Date().toLocaleTimeString() + ')';
            }
        })();

        // 9. Settings page preferences (language / appearance / timezone / data)
        (function() {
            const dictionary = {
                en: {
                    settings: 'Settings',
                    dashboard: 'Dashboard',
                    collection: 'Collection',
                    maintenance: 'Data maintenance',
                    general: 'General',
                    language: 'Language',
                    appearance: 'Appearance',
                    timezone: 'Timezone',
                    defaultRange: 'Default time range',
                    overview: 'Overview',
                    realtime: 'Real-time',
                    trafficSources: 'Traffic Sources',
                    pages: 'Pages & Screens',
                    geography: 'Geography',
                    devices: 'Devices & Browsers',
                    campaigns: 'Campaigns',
                    events: 'Events & Goals',
                    conversions: 'Conversions',
                    errors: 'Error Monitoring',
                    requests: 'Requests',
                    visitors: 'Visitors',
                    performance: 'Performance',
                    liveStream: 'Live Stream',
                    security: 'Security',
                    addFilter: 'Add filter',
                    export: 'Export',
                    totalRequests: 'Total requests',
                    dataTransfer: 'Total data transfer',
                    visits: 'Total visits',
                    successRate: 'Success rate (2xx)',
                    errorRate: '5xx error rate',
                    topPaths: 'Top paths',
                    topCountries: 'Top countries',
                    topIps: 'Top client IPs',
                    recentRequests: 'Recent requests',
                    prune: 'Prune',
                    emptyTable: 'Empty table'
                },
                bn: {
                    settings: 'সেটিংস',
                    dashboard: 'ড্যাশবোর্ড',
                    collection: 'সংগ্রহ',
                    maintenance: 'ডাটা রক্ষণাবেক্ষণ',
                    general: 'সাধারণ',
                    language: 'ভাষা',
                    appearance: 'অ্যাপিয়ারেন্স',
                    timezone: 'সময় অঞ্চল',
                    defaultRange: 'ডিফল্ট সময় সীমা',
                    overview: 'ওভারভিউ',
                    realtime: 'রিয়েল-টাইম',
                    trafficSources: 'ট্রাফিক সোর্স',
                    pages: 'পেজ ও স্ক্রিন',
                    geography: 'ভূগোল',
                    devices: 'ডিভাইস ও ব্রাউজার',
                    campaigns: 'ক্যাম্পেইন',
                    events: 'ইভেন্ট ও গোল',
                    conversions: 'কনভার্শন',
                    errors: 'এরর মনিটরিং',
                    requests: 'রিকোয়েস্ট',
                    visitors: 'ভিজিটর',
                    performance: 'পারফরম্যান্স',
                    liveStream: 'লাইভ স্ট্রিম',
                    security: 'নিরাপত্তা',
                    addFilter: 'ফিল্টার যোগ করুন',
                    export: 'এক্সপোর্ট',
                    totalRequests: 'মোট রিকোয়েস্ট',
                    dataTransfer: 'মোট ডাটা ট্রান্সফার',
                    visits: 'মোট ভিজিট',
                    successRate: 'সফলতার হার (2xx)',
                    errorRate: '5xx ত্রুটির হার',
                    topPaths: 'শীর্ষ পাথ',
                    topCountries: 'শীর্ষ দেশ',
                    topIps: 'শীর্ষ ক্লায়েন্ট আইপি',
                    recentRequests: 'সাম্প্রতিক রিকোয়েস্ট',
                    prune: 'মুছুন',
                    emptyTable: 'টেবিল খালি করুন'
                },
                hi: {
                    settings: 'सेटिंग्स',
                    dashboard: 'डैशबोर्ड',
                    collection: 'संग्रह',
                    maintenance: 'डेटा रखरखाव',
                    general: 'सामान्य',
                    language: 'भाषा',
                    appearance: 'रूप',
                    timezone: 'समय क्षेत्र',
                    defaultRange: 'डिफ़ॉल्ट समय सीमा',
                    overview: 'अवलोकन',
                    requests: 'अनुरोध',
                    visitors: 'आगंतुक',
                    performance: 'प्रदर्शन',
                    liveStream: 'लाइव स्ट्रीम',
                    security: 'सुरक्षा',
                    addFilter: 'फ़िल्टर जोड़ें',
                    export: 'निर्यात',
                    totalRequests: 'कुल अनुरोध',
                    dataTransfer: 'कुल डेटा स्थानांतरण',
                    visits: 'कुल विज़िट',
                    successRate: 'सफलता दर (2xx)',
                    errorRate: '5xx त्रुटि दर',
                    topPaths: 'शीर्ष पथ',
                    topCountries: 'शीर्ष देश',
                    topIps: 'शीर्ष IP',
                    recentRequests: 'हाल के अनुरोध',
                    prune: 'पुराने हटाएँ',
                    emptyTable: 'तालिका खाली करें'
                },
                es: {
                    settings: 'Ajustes',
                    dashboard: 'Panel',
                    collection: 'Recopilación',
                    maintenance: 'Mantenimiento de datos',
                    general: 'General',
                    language: 'Idioma',
                    appearance: 'Apariencia',
                    timezone: 'Zona horaria',
                    defaultRange: 'Rango de tiempo predeterminado',
                    overview: 'Resumen',
                    requests: 'Solicitudes',
                    visitors: 'Visitantes',
                    performance: 'Rendimiento',
                    liveStream: 'Transmisión en vivo',
                    security: 'Seguridad',
                    addFilter: 'Añadir filtro',
                    export: 'Exportar',
                    totalRequests: 'Solicitudes totales',
                    dataTransfer: 'Transferencia total de datos',
                    visits: 'Visitas totales',
                    successRate: 'Tasa de éxito (2xx)',
                    errorRate: 'Tasa de errores 5xx',
                    topPaths: 'Rutas principales',
                    topCountries: 'Países principales',
                    topIps: 'IP principales',
                    recentRequests: 'Solicitudes recientes',
                    prune: 'Depurar',
                    emptyTable: 'Vaciar tabla'
                },
                fr: {
                    settings: 'Paramètres',
                    dashboard: 'Tableau de bord',
                    collection: 'Collecte',
                    maintenance: 'Maintenance des données',
                    general: 'Général',
                    language: 'Langue',
                    appearance: 'Apparence',
                    timezone: 'Fuseau horaire',
                    defaultRange: 'Plage de temps par défaut',
                    overview: 'Aperçu',
                    requests: 'Requêtes',
                    visitors: 'Visiteurs',
                    performance: 'Performances',
                    liveStream: 'Flux en direct',
                    security: 'Sécurité',
                    addFilter: 'Ajouter un filtre',
                    export: 'Exporter',
                    totalRequests: 'Requêtes totales',
                    dataTransfer: 'Transfert total de données',
                    visits: 'Visites totales',
                    successRate: 'Taux de réussite (2xx)',
                    errorRate: "Taux d'erreurs 5xx",
                    topPaths: 'Chemins principaux',
                    topCountries: 'Pays principaux',
                    topIps: 'IP principales',
                    recentRequests: 'Requêtes récentes',
                    prune: 'Nettoyer',
                    emptyTable: 'Vider la table'
                },
                de: {
                    settings: 'Einstellungen',
                    dashboard: 'Dashboard',
                    collection: 'Sammlung',
                    maintenance: 'Datenwartung',
                    general: 'Allgemein',
                    language: 'Sprache',
                    appearance: 'Darstellung',
                    timezone: 'Zeitzone',
                    defaultRange: 'Standardzeitraum',
                    overview: 'Übersicht',
                    requests: 'Anfragen',
                    visitors: 'Besucher',
                    performance: 'Leistung',
                    liveStream: 'Live-Stream',
                    security: 'Sicherheit',
                    addFilter: 'Filter hinzufügen',
                    export: 'Exportieren',
                    totalRequests: 'Anfragen gesamt',
                    dataTransfer: 'Datenübertragung gesamt',
                    visits: 'Besuche gesamt',
                    successRate: 'Erfolgsquote (2xx)',
                    errorRate: '5xx-Fehlerrate',
                    topPaths: 'Top-Pfade',
                    topCountries: 'Top-Länder',
                    topIps: 'Top-IPs',
                    recentRequests: 'Letzte Anfragen',
                    prune: 'Bereinigen',
                    emptyTable: 'Tabelle leeren'
                },
                pt: {
                    settings: 'Configurações',
                    dashboard: 'Painel',
                    collection: 'Coleta',
                    maintenance: 'Manutenção de dados',
                    general: 'Geral',
                    language: 'Idioma',
                    appearance: 'Aparência',
                    timezone: 'Fuso horário',
                    defaultRange: 'Intervalo padrão',
                    overview: 'Visão geral',
                    requests: 'Solicitações',
                    visitors: 'Visitantes',
                    performance: 'Desempenho',
                    liveStream: 'Transmissão ao vivo',
                    security: 'Segurança',
                    addFilter: 'Adicionar filtro',
                    export: 'Exportar',
                    totalRequests: 'Solicitações totais',
                    dataTransfer: 'Transferência total de dados',
                    visits: 'Visitas totais',
                    successRate: 'Taxa de sucesso (2xx)',
                    errorRate: 'Taxa de erros 5xx',
                    topPaths: 'Principais caminhos',
                    topCountries: 'Principais países',
                    topIps: 'Principais IPs',
                    recentRequests: 'Solicitações recentes',
                    prune: 'Limpar antigos',
                    emptyTable: 'Esvaziar tabela'
                },
                ar: {
                    settings: 'الإعدادات',
                    dashboard: 'لوحة التحكم',
                    collection: 'التجميع',
                    maintenance: 'صيانة البيانات',
                    general: 'عام',
                    language: 'اللغة',
                    appearance: 'المظهر',
                    timezone: 'المنطقة الزمنية',
                    defaultRange: 'النطاق الزمني الافتراضي',
                    overview: 'نظرة عامة',
                    requests: 'الطلبات',
                    visitors: 'الزوار',
                    performance: 'الأداء',
                    liveStream: 'البث المباشر',
                    security: 'الأمان',
                    addFilter: 'إضافة عامل تصفية',
                    export: 'تصدير',
                    totalRequests: 'إجمالي الطلبات',
                    dataTransfer: 'إجمالي نقل البيانات',
                    visits: 'إجمالي الزيارات',
                    successRate: 'معدل النجاح (2xx)',
                    errorRate: 'معدل أخطاء 5xx',
                    topPaths: 'أبرز المسارات',
                    topCountries: 'أبرز الدول',
                    topIps: 'أبرز عناوين IP',
                    recentRequests: 'الطلبات الأخيرة',
                    prune: 'تنظيف',
                    emptyTable: 'إفراغ الجدول'
                },
                zh: {
                    settings: '设置',
                    dashboard: '仪表盘',
                    collection: '采集',
                    maintenance: '数据维护',
                    general: '常规',
                    language: '语言',
                    appearance: '外观',
                    timezone: '时区',
                    defaultRange: '默认时间范围',
                    overview: '概览',
                    requests: '请求',
                    visitors: '访客',
                    performance: '性能',
                    liveStream: '实时流',
                    security: '安全',
                    addFilter: '添加筛选',
                    export: '导出',
                    totalRequests: '总请求数',
                    dataTransfer: '总数据传输量',
                    visits: '总访问量',
                    successRate: '成功率 (2xx)',
                    errorRate: '5xx 错误率',
                    topPaths: '热门路径',
                    topCountries: '热门国家',
                    topIps: '热门 IP',
                    recentRequests: '最近请求',
                    prune: '清理',
                    emptyTable: '清空表'
                },
                ja: {
                    settings: '設定',
                    dashboard: 'ダッシュボード',
                    collection: '収集',
                    maintenance: 'データメンテナンス',
                    general: '一般',
                    language: '言語',
                    appearance: '外観',
                    timezone: 'タイムゾーン',
                    defaultRange: '既定の期間',
                    overview: '概要',
                    requests: 'リクエスト',
                    visitors: '訪問者',
                    performance: 'パフォーマンス',
                    liveStream: 'ライブストリーム',
                    security: 'セキュリティ',
                    addFilter: 'フィルターを追加',
                    export: 'エクスポート',
                    totalRequests: '総リクエスト数',
                    dataTransfer: '総転送量',
                    visits: '総訪問数',
                    successRate: '成功率 (2xx)',
                    errorRate: '5xx エラー率',
                    topPaths: '人気パス',
                    topCountries: '人気国',
                    topIps: '人気 IP',
                    recentRequests: '最近のリクエスト',
                    prune: '古いデータを削除',
                    emptyTable: 'テーブルを空にする'
                },
                ur: {
                    settings: 'ترتیبات',
                    dashboard: 'ڈیش بورڈ',
                    collection: 'مجموعہ',
                    maintenance: 'ڈیٹا کی دیکھ بھال',
                    general: 'عمومی',
                    language: 'زبان',
                    appearance: 'ظاہری شکل',
                    timezone: 'ٹائم زون',
                    defaultRange: 'طے شدہ مدت',
                    overview: 'جائزہ',
                    requests: 'درخواستیں',
                    visitors: 'زائرین',
                    performance: 'کارکردگی',
                    liveStream: 'لائیو اسٹریم',
                    security: 'سیکیورٹی',
                    addFilter: 'فلٹر شامل کریں',
                    export: 'برآمد',
                    totalRequests: 'کل درخواستیں',
                    dataTransfer: 'کل ڈیٹا منتقلی',
                    visits: 'کل وزٹ',
                    successRate: 'کامیابی کی شرح (2xx)',
                    errorRate: '5xx غلطی کی شرح',
                    topPaths: 'سرفہرست راستے',
                    topCountries: 'سرفہرست ممالک',
                    topIps: 'سرفہرست IP',
                    recentRequests: 'حالیہ درخواستیں',
                    prune: 'پرانی حذف کریں',
                    emptyTable: 'ٹیبل خالی کریں'
                },
                id: {
                    settings: 'Pengaturan',
                    dashboard: 'Dasbor',
                    collection: 'Koleksi',
                    maintenance: 'Pemeliharaan data',
                    general: 'Umum',
                    language: 'Bahasa',
                    appearance: 'Tampilan',
                    timezone: 'Zona waktu',
                    defaultRange: 'Rentang waktu default',
                    overview: 'Ringkasan',
                    requests: 'Permintaan',
                    visitors: 'Pengunjung',
                    performance: 'Kinerja',
                    liveStream: 'Streaming langsung',
                    security: 'Keamanan',
                    addFilter: 'Tambah filter',
                    export: 'Ekspor',
                    totalRequests: 'Total permintaan',
                    dataTransfer: 'Total transfer data',
                    visits: 'Total kunjungan',
                    successRate: 'Tingkat keberhasilan (2xx)',
                    errorRate: 'Tingkat error 5xx',
                    topPaths: 'Jalur teratas',
                    topCountries: 'Negara teratas',
                    topIps: 'IP teratas',
                    recentRequests: 'Permintaan terbaru',
                    prune: 'Bersihkan',
                    emptyTable: 'Kosongkan tabel'
                }
            };

            const languageSelect = document.getElementById('settingLanguage');
            const appearanceSelect = document.getElementById('settingAppearance');
            const timezoneSelect = document.getElementById('settingTimezone');
            const rangeSelect = document.getElementById('settingDefaultRange');
            const compactToggle = document.getElementById('settingCompact');
            const shortcutsToggle = document.getElementById('settingShortcuts');
            const clock = document.getElementById('settingsClock');

            function currentLanguage() {
                return localStorage.getItem('lwa_language') || 'en';
            }

            const languageFlags = {
                en: '🇺🇸', bn: '🇧🇩', hi: '🇮🇳', es: '🇪🇸', fr: '🇫🇷', de: '🇩🇪',
                pt: '🇵🇹', ar: '🇸🇦', zh: '🇨🇳', ja: '🇯🇵', ur: '🇵🇰', id: '🇮🇩'
            };

            function applyLanguage(lang) {
                const dict = dictionary[lang] || dictionary.en;
                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (dict[key]) el.textContent = dict[key];
                });
                const flag = document.getElementById('languageFlag');
                if (flag) flag.textContent = languageFlags[lang] || '🇺🇸';
                document.documentElement.dir = (lang === 'ar' || lang === 'ur') ? 'rtl' : 'ltr';
                if (languageSelect) languageSelect.value = lang;
            }

            function currentTimezone() {
                return localStorage.getItem('lwa_timezone') || 'UTC';
            }

            function formatDataElements(tz) {
                document.querySelectorAll('[data-utc]').forEach(el => {
                    const iso = el.getAttribute('data-utc');
                    if (!iso) return;
                    const date = new Date(iso);
                    if (isNaN(date.getTime())) return;
                    try {
                        el.textContent = new Intl.DateTimeFormat(undefined, {
                            timeZone: tz,
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                            hour12: false
                        }).format(date);
                    } catch (e) {
                        /* invalid timezone falls back to the server value */ }
                });
            }

            function tickClock() {
                if (!clock) return;
                try {
                    clock.textContent = new Intl.DateTimeFormat(undefined, {
                        timeZone: currentTimezone(),
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: false
                    }).format(new Date());
                } catch (e) {
                    /* ignore */ }
            }

            function formatTimeLabels(tz) {
                if (!Array.isArray(timeStamps) || !timeStamps.length) return [];
                return timeStamps.map((iso, i) => {
                    const date = new Date(iso);
                    if (isNaN(date.getTime())) return timeLabels[i] || iso;
                    try {
                        return new Intl.DateTimeFormat(undefined, {
                            timeZone: tz,
                            ...(timeGranularity === 'hour' ?
                                {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false
                                } :
                                {
                                    day: '2-digit',
                                    month: 'short'
                                })
                        }).format(date);
                    } catch (e) {
                        return timeLabels[i] || iso;
                    }
                });
            }

            function applyTimezone() {
                const tz = currentTimezone();
                formatDataElements(tz);
                const labels = formatTimeLabels(tz);
                if (labels.length && window.lwaTimeCharts && window.lwaTimeCharts.length) {
                    window.lwaTimeCharts.forEach(chart => chart.updateOptions({
                        xaxis: {
                            categories: labels
                        }
                    }));
                }
                tickClock();
            }

            // New live rows only need their own timestamp reformatted.
            window.lwaApplyTimezone = function() {
                formatDataElements(currentTimezone());
            };

            applyLanguage(currentLanguage());
            applyTimezone();
            if (timezoneSelect) timezoneSelect.value = currentTimezone();
            if (clock) setInterval(tickClock, 1000);

            if (languageSelect) {
                languageSelect.addEventListener('change', () => {
                    localStorage.setItem('lwa_language', languageSelect.value);
                    applyLanguage(languageSelect.value);
                });
            }

            if (appearanceSelect) {
                appearanceSelect.value = localStorage.getItem('lwa_appearance') || 'system';
                appearanceSelect.addEventListener('change', () => {
                    localStorage.setItem('lwa_appearance', appearanceSelect.value);
                    if (appearanceSelect.value === 'system') localStorage.removeItem('theme');
                    else localStorage.setItem('theme', appearanceSelect.value);
                    window.lwaApplyAppearance(appearanceSelect.value);
                });
            }

            if (timezoneSelect) {
                timezoneSelect.addEventListener('change', () => {
                    localStorage.setItem('lwa_timezone', timezoneSelect.value);
                    applyTimezone();
                });
            }

            // Apply the saved default range on the next plain dashboard visit.
            const savedRange = localStorage.getItem('lwa_default_range');
            const params = new URLSearchParams(window.location.search);
            if (savedRange && savedRange !== '24h' && !params.has('range')) {
                params.set('range', savedRange);
                window.location.search = params.toString();
            }
            if (rangeSelect) {
                rangeSelect.addEventListener('change', () => {
                    localStorage.setItem('lwa_default_range', rangeSelect.value);
                });
            }

            if (compactToggle) {
                const compact = localStorage.getItem('lwa_compact') === '1';
                compactToggle.checked = compact;
                document.body.classList.toggle('lwa-compact', compact);
                compactToggle.addEventListener('change', () => {
                    localStorage.setItem('lwa_compact', compactToggle.checked ? '1' : '0');
                    document.body.classList.toggle('lwa-compact', compactToggle.checked);
                });
            }

            let shortcutsEnabled = localStorage.getItem('lwa_shortcuts') !== '0';
            if (shortcutsToggle) {
                shortcutsToggle.checked = shortcutsEnabled;
                shortcutsToggle.addEventListener('change', () => {
                    shortcutsEnabled = shortcutsToggle.checked;
                    localStorage.setItem('lwa_shortcuts', shortcutsEnabled ? '1' : '0');
                });
            }

            const help = document.getElementById('shortcutsHelp');
            document.addEventListener('keydown', (e) => {
                if (!help) return;
                if (e.key === 'Escape') {
                    help.classList.add('hidden');
                    return;
                }
                if (!shortcutsEnabled) return;
                if (e.key === '?' && !['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement
                    ?.tagName)) {
                    help.classList.remove('hidden');
                    help.classList.add('flex');
                }
                if (e.key.toLowerCase() === 't' && !['INPUT', 'SELECT', 'TEXTAREA'].includes(document
                        .activeElement?.tagName)) {
                    document.getElementById('themeToggle')?.click();
                }
            });

            // Export link defaults
            const exportRangeSelect = document.getElementById('settingExportRange');
            const exportTenantToggle = document.getElementById('settingExportTenant');

            function applyExportDefaults() {
                document.querySelectorAll('.lwa-export-link').forEach(link => {
                    try {
                        const url = new URL(link.href, window.location.origin);
                        const range = localStorage.getItem('lwa_export_range');
                        if (range) url.searchParams.set('range', range);
                        if (localStorage.getItem('lwa_export_tenant') !== '1') {
                            url.searchParams.delete('tenant_id');
                        }
                        link.href = url.toString();
                    } catch (e) {
                        /* keep the server-rendered URL */ }
                });
            }
            if (exportRangeSelect) {
                const savedExportRange = localStorage.getItem('lwa_export_range') || (rangeSelect ? rangeSelect.value :
                    '24h');
                exportRangeSelect.value = savedExportRange;
                exportRangeSelect.addEventListener('change', () => {
                    localStorage.setItem('lwa_export_range', exportRangeSelect.value);
                    applyExportDefaults();
                });
            }
            if (exportTenantToggle) {
                exportTenantToggle.checked = localStorage.getItem('lwa_export_tenant') === '1';
                exportTenantToggle.addEventListener('change', () => {
                    localStorage.setItem('lwa_export_tenant', exportTenantToggle.checked ? '1' : '0');
                    applyExportDefaults();
                });
            }
            applyExportDefaults();

            document.querySelectorAll('.settings-nav-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    const section = document.querySelector(link.getAttribute('href'));
                    if (!section) return;
                    e.preventDefault();
                    document.querySelectorAll('.settings-nav-link').forEach(l => l.classList.remove(
                        'active'));
                    link.classList.add('active');
                    section.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });
        })();

        // 11. Manual refresh with explicit error state
        (function() {
            const btn = document.getElementById('lwaRefreshBtn');
            const toast = document.getElementById('lwaErrorToast');
            if (!btn) return;

            btn.addEventListener('click', async function() {
                const originalDisabled = btn.disabled;
                btn.disabled = true;
                const url = new URL("{{ route('lwa_data') }}", window.location.origin);
                window.location.search.replace(/^\?/, '').split('&').forEach(function(pair) {
                    if (!pair) return;
                    const parts = pair.split('=');
                    url.searchParams.set(decodeURIComponent(parts[0]), decodeURIComponent(parts.slice(1).join('=') || ''));
                });

                try {
                    const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const payload = await response.json();
                    if (!payload.success) throw new Error('Bad payload');

                    const summary = (payload.analytics && payload.analytics.summary) || {};
                    const requests = document.getElementById('kpiRequests');
                    const visits = document.getElementById('kpiVisits');
                    const bytes = document.getElementById('kpiBytes');
                    if (requests && summary.total_requests_fmt !== undefined) requests.textContent = summary.total_requests_fmt;
                    if (visits && summary.unique_visitors_fmt !== undefined) visits.textContent = summary.unique_visitors_fmt;
                    if (bytes && summary.total_bytes_fmt !== undefined) bytes.textContent = summary.total_bytes_fmt;
                    if (toast) toast.classList.add('hidden');
                } catch (err) {
                    if (toast) {
                        toast.classList.remove('hidden');
                        window.lwaRenderIcons();
                    }
                } finally {
                    btn.disabled = originalDisabled;
                }
            });
        })();

        // 10. Render Lucide icons / clear chart skeletons after the DOM is ready
        function lwaFinishLoading() {
            window.lwaRenderIcons();
            if (window.lwaInitLiveMap) window.lwaInitLiveMap();
            setTimeout(function() {
                document.querySelectorAll('.lwa-skeleton').forEach(function(el) { el.remove(); });
            }, 350);
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', lwaFinishLoading);
        } else {
            lwaFinishLoading();
        }
    </script>
