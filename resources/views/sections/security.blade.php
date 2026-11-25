                    <div>
                        <h1 class="lwa-page-title">Error Monitoring</h1>
                        <p class="lwa-subtitle mt-1">Server errors and failed requests in the selected range.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="lwa-card p-4">
                            <div class="lwa-kpi-label">Total Error Requests</div>
                            <div class="lwa-kpi-value mt-1" style="color: var(--lwa-danger)">{{ $analytics['summary']['error_count_fmt'] ?? 0 }}</div>
                            <div class="lwa-subtitle mt-1">Error rate: {{ $analytics['summary']['error_rate'] ?? 0 }}%</div>
                        </div>

                        <div class="lwa-card p-4">
                            <div class="lwa-kpi-label">4xx Client Errors</div>
                            <div class="lwa-kpi-value mt-1" style="color: var(--lwa-warning)">{{ number_format($analytics['summary']['status_counts']['4xx'] ?? 0) }}</div>
                            <div class="lwa-subtitle mt-1">404 Not Found, 401 Unauthorized, etc.</div>
                        </div>

                        <div class="lwa-card p-4">
                            <div class="lwa-kpi-label">5xx Server Errors</div>
                            <div class="lwa-kpi-value mt-1">{{ number_format($analytics['summary']['status_counts']['5xx'] ?? 0) }}</div>
                            <div class="lwa-subtitle mt-1">500 Internal Error, 502 Bad Gateway</div>
                        </div>
                    </div>

                    <div class="lwa-card overflow-hidden">
                        <div class="p-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="lwa-card-title">Top Error URLs (4xx / 5xx)</div>
                            <span class="lwa-subtitle">Sorted by failure count</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left lwa-table">
                                <thead>
                                    <tr>
                                        <th class="py-2.5 px-4 font-semibold">Status</th>
                                        <th class="py-2.5 px-4 font-semibold">Method</th>
                                        <th class="py-2.5 px-4 font-semibold">Path</th>
                                        <th class="py-2.5 px-4 font-semibold text-right">Count</th>
                                        <th class="py-2.5 px-4 font-semibold text-right">Last Seen</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($analytics['top_error_paths'] ?? [] as $err)
                                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40">
                                            <td class="py-2.5 px-4">
                                                <span class="px-2 py-0.5 rounded font-mono font-bold {{ $err['status_code'] >= 500 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400' }}">
                                                    {{ $err['status_code'] }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 font-mono font-medium text-slate-600 dark:text-slate-300">{{ $err['method'] }}</td>
                                            <td class="py-2.5 px-4 font-mono text-slate-700 dark:text-slate-200 truncate max-w-sm" title="{{ $err['path'] }}">{{ $err['path'] }}</td>
                                            <td class="py-2.5 px-4 text-right font-mono font-semibold text-slate-900 dark:text-white">{{ $err['count_fmt'] }}</td>
                                            <td class="py-2.5 px-4 text-right text-slate-400">{{ $err['last_seen'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-6 text-center text-slate-400">All server requests are succeeding. Zero errors recorded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
