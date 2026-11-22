                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Error Requests</div>
                            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $analytics['summary']['error_count_fmt'] ?? 0 }}</div>
                            <div class="text-[11px] text-slate-400 mt-1">Error rate: {{ $analytics['summary']['error_rate'] ?? 0 }}%</div>
                        </div>

                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">4xx Client Errors</div>
                            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($analytics['summary']['status_counts']['4xx'] ?? 0) }}</div>
                            <div class="text-[11px] text-slate-400 mt-1">404 Not Found, 401 Unauthorized, etc.</div>
                        </div>

                        <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                            <div class="text-xs font-medium text-slate-500 dark:text-slate-400">5xx Server Errors</div>
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ number_format($analytics['summary']['status_counts']['5xx'] ?? 0) }}</div>
                            <div class="text-[11px] text-slate-400 mt-1">500 Internal Error, 502 Bad Gateway</div>
                        </div>
                    </div>

                    <div class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                        <div class="p-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Top Error URLs (4xx / 5xx)</div>
                            <span class="text-[11px] text-slate-400">Sorted by failure count</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 border-b border-slate-200 dark:border-slate-700/60">
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
                                                <span class="px-2 py-0.5 rounded font-mono font-bold {{ $err['status_code'] >= 500 ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' }}">
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
