@php
    $listItems = $items ?? [];
    $listJson = json_encode($listItems, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
@endphp
<div class="lwa-breakdown-list relative group" data-lwa-breakdown-list
     data-lwa-breakdown-key="{{ $key ?? '' }}"
     data-lwa-title="{{ $title ?? '' }}" data-lwa-items='{!! $listJson !!}'>
    <button type="button" data-lwa-open-detail
            class="absolute right-0 -top-1 p-1 rounded-md text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 opacity-0 group-hover:opacity-100 focus:opacity-100 transition"
            aria-label="Open full breakdown" title="Open full breakdown">
        <i data-lucide="maximize-2" class="text-[11px]"></i>
    </button>

    <div>
        @forelse($listItems as $item)
            <div class="lwa-list-row">
                <span class="lwa-list-name truncate max-w-[150px] sm:max-w-[260px]" title="{{ $item['name'] }}">
                    @if(!empty($item['icon']))
                        {!! $item['icon'] !!}
                    @elseif(!empty($item['flag']))
                        @include('web-analytics::partials.flag', ['country' => $item['name'], 'emoji' => $item['flag']])
                    @endif
                    <span class="truncate">{{ $item['name'] }}</span>
                    @if(($key ?? '') === 'top_paths' && config('laravel-web-analytics.replay.enabled'))
                        <a href="{{ route('lwa_replay', ['path' => $item['name']]) }}"
                           class="ml-2 shrink-0 text-[10px]"
                           style="color: var(--lwa-accent)"
                           title="View recordings for this path">View recordings</a>
                    @endif
                </span>
                <div class="lwa-list-metric">
                    <div class="lwa-progress" title="{{ number_format((float) ($item['percentage'] ?? 0), 1) }}%">
                        <span style="width: {{ min(100, max(2, $item['percentage'] ?? 0)) }}%"></span>
                    </div>
                    <span class="lwa-list-count">
                        {{ $item['count_fmt'] ?? $item['count'] }}
                    </span>
                </div>
            </div>
        @empty
            <div class="text-xs text-slate-400 py-6 text-center">No data for this period</div>
        @endforelse
    </div>
</div>
