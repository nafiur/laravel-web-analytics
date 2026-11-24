@php
    $listItems = $items ?? [];
    $listJson = json_encode($listItems, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
@endphp
<div class="lwa-breakdown-list relative group" data-lwa-breakdown-list
     data-lwa-title="{{ $title ?? '' }}" data-lwa-items='{!! $listJson !!}'>
    <button type="button" data-lwa-open-detail
            class="absolute right-0 -top-1 p-0.5 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 opacity-0 group-hover:opacity-100 focus:opacity-100 transition"
            aria-label="Open full breakdown" title="Open full breakdown">
        <i data-lucide="maximize-2" class="text-xs"></i>
    </button>

    <div class="space-y-2">
        @forelse($listItems as $item)
            <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100 dark:border-slate-800/80 last:border-0 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 px-1 rounded transition">
                <span class="flex items-center gap-1.5 min-w-0 truncate max-w-[180px] sm:max-w-[280px] font-mono text-slate-700 dark:text-slate-300" title="{{ $item['name'] }}">
                    @if(!empty($item['icon']))
                        {!! $item['icon'] !!}
                    @elseif(!empty($item['flag']))
                        @include('web-analytics::partials.flag', ['country' => $item['name'], 'emoji' => $item['flag']])
                    @endif
                    <span class="truncate">{{ $item['name'] }}</span>
                </span>
                <div class="flex items-center gap-3 w-36 sm:w-48 shrink-0">
                    <div class="flex-1 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-[#0051c3] dark:bg-[#38bdf8] h-full rounded-full transition-all duration-500"
                             style="width: {{ min(100, max(2, $item['percentage'] ?? 0)) }}%"></div>
                    </div>
                    <span class="text-right w-14 font-mono font-medium text-slate-600 dark:text-slate-400 shrink-0">
                        {{ $item['count_fmt'] ?? $item['count'] }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-xs text-slate-400 py-3 text-center">No data for this period</div>
        @endforelse
    </div>
</div>
