                    <div class="lwa-surface border lwa-border rounded-lg p-3.5 shadow-sm flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="live-dot"></span>
                            <div>
                                <div class="text-xs font-semibold text-slate-900 dark:text-white">Real-Time Traffic Feed</div>
                                <div class="text-[11px] text-slate-400">Streaming live requests via Server-Sent Events (SSE)</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <div class="flex items-center gap-1.5 text-[11px]" id="lwaConnectionBadge" role="status" aria-live="polite">
                                <span class="w-2 h-2 rounded-full bg-amber-500" id="lwaConnectionDot"></span>
                                <span id="lwaConnectionText" class="text-amber-600 dark:text-amber-400">Connecting…</span>
                            </div>
                            <button type="button" id="lwaReconnectBtn"
                                class="hidden text-[11px] text-blue-600 dark:text-blue-400 hover:underline"
                                onclick="window.lwaStartSSE && window.lwaStartSSE()">Retry connection</button>
                            <div class="text-right text-[11px] text-slate-400" id="lastUpdatedClock">Listening for requests...</div>
                        </div>
                    </div>

                    <!-- Live visitor map (offline GeoIP coordinates when available) -->
                    @include('web-analytics::partials.visitors-map', [
                        'mapId' => 'lwaLiveMap',
                        'title' => 'Live Visitors Map',
                    ])

                    <div class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 border-b border-slate-200 dark:border-slate-700/60">
                                    <tr>
                                        <th class="py-2.5 px-4 font-semibold">Status</th>
                                        <th class="py-2.5 px-4 font-semibold">Method</th>
                                        <th class="py-2.5 px-4 font-semibold">User</th>
                                        <th class="py-2.5 px-4 font-semibold">Path</th>
                                        <th class="py-2.5 px-4 font-semibold">Client IP</th>
                                        <th class="py-2.5 px-4 font-semibold">Country</th>
                                        <th class="py-2.5 px-4 font-semibold">Duration</th>
                                        <th class="py-2.5 px-4 font-semibold">Size</th>
                                        <th class="py-2.5 px-4 font-semibold">Browser / OS</th>
                                        <th class="py-2.5 px-4 font-semibold text-right">Time</th>
                                    </tr>
                                </thead>
                                <tbody id="tableLiveFeed" class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @forelse($analytics['recent_logs'] ?? [] as $log)
                                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40" id="log-row-{{ $log['id'] }}">
                                            <td class="py-2 px-4">
                                                <span class="px-2 py-0.5 rounded font-mono font-bold {{ $log['status_code'] >= 500 ? 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400' : ($log['status_code'] >= 400 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400' : ($log['status_code'] >= 300 ? 'bg-cyan-50 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400')) }}">
                                                    {{ $log['status_code'] }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 font-mono font-semibold {{ $log['method'] === 'POST' ? 'text-sky-600' : 'text-emerald-600' }}">{{ $log['method'] }}</td>
                                            <td class="py-2 px-4">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] {{ $log['is_auth'] ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-400 font-medium' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }}">
                                                    {{ $log['user_name'] }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 font-mono text-slate-700 dark:text-slate-200 truncate max-w-[220px]" title="{{ $log['path'] }}">{{ $log['path'] }}</td>
                                            <td class="py-2 px-4 font-mono text-slate-500">
                                                @php($ipFlag = \Nafiur\WebAnalytics\Services\CountryFlag::emoji($log['country'] ?? null))
                                                @if($ipFlag !== '')@include('web-analytics::partials.flag', ['country' => $log['country'], 'emoji' => $ipFlag])@endif {{ $log['ip_address'] }}
                                            </td>
                                            <td class="py-2 px-4 whitespace-nowrap text-slate-600 dark:text-slate-300">
                                                @if($ipFlag !== '')@include('web-analytics::partials.flag', ['country' => $log['country'], 'emoji' => $ipFlag])@endif
                                                <span class="font-mono text-[11px]">{{ $log['country'] ?: 'Unknown' }}</span>
                                            </td>
                                            <td class="py-2 px-4 font-mono text-slate-600 dark:text-slate-300">{{ $log['response_time_ms'] }} ms</td>
                                            <td class="py-2 px-4 text-slate-500">{{ $log['bytes_sent_fmt'] }}</td>
                                            <td class="py-2 px-4 text-slate-500 dark:text-slate-400 truncate max-w-[140px]">{{ $log['browser'] }} on {{ $log['os'] }}</td>
                                            <td class="py-2 px-4 text-right text-slate-400" data-utc="{{ $log['created_at_iso'] ?? '' }}">{{ $log['time_ago'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="py-8 text-center text-slate-400">Waiting for live requests...</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
