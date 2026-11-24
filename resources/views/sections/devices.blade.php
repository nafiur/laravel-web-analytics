                 @php
                     $deviceOverTime = $analytics['time_series']['device_over_time'] ?? ['labels' => [], 'series' => []];
                     $deviceSeries = $deviceOverTime['series'] ?? [];
                     $deviceTotals = [];
                     foreach ($deviceSeries as $deviceItem) {
                         $deviceTotals[$deviceItem['name']] = array_sum($deviceItem['data'] ?? []);
                     }
                     $deviceColors = [
                         'Desktop' => '#0051c3',
                         'Mobile'  => '#f59e0b',
                         'Tablet'  => '#8b5cf6',
                         'Bot'     => '#94a3b8',
                     ];
                     $technologyExport = [
                         'device_types_over_time' => $deviceOverTime,
                         'operating_systems'      => $analytics['breakdowns']['top_os'] ?? [],
                         'browsers'               => $analytics['breakdowns']['top_browsers'] ?? [],
                         'user_agents'            => $analytics['breakdowns']['top_user_agents'] ?? [],
                         'device_types'           => $analytics['breakdowns']['top_devices'] ?? [],
                     ];
                 @endphp

                 <div class="space-y-4">
                     <div>
                         <h2 class="text-sm font-semibold text-slate-900 dark:text-white">Technology</h2>
                         <p class="text-[11px] text-slate-400 mt-0.5">Device, operating system and browser breakdowns for the selected period — {{ $timeRange ?? '24h' }}</p>
                     </div>

                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                         <!-- Device types over time -->
                         <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                             <div class="flex items-start justify-between gap-3">
                                 <div class="min-w-0">
                                     <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">Device types over time</div>
                                     <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2">
                                         @forelse($deviceSeries as $deviceItem)
                                             <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400">
                                                 <span class="w-2 h-2 rounded-full shrink-0" style="background: {{ $deviceColors[$deviceItem['name']] ?? '#0051c3' }}"></span>
                                                 <span>{{ $deviceItem['name'] }}</span>
                                                 <span class="font-semibold text-slate-700 dark:text-slate-200">{{ number_format($deviceTotals[$deviceItem['name']] ?? 0) }}</span>
                                             </div>
                                         @empty
                                             <span class="text-[11px] text-slate-400">No device data for this period</span>
                                         @endforelse
                                     </div>
                                 </div>

                                 <div class="relative shrink-0" data-lwa-card-menu>
                                     <button type="button" data-lwa-card-menu-toggle
                                             class="p-1 rounded text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                                             aria-label="Technology report options" aria-haspopup="true" aria-expanded="false">
                                         <i data-lucide="ellipsis" class="text-base"></i>
                                     </button>
                                     <div data-lwa-card-menu-panel
                                          class="hidden absolute right-0 top-full mt-1 z-20 w-40 rounded-md border lwa-border bg-white dark:bg-slate-800 shadow-lg py-1">
                                         <button type="button" data-lwa-export="csv"
                                                 class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">
                                             Export CSV
                                         </button>
                                         <button type="button" data-lwa-export="json"
                                                 class="w-full text-left px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700">
                                             Export JSON
                                         </button>
                                     </div>
                                 </div>
                             </div>

                             <div class="relative mt-3 h-64">
                                 <div id="chartDeviceTypes" class="h-64 w-full"></div>
                                 @if(empty($deviceSeries))
                                     <div class="absolute inset-0 flex items-center justify-center text-xs text-slate-400 pointer-events-none">
                                         No device data recorded for this period
                                     </div>
                                 @endif
                             </div>
                         </div>

                         <!-- Operating systems -->
                         <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                             <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top operating systems</div>
                             @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_os'] ?? []])
                         </div>
                     </div>

                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                         <!-- Browsers -->
                         <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                             <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top browsers</div>
                             @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_browsers'] ?? []])
                         </div>

                         <!-- Raw user agents -->
                         <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                             <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top user agents</div>
                             @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_user_agents'] ?? []])
                         </div>
                     </div>

                     <script type="application/json" id="lwaTechnologyExportData">{!! json_encode($technologyExport, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                 </div>
