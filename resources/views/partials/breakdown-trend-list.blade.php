{{-- Compact inline-SVG sparkline list for "View as: Trend". --}}
@php
    $trendIconKind = $iconKind ?? null;
@endphp
<div>
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
        <div class="lwa-list-row">
            <span class="lwa-list-name truncate max-w-[150px] sm:max-w-[260px]" title="{{ $trendItem['name'] }}">
                @if($trendIconKind)
                    {!! \Nafiur\WebAnalytics\Services\TechnologyIcon::render($trendIconKind, $trendItem['name']) !!}
                @endif
                <span class="truncate">{{ $trendItem['name'] }}</span>
            </span>
            <div class="lwa-list-metric">
                <svg viewBox="0 0 100 24" preserveAspectRatio="none" class="h-5 flex-1" style="color: var(--lwa-primary)" aria-hidden="true">
                    <polyline points="{{ trim($trendPoints) }}" fill="none" stroke="currentColor" stroke-width="1.6"
                              vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round" />
                </svg>
                <span class="lwa-list-count">
                    {{ number_format(array_sum($trendData)) }}
                </span>
            </div>
        </div>
    @empty
        <div class="text-xs text-slate-400 py-6 text-center">No trend data for this period</div>
    @endforelse
</div>
