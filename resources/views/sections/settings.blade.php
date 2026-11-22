                <!-- ============================================== -->
                <!-- TAB 6: SETTING                                 -->
                <!-- ============================================== -->
                <div id="tab-settings" class="tab-pane space-y-4">
                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg shadow-sm overflow-hidden">
                        <div class="p-3.5 border-b border-slate-100 dark:border-slate-800">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Analytics Settings</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Current package configuration (read-only)</div>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @php
                                $settingRows = [
                                    'Tracking'          => config('laravel-web-analytics.enabled', true) ? 'Enabled' : 'Disabled',
                                    'Data source'       => ($source ?? 'database') === 'file' ? 'Server log file' : 'Database table',
                                    'Storage'           => $logPath ?? '-',
                                    'Records'           => $logSize ?? '-',
                                    'Time range'        => $timeRange ?? '24h',
                                    'Active visitor window' => config('laravel-web-analytics.active_visitor_window', 5) . ' minutes',
                                    'Live stream interval'  => config('laravel-web-analytics.stream_interval', 2) . ' seconds',
                                    'Log retention'     => config('laravel-web-analytics.retention_days', 30) . ' days',
                                    'GeoIP provider'    => config('laravel-web-analytics.geoip.enabled', true)
                                        ? config('laravel-web-analytics.geoip.provider', 'cloudflare') : 'Disabled',
                                    'Alerts'            => config('laravel-web-analytics.alerts.enabled', false)
                                        ? ucfirst(config('laravel-web-analytics.alerts.channel', 'telegram')) : 'Disabled',
                                    'Multi-tenancy'     => config('laravel-web-analytics.multi_tenancy.enabled', false) ? 'Enabled' : 'Disabled',
                                    'Tenant scope'      => !empty($tenantId) ? $tenantId : 'All tenants',
                                ];
                            @endphp
                            @foreach($settingRows as $label => $value)
                                <div class="flex items-center justify-between px-4 py-2.5 text-xs gap-4">
                                    <span class="text-slate-500 dark:text-slate-400">{{ $label }}</span>
                                    <span class="font-mono text-slate-800 dark:text-slate-200 text-right truncate max-w-[60%]">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Quick actions</div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('lwa_export', array_filter(['range' => $timeRange ?? '24h', 'tenant_id' => $tenantId ?? null, 'start_date' => $customStart ?? null, 'end_date' => $customEnd ?? null])) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm transition">
                                <i class="bx bx-download text-sm text-slate-400"></i> Export CSV
                            </a>
                            <button type="button" onclick="switchDataSource('{{ ($source ?? 'database') === 'file' ? 'database' : 'file' }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-md shadow-sm transition">
                                <i class="bx bx-transfer text-sm text-slate-400"></i> Switch to {{ ($source ?? 'database') === 'file' ? 'Database' : 'Server log file' }}
                            </button>
                        </div>
                    </div>
                </div>
