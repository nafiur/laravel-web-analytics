@php
    $highlightCountries = collect($analytics['breakdowns']['top_countries'] ?? [])
        ->map(fn ($item) => \Nafiur\WebAnalytics\Services\CountryFlag::code($item['name'] ?? null))
        ->filter()
        ->unique()
        ->values()
        ->all();
@endphp
<div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
    <div class="flex items-center justify-between mb-2">
        <div>
            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $title ?? 'Visitors Map' }}</div>
            <p class="text-[11px] text-slate-400">{{ $subtitle ?? 'Approximate location — country-level when GeoIP data is limited, no external API calls at lookup time' }}</p>
        </div>
        <span id="{{ $mapId }}Count" class="text-[11px] font-mono text-slate-500">0 located</span>
    </div>
    <div id="{{ $mapId }}" data-lwa-map="{{ $mapId }}" data-geoip-ready="{{ !empty($geoipReady) ? '1' : '0' }}" data-highlight-countries="{{ json_encode($highlightCountries) }}" class="w-full h-64 rounded-md overflow-hidden bg-slate-50 dark:bg-slate-800/40"></div>
    <p id="{{ $mapId }}Empty" class="lwa-map-empty text-[11px] text-slate-400 text-center mt-2">
        @if(!empty($geoipReady))
            No located visitors in this period yet.
        @else
            Country-level locations are plotted from visitor country data. For city-level precision, install <code class="font-mono">maxmind-db/reader</code> and configure a local GeoLite2-City.mmdb.
        @endif
    </p>
</div>
