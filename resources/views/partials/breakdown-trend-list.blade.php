{{-- Compact inline-SVG sparkline list for "View as: Trend". --}}
@php
    $trendIconKind = $iconKind ?? null;
@endphp
<div class="space-y-1">
    @forelse($trend['series'] ?? [] as $trendItem)
        @php
            $trendData = $trendItem['data'] ?? [];
            $trendMax = !empty($trendData) ? max($trendData) : 1;
            $trendMax = $trendMax > 0 ? $trendMax : 1;
            $trendCount = count($trendData);
            $trendPoints = '';
            foreach ($trendData as $trendIndex => $trendValue) {
                $tx = $trendCount > 1 ? ($trendIndex / ($trendCount - 1)) * 100 : 0;
                $ty = 22 - ((float) $trendValue / $trendMax) * 18;
                $trendPoints .= round($tx, 2) . ',' . round($ty, 2) . ' ';
            }
        @endphp
        <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100 dark:border-slate-800/80 last:border-0 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 px-1 rounded transition">
            <span class="flex items-center gap-1.5 min-w-0 truncate max-w-[180px] sm:max-w-[280px] font-mono text-slate-700 dark:text-slate-300" title="{{ $trendItem['name'] }}">
                @if($trendIconKind)
                    {!! \Nafiur\WebAnalytics\Services\TechnologyIcon::render($trendIconKind, $trendItem['name']) !!}
                @endif
                <span class="truncate">{{ $trendItem['name'] }}</span>
            </span>
            <div class="flex items-center gap-3 w-36 sm:w-48 shrink-0">
                <svg viewBox="0 0 100 24" preserveAspectRatio="none" class="h-5 flex-1 text-[#0051c3] dark:text-[#38bdf8]" aria-hidden="true">
                    <polyline points="{{ trim($trendPoints) }}" fill="none" stroke="currentColor" stroke-width="1.6"
                              vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round" />
                </svg>
                <span class="text-right w-14 font-mono font-medium text-slate-600 dark:text-slate-400 shrink-0">
                    {{ number_format(array_sum($trendData)) }}
                </span>
            </div>
        </div>
    @empty
        <div class="text-xs text-slate-400 py-3 text-center">No trend data for this period</div>
    @endforelse
</div>
