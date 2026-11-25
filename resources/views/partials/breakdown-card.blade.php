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
<div class="lwa-card p-4 lwa-breakdown-card" data-lwa-card data-card-id="{{ $cardId }}" data-mode="proportion">
    <div class="flex items-start justify-between gap-2 mb-2">
        <div class="lwa-card-title">{{ $cardTitle }}</div>
        @include('web-analytics::partials.card-menu', [
            'cardId'          => $cardId,
            'exportData'      => $exportData,
            'trendKey'        => $cardTrend ? $cardId : null,
            'pinnable'        => $cardPinnable,
        ])
    </div>

    <div data-view="proportion">
        @include('web-analytics::partials.breakdown-list', [
            'items' => $cardItems,
            'key'   => str_replace('-', '_', (string) $cardId),
        ])
    </div>

    @if($cardTrend)
        <div data-view="trend" data-lwa-breakdown-key="{{ str_replace('-', '_', (string) $cardId) }}">
            @include('web-analytics::partials.breakdown-trend-list', [
                'trend'    => $cardTrend,
                'iconKind' => $iconKind,
            ])
        </div>
    @endif
</div>
