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
                     $breakdownTrends = $analytics['breakdown_trends'] ?? [];
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
                         <h2 class="lwa-page-title">Technology</h2>
                         <p class="text-[11px] text-slate-400 mt-0.5">Device, operating system and browser breakdowns for the selected period — {{ $timeRange ?? '24h' }}</p>
                     </div>

                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                         <!-- Device types over time -->
                         <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm" data-lwa-card data-card-id="device-types-over-time">
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

                                 @include('web-analytics::partials.card-menu', [
                                     'cardId'     => 'device-types-over-time',
                                     'exportData' => $technologyExport,
                                     'pinnable'   => false,
                                 ])
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
                         @include('web-analytics::partials.breakdown-card', [
                             'cardId'     => 'top-os',
                             'cardTitle'  => 'Top operating systems',
                             'cardItems'  => $analytics['breakdowns']['top_os'] ?? [],
                             'cardTrend'  => $breakdownTrends['top_os'] ?? null,
                             'iconKind'   => 'os',
                             'exportData' => ['operating_systems' => $analytics['breakdowns']['top_os'] ?? []],
                         ])
                     </div>

                     <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                         <!-- Browsers -->
                         @include('web-analytics::partials.breakdown-card', [
                             'cardId'     => 'top-browsers',
                             'cardTitle'  => 'Top browsers',
                             'cardItems'  => $analytics['breakdowns']['top_browsers'] ?? [],
                             'cardTrend'  => $breakdownTrends['top_browsers'] ?? null,
                             'iconKind'   => 'browser',
                             'exportData' => ['browsers' => $analytics['breakdowns']['top_browsers'] ?? []],
                         ])

                         <!-- Raw user agents -->
                         @include('web-analytics::partials.breakdown-card', [
                             'cardId'     => 'top-user-agents',
                             'cardTitle'  => 'Top user agents',
                             'cardItems'  => $analytics['breakdowns']['top_user_agents'] ?? [],
                             'cardTrend'  => null,
                             'exportData' => ['user_agents' => $analytics['breakdowns']['top_user_agents'] ?? []],
                         ])
                     </div>
                 </div>
