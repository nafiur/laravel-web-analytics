    <!-- ============ SCRIPTS ============ -->
    <script>
        // 1. Dark Mode Toggle
        (function() {
            const toggle = document.getElementById('themeToggle');
            const icon = document.getElementById('themeIcon');
            const isDark = localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            
            if (isDark) {
                document.documentElement.classList.add('dark');
                if (icon) icon.className = 'bx bx-sun text-lg';
            } else {
                document.documentElement.classList.remove('dark');
                if (icon) icon.className = 'bx bx-moon text-lg';
            }

            if (toggle) {
                toggle.addEventListener('click', () => {
                    const darkNow = document.documentElement.classList.toggle('dark');
                    localStorage.theme = darkNow ? 'dark' : 'light';
                    if (icon) icon.className = darkNow ? 'bx bx-sun text-lg' : 'bx bx-moon text-lg';
                    window.dispatchEvent(new Event('theme-changed'));
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

        // 3. Tab Switching (segmented controls + sidebar links)
        (function() {
            const tabs = document.querySelectorAll('.cf-tab-btn');
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            const overviewLink = document.getElementById('sidebarOverview');
            const panes = document.querySelectorAll('.tab-pane');

            function activateTab(target) {
                const effective = target === '#overview' ? '#tab-requests' : target;
                const pane = document.querySelector(effective);
                if (!pane) return;

                panes.forEach(p => p.classList.remove('active'));
                pane.classList.add('active');

                tabs.forEach(t => t.classList.toggle('active', t.getAttribute('data-target') === effective));
                sidebarLinks.forEach(l => l.classList.toggle('active', l.getAttribute('data-sidebar-tab') === target));
                if (overviewLink) overviewLink.classList.toggle('active', target === '#overview');

                // Trigger resize for ApexCharts inside newly visible tab
                window.dispatchEvent(new Event('resize'));
            }

            tabs.forEach(btn => {
                btn.addEventListener('click', () => {
                    const target = btn.getAttribute('data-target');
                    activateTab(target);
                    history.replaceState(null, '', target);
                });
            });

            sidebarLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    const target = link.getAttribute('data-sidebar-tab');
                    if (!target || !document.querySelector(target)) return;
                    e.preventDefault();
                    activateTab(target);
                    history.replaceState(null, '', target);
                    document.getElementById('appSidebar')?.classList.add('-translate-x-full');
                    document.getElementById('sidebarBackdrop')?.classList.add('hidden');
                });
            });

            window.addEventListener('hashchange', () => activateTab(window.location.hash || '#overview'));
            activateTab(window.location.hash || '#overview');
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
        const requestsData = {!! json_encode($analytics['time_series']['requests'] ?? [0]) !!};
        const bandwidthData = {!! json_encode($analytics['time_series']['bandwidth_mb'] ?? [0]) !!};
        const latencyData = {!! json_encode($analytics['time_series']['latency'] ?? [0]) !!};
        const visitorsData = {!! json_encode($analytics['sparklines']['visitors'] ?? $analytics['time_series']['visitors'] ?? [0]) !!};

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

        // 1. Requests Over Time (Area Chart matching Cloudflare)
        let chartRequests = new ApexCharts(document.querySelector("#chartRequestsOverTime"), {
            series: [{ name: 'Requests', data: requestsData }],
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['#0051c3'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95, 100] }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'straight', width: 2 },
            xaxis: {
                categories: timeLabels,
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } }
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: {
                theme: isDarkMode() ? 'dark' : 'light',
                y: { formatter: (val) => Number(val).toLocaleString() + ' reqs' }
            }
        });
        chartRequests.render();

        // 2. Data Transfer (Area Chart matching Cloudflare)
        let chartBandwidth = new ApexCharts(document.querySelector("#chartDataTransfer"), {
            series: [{ name: 'Data Transfer (MB)', data: bandwidthData }],
            chart: {
                type: 'area',
                height: 250,
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['#0051c3'],
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 95, 100] }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'straight', width: 2 },
            xaxis: {
                categories: timeLabels,
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '10px', colors: '#94a3b8' },
                    formatter: (val) => val + ' MB'
                }
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: {
                theme: isDarkMode() ? 'dark' : 'light',
                y: { formatter: (val) => val + ' MB' }
            }
        });
        chartBandwidth.render();

        // 3. Status Code Stacked Column Chart
        let chartStatus = new ApexCharts(document.querySelector("#chartStatusCodeBars"), {
            series: [
                { name: '2xx Success', data: status2xx },
                { name: '3xx Redirect', data: status3xx },
                { name: '4xx Client Err', data: status4xx },
                { name: '5xx Server Err', data: status5xx }
            ],
            chart: {
                type: 'bar',
                height: 250,
                stacked: true,
                toolbar: { show: false }
            },
            colors: ['#10b981', '#06b6d4', '#f43f5e', '#a855f7'],
            plotOptions: {
                bar: { columnWidth: '45%', borderRadius: 2 }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: timeLabels,
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } },
                axisBorder: { show: false }
            },
            yaxis: {
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } }
            },
            legend: { show: false },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: { theme: isDarkMode() ? 'dark' : 'light' }
        });
        chartStatus.render();

        // 4. Method Breakdown Bar Chart
        let chartMethod = new ApexCharts(document.querySelector("#chartMethodBars"), {
            series: [{
                name: 'Requests',
                data: methodBreakdown.map(m => m.count)
            }],
            chart: {
                type: 'bar',
                height: 250,
                toolbar: { show: false }
            },
            colors: ['#3b82f6'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    horizontal: true,
                    barHeight: '40%'
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: methodBreakdown.map(m => m.name),
                labels: { style: { fontSize: '10px', colors: '#94a3b8' } }
            },
            yaxis: {
                labels: { style: { fontSize: '11px', colors: '#94a3b8' } }
            },
            grid: {
                borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9',
                strokeDashArray: 3
            },
            tooltip: { theme: isDarkMode() ? 'dark' : 'light' }
        });
        chartMethod.render();

        // 5. Origin Latency Chart
        let chartLatencyEl = document.querySelector("#chartLatency");
        if (chartLatencyEl) {
            let chartLatency = new ApexCharts(chartLatencyEl, {
                series: [{ name: 'Latency (ms)', data: latencyData }],
                chart: { type: 'line', height: 250, toolbar: { show: false } },
                colors: ['#f59e0b'],
                stroke: { curve: 'smooth', width: 2 },
                xaxis: { categories: timeLabels, labels: { style: { fontSize: '10px', colors: '#94a3b8' } } },
                yaxis: { labels: { formatter: (val) => val + ' ms', style: { fontSize: '10px', colors: '#94a3b8' } } },
                grid: { borderColor: isDarkMode() ? '#1f2937' : '#f1f5f9', strokeDashArray: 3 },
                tooltip: { theme: isDarkMode() ? 'dark' : 'light' }
            });
            chartLatency.render();
        }

        // Mini Sparklines
        function renderSparkline(selector, data, color) {
            let el = document.querySelector(selector);
            if (!el) return;
            new ApexCharts(el, {
                series: [{ data: data && data.length ? data : [0] }],
                chart: { type: 'line', height: 28, sparkline: { enabled: true } },
                colors: [color],
                stroke: { curve: 'smooth', width: 1.5 },
                tooltip: { fixed: { enabled: false }, x: { show: false } }
            }).render();
        }

        renderSparkline('#sparklineRequests', requestsData, '#0051c3');
        renderSparkline('#sparklineBytes', bandwidthData, '#0051c3');
        renderSparkline('#sparklineVisits', visitorsData, '#0051c3');

        // ============ REAL-TIME SSE STREAM ============
        (function() {
            let lastTrafficId = {{ \Nafiur\WebAnalytics\Models\LwaLogs::forTenant($tenantId ?? null)->max('id') ?? 0 }};
            let sseSource = null;
            let currentTenantId = {!! json_encode((string) ($tenantId ?? '')) !!};

            function startSSE() {
                if (!window.EventSource) return;
                if (sseSource) sseSource.close();

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
                            parsed.logs.forEach(prependLiveFeedRow);
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
                let stBadge = st >= 500 ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400' 
                            : (st >= 400 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400'
                            : (st >= 300 ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-400'
                            : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400'));

                let cleanMethod = escapeHtml(log.method || 'GET');
                let cleanUser = escapeHtml(log.user_name || 'Guest');
                let cleanPath = escapeHtml(log.path || '/');
                let cleanIp = escapeHtml(log.ip_address || '-');
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
                    <td class="py-2.5 px-4 font-mono text-slate-600 dark:text-slate-300">${parseFloat(log.response_time_ms || 0)} ms</td>
                    <td class="py-2.5 px-4 text-slate-500">${cleanBytes}</td>
                    <td class="py-2.5 px-4 text-slate-500 dark:text-slate-400 truncate max-w-[140px]">${cleanClient}</td>
                    <td class="py-2.5 px-4 text-right text-slate-400">just now</td>
                `;

                tbody.insertBefore(tr, tbody.firstChild);
                setTimeout(() => { tr.className = 'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-all duration-1000'; }, 1500);

                while (tbody.children.length > 30) {
                    tbody.removeChild(tbody.lastChild);
                }

                const clock = document.getElementById('lastUpdatedClock');
                if (clock) clock.textContent = 'Hit: ' + log.path + ' (' + new Date().toLocaleTimeString() + ')';
            }
        })();
    </script>
</body>
</html>
