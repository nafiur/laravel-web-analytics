                 @php
                     $cacheTrend = $analytics['breakdown_trends']['top_cache_statuses'] ?? ['labels' => [], 'series' => []];
                     $logColumns = [
                         'Timestamp', 'Method', 'Host', 'Path', 'Status', 'Cache status', 'Source IP',
                     ];
                 @endphp

                 <div class="space-y-4">
                     <div>
                         <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Logs</h2>
                         <p class="text-[11px] text-slate-400 mt-0.5">Recorded HTTP requests — filter, inspect and export the raw rows in the selected range.</p>
                     </div>

                     <!-- Request outcomes over time -->
                     <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                         <div class="flex items-start justify-between gap-3">
                             <div>
                                 <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Request outcomes over time</div>
                                 <p class="text-[11px] text-slate-400 mt-0.5">Cache delivery breakdown for the selected period</p>
                             </div>
                             @include('web-analytics::partials.group-by-select', [
                                 'chartId' => 'chartLogOutcomes',
                                 'default' => 'top_cache_statuses',
                             ])
                         </div>
                         <div class="relative mt-3 h-56">
                             <div id="chartLogOutcomes" class="h-56 w-full"></div>
                             @if(empty($cacheTrend['series'] ?? []))
                                 <div class="absolute inset-0 flex items-center justify-center text-xs text-slate-400 pointer-events-none">
                                     No cache data recorded for this period
                                 </div>
                             @endif
                         </div>
                     </div>

                     <!-- Filters -->
                     <form method="GET" action="{{ route('lwa_logs') }}" class="lwa-surface border lwa-border rounded-lg p-3 shadow-sm">
                         @foreach(['range' => $timeRange ?? '24h', 'start_date' => $customStart ?? null, 'end_date' => $customEnd ?? null, 'source' => $source ?? 'database', 'tenant_id' => $tenantId ?? null] as $hiddenKey => $hiddenValue)
                             @if($hiddenValue !== null)
                                 <input type="hidden" name="{{ $hiddenKey }}" value="{{ $hiddenValue }}">
                             @endif
                         @endforeach
                         <div class="flex flex-wrap items-center gap-2">
                             <input type="search" name="q" value="{{ $logFilters['q'] ?? '' }}" placeholder="Search path, URL, IP or user agent..."
                                    class="flex-1 min-w-[240px] text-xs px-2.5 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                             @foreach([
                                 'method' => ['Method', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']],
                                 'status' => ['Status', ['2xx', '3xx', '4xx', '5xx', '200', '301', '302', '404', '500']],
                                 'cache_status' => ['Cache', ['HIT', 'MISS', 'BYPASS', 'EXPIRED', 'Dynamic']],
                             ] as $field => [$label, $options])
                                 <select name="{{ $field }}" class="text-xs px-2 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                     <option value="">{{ $label }}: all</option>
                                     @foreach($options as $option)
                                         <option value="{{ $option }}" {{ ($logFilters[$field] ?? '') === $option ? 'selected' : '' }}>{{ $option }}</option>
                                     @endforeach
                                 </select>
                             @endforeach
                             <input type="text" name="host" value="{{ $logFilters['host'] ?? '' }}" placeholder="Host"
                                    class="w-32 text-xs px-2 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                             <input type="text" name="path" value="{{ $logFilters['path'] ?? '' }}" placeholder="Path contains"
                                    class="w-36 text-xs px-2 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                             <input type="text" name="ip_address" value="{{ $logFilters['ip_address'] ?? '' }}" placeholder="Source IP"
                                    class="w-32 text-xs px-2 py-1.5 border border-slate-200 dark:border-slate-700 rounded-md bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                             <button type="submit"
                                     class="px-3 py-1.5 text-xs font-semibold text-white bg-[#0051c3] hover:bg-[#003d94] rounded-md">Apply</button>
                             <button type="button" data-lwa-export-logs
                                     class="px-3 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-md">Export CSV</button>
                         </div>
                     </form>

                     <!-- Raw request table -->
                     <div class="lwa-surface border lwa-border rounded-lg shadow-sm overflow-hidden">
                         <div class="overflow-x-auto">
                             <table class="w-full text-xs">
                                 <thead class="bg-slate-50 dark:bg-slate-800/60 text-[11px] uppercase tracking-wide text-slate-400">
                                     <tr>
                                         <th class="w-10"></th>
                                         @foreach($logColumns as $column)
                                             <th class="px-3 py-2 text-left whitespace-nowrap">{{ $column }}</th>
                                         @endforeach
                                     </tr>
                                 </thead>
                                 <tbody id="lwaLogsTable" class="divide-y divide-slate-100 dark:divide-slate-800">
                                     @forelse($logRows as $row)
                                         <tr data-log-row="{{ $loop->index }}" class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 align-top">
                                             <td class="px-2 py-2">
                                                 <button type="button" data-log-expand="{{ $loop->index }}" class="p-1 rounded text-slate-400 hover:text-slate-700" aria-label="Expand row">
                                                     <i data-lucide="chevron-right" class="text-sm"></i>
                                                 </button>
                                             </td>
                                             <td class="px-3 py-2 whitespace-nowrap font-mono text-slate-600 dark:text-slate-300" data-utc="{{ $row->created_at?->toIso8601String() }}">{{ $row->created_at?->format('Y-m-d H:i:s') }}</td>
                                             <td class="px-3 py-2"><span class="px-2 py-0.5 rounded font-mono font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300">{{ $row->method }}</span></td>
                                             <td class="px-3 py-2 font-mono text-slate-600 dark:text-slate-300 truncate max-w-[180px]" title="{{ $row->host }}">{{ $row->host ?: '-' }}</td>
                                             <td class="px-3 py-2 font-mono text-slate-700 dark:text-slate-200 truncate max-w-[300px]" title="{{ $row->path }}">{{ $row->path }}</td>
                                             <td class="px-3 py-2"><span class="px-2 py-0.5 rounded font-mono {{ $row->status_code >= 400 ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300' }}">{{ $row->status_code }}</span></td>
                                             <td class="px-3 py-2 whitespace-nowrap text-slate-500 dark:text-slate-400">{{ $row->cache_status ?: '-' }}</td>
                                             <td class="px-3 py-2 font-mono text-slate-500 dark:text-slate-400">{{ $row->ip_address }}</td>
                                         </tr>
                                         <tr data-log-detail="{{ $loop->index }}" class="hidden bg-slate-50/70 dark:bg-slate-800/40">
                                             <td colspan="{{ count($logColumns) + 1 }}" class="px-4 py-3">
                                                 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-2 text-[11px]">
                                                     <div><span class="text-slate-400">Full URL</span><div class="font-mono text-slate-700 dark:text-slate-200 break-all">{{ $row->full_url ?: '-' }}</div></div>
                                                     <div><span class="text-slate-400">User agent</span><div class="font-mono text-slate-700 dark:text-slate-200 break-all">{{ $row->user_agent ?: '-' }}</div></div>
                                                     <div><span class="text-slate-400">Referrer</span><div class="font-mono text-slate-700 dark:text-slate-200 break-all">{{ $row->referrer ?: '-' }}</div></div>
                                                     <div><span class="text-slate-400">Location</span><div class="text-slate-700 dark:text-slate-200">{{ trim(($row->city ?? '') . ', ' . ($row->country ?? ''), ', ') ?: '-' }}</div></div>
                                                     <div><span class="text-slate-400">Response time</span><div class="font-mono text-slate-700 dark:text-slate-200">{{ $row->response_time_ms }} ms</div></div>
                                                     <div><span class="text-slate-400">Bytes sent</span><div class="font-mono text-slate-700 dark:text-slate-200">{{ number_format($row->bytes_sent ?? 0) }}</div></div>
                                                     <div><span class="text-slate-400">Session / User</span><div class="font-mono text-slate-700 dark:text-slate-200">{{ ($row->session_id ?: '-') . ' / ' . ($row->user_id ?: 'guest') }}</div></div>
                                                     <div><span class="text-slate-400">UTM</span><div class="font-mono text-slate-700 dark:text-slate-200">{{ trim(($row->utm_source ?? '') . ' / ' . ($row->utm_medium ?? '') . ' / ' . ($row->utm_campaign ?? ''), ' /') ?: '-' }}</div></div>
                                                 </div>
                                             </td>
                                         </tr>
                                     @empty
                                         <tr><td colspan="{{ count($logColumns) + 1 }}" class="px-4 py-8 text-center text-xs text-slate-400">No requests match these filters</td></tr>
                                     @endforelse
                                 </tbody>
                             </table>
                         </div>
                         @if($logRows->hasPages())
                             <div class="px-4 py-3 border-t lwa-border">{{ $logRows->links() }}</div>
                         @endif
                         <script type="application/json" id="lwaLogsData">{!! json_encode($logRows->items(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                     </div>
                 </div>
