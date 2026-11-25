{{-- Reusable card tools: expand, View as, Export, Observability, pin. --}}
@php
    $cardId = $cardId ?? 'card';
    $menuId = 'lwaMenu-' . $cardId;
    $pinnable = $pinnable ?? true;
    $trendKey = $trendKey ?? null;
    $exportData = $exportData ?? [];
@endphp
<div class="lwa-card-tools relative flex items-center gap-0.5 shrink-0" data-lwa-card-tools data-card-id="{{ $cardId }}">
    <button type="button" data-lwa-card-expand
            class="lwa-btn-ghost p-1"
            aria-label="Expand card" title="Expand card">
        <i data-lucide="maximize-2" class="text-sm"></i>
    </button>
    <button type="button" data-lwa-card-menu-toggle
            class="lwa-btn-ghost p-1"
            aria-label="Card options" aria-haspopup="true" aria-expanded="false" aria-controls="{{ $menuId }}">
        <i data-lucide="ellipsis" class="text-base"></i>
    </button>

    <div id="{{ $menuId }}" data-lwa-card-menu-panel
         class="hidden absolute right-0 top-full mt-1.5 z-30 w-52 lwa-card p-1">
        @if($trendKey)
            <div class="px-3 pt-1.5 pb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">View as</div>
            <button type="button" data-view-mode="proportion"
                    class="lwa-view-option w-full flex items-center justify-between px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
                <span class="flex items-center gap-2"><i data-lucide="bar-chart-3" class="text-sm"></i> Proportion</span>
                <span data-view-check class="text-blue-600">✓</span>
            </button>
            <button type="button" data-view-mode="trend"
                    class="lwa-view-option w-full flex items-center justify-between px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
                <span class="flex items-center gap-2"><i data-lucide="trending-up" class="text-sm"></i> Trend</span>
                <span data-view-check class="text-blue-600">✓</span>
            </button>
            <div class="border-t lwa-border my-1"></div>
        @endif

        <div class="px-3 pb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Export</div>
        <button type="button" data-lwa-export="csv"
                class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
            <i data-lucide="file-spreadsheet" class="text-sm"></i> CSV
        </button>
        <button type="button" data-lwa-export="json"
                class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
            <i data-lucide="braces" class="text-sm"></i> JSON
        </button>

        <div class="border-t lwa-border my-1"></div>
        <a href="{{ $observabilityUrl ?? route('lwa_realtime') }}"
           class="flex items-center gap-2 px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
            <i data-lucide="activity" class="text-sm"></i> View in Observability
        </a>
        @if($pinnable)
            <button type="button" data-lwa-pin
                    class="w-full text-left flex items-center gap-2 px-2.5 py-1.5 rounded text-xs text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
                <i data-lucide="pin" class="text-sm"></i> <span data-pin-label>Add to dashboard</span>
            </button>
        @endif
    </div>

    <script type="application/json" id="lwa-card-data-{{ $cardId }}">{!! json_encode($exportData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</div>
