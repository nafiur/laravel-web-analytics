    <!-- ============ SCRIPTS ============ -->
    <script>
        const lwaStreamMode = {!! json_encode(config('laravel-web-analytics.stream.mode', 'sse')) !!};
        const lwaPollingInterval = {!! json_encode(config('laravel-web-analytics.stream.polling_interval', 5)) !!};
        const lwaDataUrl = "{{ route('lwa_data') }}";
        const lwaInitialMapMarkers = {!! json_encode(collect($analytics['recent_logs'] ?? [])
            ->filter(fn ($log) => !empty($log['latitude']) && !empty($log['longitude']))
            ->map(fn ($log) => [
                'lat'   => $log['latitude'],
                'lng'   => $log['longitude'],
                'label' => $log['city'] ?: ($log['country'] ?? 'Visitor'),
                'flag'  => $log['flag'] ?? '',
            ])->values()->all()) !!};

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

                // Deep link from a breakdown row: ?filter=<name>
                const preselected = new URLSearchParams(window.location.search).get('filter');
                if (preselected) {
                    input.value = preselected;
                    input.classList.remove('hidden');
                    input.dispatchEvent(new Event('keyup'));
                }
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
        const deviceOverTime = {!! json_encode($analytics['time_series']['device_over_time'] ?? ['labels' => [], 'series' => []]) !!};
        const deviceColors = { Desktop: '#0051c3', Mobile: '#f59e0b', Tablet: '#8b5cf6', Bot: '#94a3b8' };

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
        if (overviewChartEl && window.ApexCharts) {
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


        // 3b. Device types over time (stacked columns, only on Devices & Browsers)
        let chartDeviceTypes = null;
        const deviceChartEl = document.querySelector("#chartDeviceTypes");
        if (deviceChartEl && window.ApexCharts) {
            const deviceSeries = (deviceOverTime.series || []).map(s => ({
                name: s.name,
                data: s.data || []
            }));
            chartDeviceTypes = new ApexCharts(deviceChartEl, {
                series: deviceSeries,
                chart: { type: 'bar', height: 250, stacked: true, toolbar: { show: false }, zoom: { enabled: false } },
                colors: deviceSeries.map(s => deviceColors[s.name] || '#0051c3'),
                plotOptions: { bar: { columnWidth: '45%', borderRadius: 2 } },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: deviceOverTime.labels || timeLabels,
                    labels: { style: { fontSize: '10px', colors: '#94a3b8' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: { labels: { style: { fontSize: '10px', colors: '#94a3b8' } } },
                legend: { show: false },
                grid: { borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9', strokeDashArray: 3 },
                tooltip: { theme: isDarkMode() ? 'dark' : 'light', y: { formatter: val => Number(val).toLocaleString() } }
            });
            chartDeviceTypes.render();
        }

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
        if (chartLatencyEl && window.ApexCharts) {
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
            if (!el || !window.ApexCharts) return;
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
        window.lwaTimeCharts = [chartOverviewTraffic, chartRequests, chartBandwidth, chartStatus, chartDeviceTypes, chartLatency].filter(Boolean);
        window.lwaChartRegistry = {
            chartOverviewTraffic: chartOverviewTraffic,
            chartRequestsOverTime: chartRequests,
            chartBandwidth: chartBandwidth,
            chartStatus: chartStatus,
            chartDeviceTypes: chartDeviceTypes,
            chartLatency: chartLatency,
        };
        window.lwaBreakdownTrends = {!! json_encode($analytics['breakdown_trends'] ?? []) !!};

        // ============ OFFLINE GEOIP VISITOR MAPS ============
        (function() {
            const maps = {};
            const counts = {};

            window.lwaInitLiveMap = function() {
                if (!window.jsVectorMap) return;

                document.querySelectorAll('[data-lwa-map]').forEach(function(el) {
                    const id = el.getAttribute('data-lwa-map');
                    if (!id || maps[id]) return;

                    try {
                        let highlighted = [];
                        try {
                            highlighted = JSON.parse(el.getAttribute('data-highlight-countries') || '[]');
                        } catch (err) { highlighted = []; }

                        maps[id] = new jsVectorMap({
                            selector: '#' + id,
                            map: 'world',
                            zoomButtons: true,
                            selectedRegions: highlighted,
                            regionStyle: {
                                initial: { fill: '#cbd5e1', stroke: '#ffffff', strokeWidth: 0.5 },
                                hover: { fill: '#94a3b8' },
                                selected: { fill: '#f6821f' }
                            },
                            markerStyle: { initial: { fill: '#4f46e5', stroke: '#ffffff', strokeWidth: 1, r: 5 } },
                            markers: []
                        });
                        counts[id] = 0;

                        (window.lwaInitialMapMarkers || []).forEach(function(marker) {
                            window.lwaAddMapMarker(marker.lat, marker.lng, marker.label, id, marker.flag);
                        });
                    } catch (err) { /* map library unavailable */ }
                });
            };

            window.lwaAddMapMarker = function(lat, lng, label, targetId, flag) {
                if (!lat || !lng) return;

                Object.keys(maps).forEach(function(id) {
                    if (targetId && id !== targetId) return;
                    try {
                        const name = (flag ? flag + ' ' : '') + (label || 'Visitor');
                        maps[id].addMarker({ latLng: [Number(lat), Number(lng)], name: name });
                        counts[id] = (counts[id] || 0) + 1;
                        const count = document.getElementById(id + 'Count');
                        if (count) count.textContent = counts[id] + ' located';
                        document.querySelectorAll('.lwa-map-empty').forEach(function (el) { el.classList.add('hidden'); });
                    } catch (err) { /* ignore bad coordinates */ }
                });
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
                                            window.lwaAddMapMarker(log.latitude, log.longitude, log.city || log.country || log.ip_address, null, log.flag);
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
                const streamParams = new URLSearchParams(window.location.search);
                const streamRange = streamParams.get('range') || "{{ $timeRange ?? '24h' }}";
                streamUrl += "&range=" + encodeURIComponent(streamRange);
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
                                    window.lwaAddMapMarker(log.latitude, log.longitude, log.city || log.country || log.ip_address, null, log.flag);
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

                sseSource.addEventListener('analytics', function(e) {
                try {
                    const parsed = JSON.parse(e.data);
                    if (parsed.analytics && window.lwaApplyAnalytics) window.lwaApplyAnalytics(parsed.analytics);
                } catch (err) { /* ignore malformed payload */ }
            });

            sseSource.addEventListener('ping', function(e) {
                    try {
                        let p = JSON.parse(e.data);
                        if (p.last_id) lastTrafficId = p.last_id;
                        const clock = document.getElementById('lastUpdatedClock');
                        if (clock) clock.textContent = 'Listening — ' + new Date().toLocaleTimeString();
                    } catch (err) {}
                });

                sseSource.onopen = function() {
                    connectionFailures = 0;
                    setConnectionState('connected');
                };

                sseSource.onerror = function() {
                    connectionFailures++;
                    // A short-lived response ends with EOF, which browsers report
                    // as an error on every normal reconnect. Do not flash
                    // "Reconnecting" for a single EOF — only for repeated failures.
                    if (connectionFailures >= 4) {
                        setConnectionState('disconnected');
                    } else if (connectionFailures >= 2) {
                        setConnectionState('reconnecting');
                    }
                    // first failure: keep the existing "Connected" state
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

            function lwaFlagHtml(country, emoji) {
                if (!emoji) return '';
                const code = String(country || '').trim().slice(0, 2).toUpperCase();
                if (/^[A-Z]{2}$/.test(code)) {
                    return '<img src="https://flagcdn.com/w20/' + code.toLowerCase() + '.png" width="18" height="12" alt="' + code + '" class="inline-block rounded-sm shrink-0 align-middle" loading="lazy" onerror="this.style.display=\'none\'">';
                }
                return escapeHtml(emoji);
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
                    <td class="py-2.5 px-4 font-mono text-slate-500">${cleanIp}</td>
                    <td class="py-2.5 px-4 whitespace-nowrap text-slate-600 dark:text-slate-300">
                        <span class="mr-1">${lwaFlagHtml(log.country, log.flag)}</span>
                        <span class="font-mono text-[11px]">${escapeHtml(log.country || 'Unknown')}</span>
                    </td>
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
                en: { code: 'us', emoji: '🇺🇸' }, bn: { code: 'bd', emoji: '🇧🇩' },
                hi: { code: 'in', emoji: '🇮🇳' }, es: { code: 'es', emoji: '🇪🇸' },
                fr: { code: 'fr', emoji: '🇫🇷' }, de: { code: 'de', emoji: '🇩🇪' },
                pt: { code: 'pt', emoji: '🇵🇹' }, ar: { code: 'sa', emoji: '🇸🇦' },
                zh: { code: 'cn', emoji: '🇨🇳' }, ja: { code: 'jp', emoji: '🇯🇵' },
                ur: { code: 'pk', emoji: '🇵🇰' }, id: { code: 'id', emoji: '🇮🇩' }
            };

            function applyLanguage(lang) {
                const dict = dictionary[lang] || dictionary.en;
                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    if (dict[key]) el.textContent = dict[key];
                });
                const flag = document.getElementById('languageFlag');
                if (flag) {
                    const meta = languageFlags[lang] || languageFlags.en;
                    flag.innerHTML = '<img src="https://flagcdn.com/w20/' + meta.code + '.png" width="18" height="12" alt="' + meta.code + '" class="inline-block rounded-sm align-middle" loading="lazy" onerror="this.outerHTML=\'' + meta.emoji + '\'">';
                }
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
                try {
                    if (window.lwaRefreshAnalytics) await window.lwaRefreshAnalytics();
                    else window.location.reload();
                } finally {
                    btn.disabled = originalDisabled;
                }
            });
        })();

        // 9b. Reusable card menus: View as, Export, Observability, pin, expand
        (function() {
            function closeAllMenus() {
                document.querySelectorAll('[data-lwa-card-menu-panel]:not(.hidden)').forEach(function(panel) {
                    panel.classList.add('hidden');
                    const toggle = panel.closest('[data-lwa-card-tools]')?.querySelector('[data-lwa-card-menu-toggle]');
                    if (toggle) toggle.setAttribute('aria-expanded', 'false');
                });
            }

            function csvCell(value) {
                let text = String(value ?? '');
                // Spreadsheet formula injection guard (UA strings are user data).
                if (/^[=+\-@\t\r]/.test(text)) text = "'" + text;
                return '"' + text.replace(/"/g, '""') + '"';
            }

            function download(filename, content, mime) {
                const blob = new Blob([content], { type: mime });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }

            function pinnedWidgets() {
                try {
                    const stored = JSON.parse(localStorage.getItem('lwa_pinned_widgets') || '[]');
                    return Array.isArray(stored) ? stored : [];
                } catch (err) { return []; }
            }

            function applyPinned() {
                const pinned = pinnedWidgets();
                document.querySelectorAll('[data-lwa-pin-slot]').forEach(function(slot) {
                    slot.classList.toggle('hidden', !pinned.includes(slot.dataset.lwaPinSlot));
                });
                const wrap = document.getElementById('lwaPinnedReports');
                if (wrap) {
                    const anyVisible = !!wrap.querySelector('[data-lwa-pin-slot]:not(.hidden)');
                    wrap.classList.toggle('hidden', !anyVisible);
                }
                document.querySelectorAll('[data-lwa-card-tools][data-card-id]').forEach(function(tools) {
                    const button = tools.querySelector('[data-lwa-pin]');
                    if (!button) return;
                    const active = pinned.includes(tools.dataset.cardId);
                    const label = button.querySelector('[data-pin-label]');
                    if (label) label.textContent = active ? 'Remove from dashboard' : 'Add to dashboard';
                    button.classList.toggle('text-blue-600', active);
                    const icon = button.querySelector('i[data-lucide]');
                    if (icon) icon.setAttribute('data-lucide', active ? 'pin-off' : 'pin');
                });
                if (window.lwaRenderIcons) window.lwaRenderIcons();
            }

            document.querySelectorAll('[data-lwa-card]').forEach(function(card) {
                const id = card.dataset.cardId;
                if (!id) return;
                const mode = localStorage.getItem('lwa_view_' + id);
                if (mode === 'proportion' || mode === 'trend') card.dataset.mode = mode;
                if (localStorage.getItem('lwa_expanded_' + id) === '1') card.classList.add('lwa-card-expanded');
            });

            document.querySelectorAll('[data-lwa-card-tools]').forEach(function(tools) {
                const cardId = tools.dataset.cardId;
                const card = tools.closest('[data-lwa-card]');
                const toggle = tools.querySelector('[data-lwa-card-menu-toggle]');
                const panel = tools.querySelector('[data-lwa-card-menu-panel]');

                if (toggle && panel) {
                    toggle.addEventListener('click', function(event) {
                        event.stopPropagation();
                        const willOpen = panel.classList.contains('hidden');
                        closeAllMenus();
                        panel.classList.toggle('hidden', !willOpen);
                        toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                    });
                }
                if (!panel) return;

                panel.querySelectorAll('[data-view-mode]').forEach(function(button) {
                    button.addEventListener('click', function() {
                        if (!card) return;
                        card.dataset.mode = button.dataset.viewMode;
                        localStorage.setItem('lwa_view_' + cardId, button.dataset.viewMode);
                        closeAllMenus();
                    });
                });

                panel.querySelectorAll('[data-lwa-export]').forEach(function(button) {
                    button.addEventListener('click', function() {
                        let data = {};
                        const source = document.getElementById('lwa-card-data-' + cardId);
                        try { data = JSON.parse(source?.textContent || '{}'); } catch (err) { data = {}; }

                        if (button.dataset.lwaExport === 'json') {
                            download(cardId + '.json', JSON.stringify(data, null, 2), 'application/json');
                        } else {
                            const rows = [['section', 'name', 'count', 'percentage']];
                            Object.keys(data).forEach(function(section) {
                                const value = data[section];
                                if (Array.isArray(value)) {
                                    value.forEach(function(item) {
                                        rows.push([section, item.name, item.count, item.percentage]);
                                    });
                                } else if (value && Array.isArray(value.series)) {
                                    (value.series || []).forEach(function(series) {
                                        (series.data || []).forEach(function(count, index) {
                                            rows.push([section, ((value.labels || [])[index] || index) + ' / ' + series.name, count, '']);
                                        });
                                    });
                                }
                            });
                            download(cardId + '.csv', '\ufeff' + rows.map(row => row.map(csvCell).join(',')).join('\r\n'), 'text/csv;charset=utf-8');
                        }
                        closeAllMenus();
                    });
                });

                const pinButton = panel.querySelector('[data-lwa-pin]');
                if (pinButton) {
                    pinButton.addEventListener('click', function() {
                        const list = pinnedWidgets();
                        const index = list.indexOf(cardId);
                        if (index >= 0) list.splice(index, 1); else list.push(cardId);
                        localStorage.setItem('lwa_pinned_widgets', JSON.stringify(list));
                        applyPinned();
                        closeAllMenus();
                    });
                }

            });

            document.addEventListener('click', function(event) {
                if (!event.target.closest('[data-lwa-card-tools]')) closeAllMenus();
            });
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') closeAllMenus();
            });
            applyPinned();
        })();

        // 9c. Breakdown detail modal (search / sort / limit / row actions)
        (function() {
            const modal = document.querySelector('[data-lwa-breakdown-modal]');
            if (!modal) return;

            const body = modal.querySelector('[data-lwa-modal-body]');
            const emptyEl = modal.querySelector('[data-lwa-modal-empty]');
            const search = modal.querySelector('[data-lwa-modal-search]');
            const sortSelect = modal.querySelector('[data-lwa-modal-sort]');
            const limitSelect = modal.querySelector('[data-lwa-modal-limit]');
            const titleEl = modal.querySelector('[data-lwa-modal-title]');
            const subtitleEl = modal.querySelector('[data-lwa-modal-subtitle]');
            const observabilityUrl = modal.dataset.observabilityUrl || '#';
            let rows = [];

            function escapeHtml(value) {
                return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function renderRows() {
                const term = search.value.trim().toLowerCase();
                let visible = rows.filter(item => String(item.name ?? '').toLowerCase().includes(term));
                const mode = sortSelect.value;
                if (mode === 'value') {
                    visible.sort((a, b) => (Number(b.count) || 0) - (Number(a.count) || 0));
                } else if (mode === 'name') {
                    visible.sort((a, b) => String(a.name).localeCompare(String(b.name)));
                } else {
                    visible.sort((a, b) => (Number(b.percentage) || 0) - (Number(a.percentage) || 0));
                }
                const limit = parseInt(limitSelect.value, 10) || 25;
                visible = visible.slice(0, limit);

                body.innerHTML = visible.map((item, index) => {
                    const pct = Math.min(100, Math.max(2, Number(item.percentage) || 0));
                    const icon = item.icon || '';
                    const name = escapeHtml(item.name);
                    return '<tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">' +
                        '<td class="px-4 py-2"><input type="checkbox" class="lwa-row-check" aria-label="Select ' + name + '"></td>' +
                        '<td class="px-4 py-2"><span class="flex items-center gap-2 min-w-0 truncate" title="' + name + '">' + icon + '<span class="truncate">' + name + '</span></span></td>' +
                        '<td class="px-4 py-2"><div class="flex items-center gap-2"><div class="flex-1 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden"><div class="bg-[#0051c3] dark:bg-[#38bdf8] h-full rounded-full" style="width:' + pct + '%"></div></div><span class="w-10 text-right text-slate-400">' + pct.toFixed(1) + '%</span></div></td>' +
                        '<td class="px-4 py-2 text-right font-mono text-slate-600 dark:text-slate-300">' + escapeHtml(item.count_fmt ?? item.count ?? 0) + '</td>' +
                        '<td class="px-2 py-2 text-right relative">' +
                            '<button type="button" data-row-menu="' + index + '" class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Row options">...</button>' +
                            '<div data-row-menu-panel="' + index + '" class="hidden absolute right-2 top-full z-10 w-44 rounded-md border lwa-border bg-white dark:bg-slate-800 shadow-lg py-1 text-left">' +
                                '<a href="' + observabilityUrl + (observabilityUrl.includes('?') ? '&' : '?') + 'filter=' + encodeURIComponent(item.name) + '" class="block px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">View in Observability</a>' +
                                '<button type="button" data-row-copy="' + escapeHtml(item.name) + '" class="block w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">Copy name</button>' +
                            '</div>' +
                        '</td></tr>';
                }).join('');
                emptyEl.classList.toggle('hidden', visible.length > 0);
                const all = modal.querySelector('[data-modal-select-all]');
                if (all) all.checked = false;
                if (window.lwaRenderIcons) window.lwaRenderIcons();
            }

            function open(title, items, subtitle) {
                rows = Array.isArray(items) ? items : [];
                titleEl.textContent = title || 'Breakdown';
                subtitleEl.textContent = subtitle || (rows.length + ' items');
                search.value = '';
                sortSelect.value = 'proportion';
                limitSelect.value = '25';
                modal.classList.remove('hidden');
                renderRows();
            }

            function close() {
                modal.classList.add('hidden');
            }

            document.addEventListener('click', function(event) {
                const listOpener = event.target.closest('[data-lwa-open-detail]');
                if (listOpener) {
                    const list = listOpener.closest('[data-lwa-breakdown-list]');
                    let items = [];
                    try { items = JSON.parse(list?.dataset.lwaItems || '[]'); } catch (err) { items = []; }
                    open(list?.dataset.lwaTitle || 'Breakdown', items);
                    return;
                }

                const cardOpener = event.target.closest('[data-lwa-card-expand]');
                if (cardOpener) {
                    const tools = cardOpener.closest('[data-lwa-card-tools]');
                    const card = cardOpener.closest('[data-lwa-card]');
                    const id = tools?.dataset.cardId;
                    let data = {};
                    const source = document.getElementById('lwa-card-data-' + id);
                    try { data = JSON.parse(source?.textContent || '{}'); } catch (err) { data = {}; }
                    const values = Object.values(data).find(value => Array.isArray(value)) || [];
                    const heading = card?.querySelector('.text-xs.font-semibold') || card?.querySelector('h2');
                    open(heading?.textContent?.trim() || 'Breakdown', values);
                    return;
                }

                if (event.target.closest('[data-lwa-modal-close]') || event.target.closest('[data-lwa-modal-backdrop]')) {
                    close();
                    return;
                }

                const rowMenu = event.target.closest('[data-row-menu]');
                if (rowMenu) {
                    const panel = body.querySelector('[data-row-menu-panel="' + rowMenu.dataset.rowMenu + '"]');
                    const willOpen = panel?.classList.contains('hidden');
                    body.querySelectorAll('[data-row-menu-panel]').forEach(p => p.classList.add('hidden'));
                    panel?.classList.toggle('hidden', !willOpen);
                    return;
                }

                const copy = event.target.closest('[data-row-copy]');
                if (copy) {
                    try { navigator.clipboard?.writeText(copy.dataset.rowCopy); } catch (err) { /* clipboard unavailable */ }
                    body.querySelectorAll('[data-row-menu-panel]').forEach(p => p.classList.add('hidden'));
                }
            });

            search.addEventListener('input', renderRows);
            sortSelect.addEventListener('change', renderRows);
            limitSelect.addEventListener('change', renderRows);
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') close();
            });
            modal.addEventListener('change', function(event) {
                if (event.target.matches('[data-modal-select-all]')) {
                    body.querySelectorAll('.lwa-row-check').forEach(check => { check.checked = event.target.checked; });
                }
            });
        })();

        // 9d. Group by on time-series charts
        (function() {
            const trends = window.lwaBreakdownTrends || {};
            const palette = ['#0051c3', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444', '#06b6d4'];
            const defaults = {
                chartOverviewTraffic: [{ name: 'Requests', data: requestsData }],
                chartRequestsOverTime: [{ name: 'Requests', data: requestsData }],
            };

            document.querySelectorAll('[data-lwa-group-by]').forEach(function(select) {
                select.addEventListener('change', function() {
                    const chartId = select.dataset.chartId;
                    const chart = window.lwaChartRegistry[chartId];
                    if (!chart) return;

                    const key = select.value;
                    if (!key) {
                        chart.updateSeries(defaults[chartId] || [{ name: 'Requests', data: requestsData }]);
                        chart.updateOptions({ colors: ['#0051c3'], legend: { show: false } });
                        return;
                    }

                    const trend = trends[key] || {};
                    const series = (trend.series || []).slice(0, 6).map(function(item) {
                        return { name: item.name, data: item.data || [] };
                    });
                    if (!series.length) {
                        series.push({ name: 'No data', data: requestsData.map(function() { return 0; }) });
                    }
                    chart.updateSeries(series);
                    chart.updateOptions({ colors: palette, legend: { show: series.length > 1, position: 'bottom' } });
                });
            });
        })();

        // 9e. Live analytics updates: polling + SSE payloads
        (function() {
            const dataUrl = "{{ route('lwa_data') }}";
            const autoSelect = document.getElementById('lwaAutoRefresh');
            let timer = null;
            let inFlight = false;

            function esc(value) {
                return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }

            function proportionRow(item) {
                const pct = Math.min(100, Math.max(2, Number(item.percentage) || 0));
                const mark = item.icon || (item.flag ? '<span aria-hidden="true">' + esc(item.flag) + '</span>' : '');
                return '<div class="flex items-center justify-between text-xs py-1 border-b border-slate-100 dark:border-slate-800/80 last:border-0 px-1 rounded">' +
                    '<span class="flex items-center gap-1.5 min-w-0 truncate max-w-[180px] sm:max-w-[280px] font-mono text-slate-700 dark:text-slate-300" title="' + esc(item.name) + '">' +
                        mark + '<span class="truncate">' + esc(item.name) + '</span></span>' +
                    '<div class="flex items-center gap-3 w-36 sm:w-48 shrink-0">' +
                        '<div class="flex-1 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden"><div class="bg-[#0051c3] dark:bg-[#38bdf8] h-full rounded-full" style="width:' + pct + '%"></div></div>' +
                        '<span class="text-right w-14 font-mono text-slate-600 dark:text-slate-400">' + esc(item.count_fmt ?? item.count ?? 0) + '</span>' +
                    '</div></div>';
            }

            function trendRow(item) {
                const data = item.data || [];
                const max = Math.max(1, ...data.map(Number));
                const points = data.map((value, index) =>
                    (data.length > 1 ? (index / (data.length - 1)) * 100 : 0).toFixed(2) + ',' +
                    (22 - (Number(value) / max) * 18).toFixed(2)).join(' ');
                return '<div class="flex items-center justify-between text-xs py-1 border-b border-slate-100 dark:border-slate-800/80 last:border-0 px-1 rounded">' +
                    '<span class="flex items-center gap-1.5 min-w-0 truncate max-w-[180px] sm:max-w-[280px] font-mono text-slate-700 dark:text-slate-300" title="' + esc(item.name) + '"><span class="truncate">' + esc(item.name) + '</span></span>' +
                    '<div class="flex items-center gap-3 w-36 sm:w-48 shrink-0">' +
                        '<svg viewBox="0 0 100 24" preserveAspectRatio="none" class="h-5 flex-1 text-[#0051c3] dark:text-[#38bdf8]" aria-hidden="true"><polyline points="' + points + '" fill="none" stroke="currentColor" stroke-width="1.6" vector-effect="non-scaling-stroke"/></svg>' +
                        '<span class="text-right w-14 font-mono text-slate-600 dark:text-slate-400">' + data.reduce((a, b) => a + Number(b), 0).toLocaleString() + '</span>' +
                    '</div></div>';
            }

            function updateList(container, html) {
                const target = container.querySelector(':scope > .space-y-2') || container;
                target.innerHTML = html || '<div class="text-xs text-slate-400 py-3 text-center">No data for this period</div>';
            }

            function applyCharts(analytics) {
                const seriesOf = (key, name) => [{ name: name, data: (analytics.time_series || {})[key] || [] }];
                const registry = window.lwaChartRegistry || {};
                const overview = registry.chartOverviewTraffic;
                if (overview) {
                    const select = document.querySelector('[data-lwa-group-by][data-chart-id="chartOverviewTraffic"]');
                    let series = seriesOf('requests', 'Requests');
                    if (select && select.value && analytics.breakdown_trends) {
                        const grouped = (analytics.breakdown_trends[select.value] || {}).series || [];
                        if (grouped.length) series = grouped.slice(0, 6).map(s => ({ name: s.name, data: s.data || [] }));
                    }
                    overview.updateSeries(series);
                    overview.updateOptions({ xaxis: { categories: (analytics.time_series || {}).labels || [] } });
                }
                if (registry.chartRequestsOverTime) registry.chartRequestsOverTime.updateSeries(seriesOf('requests', 'Requests'));
                if (registry.chartBandwidth) registry.chartBandwidth.updateSeries(seriesOf('bandwidth_mb', 'Data Transfer (MB)'));
                if (registry.chartStatus) {
                    registry.chartStatus.updateSeries([
                        { name: '2xx', data: (analytics.time_series || {}).status_2xx || [] },
                        { name: '3xx', data: (analytics.time_series || {}).status_3xx || [] },
                        { name: '4xx', data: (analytics.time_series || {}).status_4xx || [] },
                        { name: '5xx', data: (analytics.time_series || {}).status_5xx || [] },
                    ]);
                }
                if (registry.chartLatency) registry.chartLatency.updateSeries(seriesOf('latency', 'Latency (ms)'));

                if (registry.chartLogOutcomes) {
                    const select = document.querySelector('[data-lwa-group-by][data-chart-id="chartLogOutcomes"]');
                    let logSeries = seriesOf('requests', 'Requests');
                    if (select && select.value && analytics.breakdown_trends) {
                        const grouped = (analytics.breakdown_trends[select.value] || {}).series || [];
                        if (grouped.length) logSeries = grouped.slice(0, 6).map(s => ({ name: s.name, data: s.data || [] }));
                    }
                    registry.chartLogOutcomes.updateSeries(logSeries);
                    registry.chartLogOutcomes.updateOptions({ xaxis: { categories: (analytics.time_series || {}).labels || [] } });
                }

                const device = analytics.time_series?.device_over_time;
                if (registry.chartDeviceTypes && device) {
                    const series = (device.series || []).map(s => ({ name: s.name, data: s.data || [] }));
                    registry.chartDeviceTypes.updateSeries(series.length ? series : [{ name: 'No data', data: [] }]);
                    const colors = { Desktop: '#0051c3', Mobile: '#f59e0b', Tablet: '#8b5cf6', Bot: '#94a3b8' };
                    const legend = document.querySelector('[data-card-id="device-types-over-time"] .flex.flex-wrap');
                    if (legend) {
                        legend.innerHTML = series.map(s => {
                            const total = (s.data || []).reduce((a, b) => a + Number(b), 0).toLocaleString();
                            return '<div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">' +
                                '<span class="w-2 h-2 rounded-full" style="background:' + (colors[s.name] || '#0051c3') + '"></span>' +
                                '<span>' + esc(s.name) + '</span><span class="font-semibold text-slate-700 dark:text-slate-200">' + total + '</span></div>';
                        }).join('');
                    }
                }
            }

            window.lwaApplyAnalytics = function(analytics) {
                if (!analytics) return;
                const breakdowns = analytics.breakdowns || {};
                window.lwaBreakdownTrends = analytics.breakdown_trends || window.lwaBreakdownTrends || {};

                document.querySelectorAll('[data-lwa-breakdown-list][data-lwa-breakdown-key]').forEach(function(list) {
                    const key = list.dataset.lwaBreakdownKey;
                    if (!key || !Object.prototype.hasOwnProperty.call(breakdowns, key)) return;
                    const items = breakdowns[key] || [];
                    list.dataset.lwaItems = JSON.stringify(items);
                    updateList(list, items.map(proportionRow).join(''));
                });
                document.querySelectorAll('[data-view="trend"][data-lwa-breakdown-key]').forEach(function(container) {
                    const key = container.dataset.lwaBreakdownKey;
                    const trend = (analytics.breakdown_trends || {})[key];
                    if (!trend) return;
                    updateList(container, (trend.series || []).map(trendRow).join(''));
                });

                document.querySelectorAll('[data-lwa-card-tools][data-card-id]').forEach(function(tools) {
                    const source = document.getElementById('lwa-card-data-' + tools.dataset.cardId);
                    if (!source) return;
                    try {
                        const data = JSON.parse(source.textContent || '{}');
                        Object.keys(data).forEach(function(section) {
                            if (Array.isArray(breakdowns[section])) data[section] = breakdowns[section];
                            else if (analytics.breakdown_trends && analytics.breakdown_trends[section]) data[section] = analytics.breakdown_trends[section];
                        });
                        source.textContent = JSON.stringify(data);
                    } catch (err) { /* keep previous data */ }
                });

                const summary = analytics.summary || {};
                [['kpiRequests', 'total_requests_fmt'], ['kpiVisits', 'unique_visitors_fmt'], ['kpiBytes', 'total_bytes_fmt']]
                    .forEach(function(pair) {
                        const el = document.getElementById(pair[0]);
                        if (el && summary[pair[1]] !== undefined) el.textContent = summary[pair[1]];
                    });

                applyCharts(analytics);
                if (window.lwaRenderIcons) window.lwaRenderIcons();
                const toast = document.getElementById('lwaErrorToast');
                if (toast) toast.classList.add('hidden');
            };

            window.lwaRefreshAnalytics = async function() {
                if (inFlight) return;
                inFlight = true;
                try {
                    const url = new URL(dataUrl, window.location.origin);
                    window.location.search.replace(/^\?/, '').split('&').forEach(function(pair) {
                        if (!pair) return;
                        const parts = pair.split('=');
                        url.searchParams.set(decodeURIComponent(parts[0]), decodeURIComponent(parts.slice(1).join('=') || ''));
                    });
                    url.searchParams.set('light', '1');
                    const response = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const payload = await response.json();
                    if (!payload.success) throw new Error('Bad payload');
                    window.lwaApplyAnalytics(payload.analytics);
                } catch (err) {
                    const toast = document.getElementById('lwaErrorToast');
                    if (toast) { toast.classList.remove('hidden'); if (window.lwaRenderIcons) window.lwaRenderIcons(); }
                } finally {
                    inFlight = false;
                }
            };

            function applyTimer() {
                if (timer) { clearInterval(timer); timer = null; }
                const seconds = parseInt(autoSelect?.value || '0', 10) || 0;
                if (seconds > 0) timer = setInterval(window.lwaRefreshAnalytics, seconds * 1000);
            }
            if (autoSelect) {
                autoSelect.value = localStorage.getItem('lwa_auto_refresh') || '0';
                autoSelect.addEventListener('change', function() {
                    localStorage.setItem('lwa_auto_refresh', autoSelect.value);
                    applyTimer();
                });
                applyTimer();
            }
        })();

        // 9f. Logs page: outcomes chart, expandable rows, CSV export
        (function() {
            const el = document.querySelector('#chartLogOutcomes');
            if (el && window.ApexCharts) {
                const trend = (window.lwaBreakdownTrends || {}).top_cache_statuses || {};
                const series = (trend.series || []).map(item => ({ name: item.name, data: item.data || [] }));
                const chart = new ApexCharts(el, {
                    series: series.length ? series : [{ name: 'No data', data: [] }],
                    chart: { type: 'area', height: 224, stacked: false, toolbar: { show: false }, zoom: { enabled: false } },
                    colors: ['#0051c3', '#f59e0b', '#8b5cf6', '#10b981', '#ef4444', '#06b6d4'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'straight', width: 2 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 95, 100] } },
                    xaxis: { categories: trend.labels || [], labels: { style: { fontSize: '10px', colors: '#94a3b8' } }, axisBorder: { show: false } },
                    yaxis: { labels: { style: { fontSize: '10px', colors: '#94a3b8' } } },
                    grid: { borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9', strokeDashArray: 3 },
                    legend: { show: series.length > 1, position: 'top' },
                    tooltip: { theme: isDarkMode() ? 'dark' : 'light' }
                });
                chart.render();
                if (!window.lwaChartRegistry) window.lwaChartRegistry = {};
                window.lwaChartRegistry.chartLogOutcomes = chart;
            }

            document.querySelectorAll('[data-log-expand]').forEach(function(button) {
                button.addEventListener('click', function() {
                    const index = button.dataset.logExpand;
                    const detail = document.querySelector('[data-log-detail="' + index + '"]');
                    const icon = button.querySelector('i[data-lucide]');
                    const open = detail?.classList.toggle('hidden') === false;
                    if (icon) icon.setAttribute('data-lucide', open ? 'chevron-down' : 'chevron-right');
                    if (window.lwaRenderIcons) window.lwaRenderIcons();
                });
            });

            const chipContainer = document.getElementById('lwaLogFilterChips');
            const knownFields = ['method', 'host', 'path', 'full_url', 'status', 'ip_address', 'country', 'city',
                'device', 'os', 'browser', 'user_agent', 'session_id', 'user_id', 'cache_status', 'tenant_id',
                'referrer', 'utm_source', 'utm_medium', 'utm_campaign'];
            function addFilterChip(field, value) {
                if (!chipContainer || !knownFields.includes(field)) return;
                const nativeControl = document.querySelector('form [name="' + field + '"]');
                if (nativeControl && !chipContainer.querySelector('[data-chip="' + field + '"]')) {
                    nativeControl.focus();
                    return;
                }
                const existing = chipContainer.querySelector('[data-chip="' + field + '"]');
                if (existing) { existing.querySelector('input')?.focus(); return; }
                const chip = document.createElement('div');
                chip.className = 'flex items-center gap-1 rounded border lwa-border bg-white dark:bg-slate-800 px-1.5 py-1';
                chip.dataset.chip = field;
                const label = document.createElement('span');
                label.className = 'text-[10px] text-slate-400';
                label.textContent = field.replace(/_/g, ' ') + ':';
                const input = document.createElement('input');
                input.type = 'text';
                input.name = field;
                input.value = value || '';
                input.placeholder = 'value';
                input.className = 'w-28 text-[11px] font-mono bg-transparent text-slate-700 dark:text-slate-200 focus:outline-none';
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.dataset.chipRemove = field;
                remove.className = 'text-slate-400 hover:text-rose-500 text-sm leading-none';
                remove.textContent = '×';
                chip.append(label, input, remove);
                chipContainer.appendChild(chip);
                input.focus();
            }
            document.querySelectorAll('[data-log-field]').forEach(function(button) {
                button.addEventListener('click', function() { addFilterChip(button.dataset.logField, ''); });
            });
            chipContainer?.addEventListener('click', function(event) {
                if (event.target.closest('[data-chip-remove]')) {
                    event.target.closest('[data-chip]')?.remove();
                }
            });
            new URLSearchParams(window.location.search).forEach(function(value, key) {
                addFilterChip(key, value);
            });

            const exportButton = document.querySelector('[data-lwa-export-logs]');
            if (exportButton) {
                exportButton.addEventListener('click', function() {
                    let rows = [];
                    try { rows = JSON.parse(document.getElementById('lwaLogsData')?.textContent || '[]'); } catch (err) { rows = []; }
                    const columns = ['created_at', 'method', 'host', 'path', 'status_code', 'cache_status', 'ip_address', 'country', 'city', 'browser', 'os', 'device', 'response_time_ms', 'bytes_sent'];
                    function cell(value) {
                        let text = String(value ?? '');
                        if (/^[=+\-@\t\r]/.test(text)) text = "'" + text;
                        return '"' + text.replace(/"/g, '""') + '"';
                    }
                    const lines = [columns.join(',')].concat(rows.map(row => columns.map(column => cell(row[column])).join(',')));
                    const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'web-analytics-logs.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                });
            }
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
