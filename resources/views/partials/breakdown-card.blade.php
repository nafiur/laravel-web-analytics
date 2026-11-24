{{-- A breakdown card with reusable menu + Proportion/Trend views. --}}
@php
    $cardId = $cardId ?? 'breakdown';
    $cardTitle = $cardTitle ?? 'Breakdown';
    $cardItems = $cardItems ?? [];
    $cardTrend = $cardTrend ?? null;
    $iconKind = $iconKind ?? null;
    $cardPinnable = $cardPinnable ?? true;
    $exportData = $exportData ?? $cardItems;
@endphp
<div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm lwa-breakdown-card" data-lwa-card data-card-id="{{ $cardId }}" data-mode="proportion">
    <div class="flex items-start justify-between gap-2 mb-3">
        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $cardTitle }}</div>
        @include('web-analytics::partials.card-menu', [
            'cardId'          => $cardId,
            'exportData'      => $exportData,
            'trendKey'        => $cardTrend ? $cardId : null,
            'pinnable'        => $cardPinnable,
        ])
    </div>

    <div data-view="proportion">
        @include('web-analytics::partials.breakdown-list', ['items' => $cardItems])
    </div>

    @if($cardTrend)
        <div data-view="trend">
            @include('web-analytics::partials.breakdown-trend-list', [
                'trend'    => $cardTrend,
                'iconKind' => $iconKind,
            ])
        </div>
    @endif
</div>
