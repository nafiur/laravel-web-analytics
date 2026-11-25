@php
    $topCountryItems = collect($analytics['breakdowns']['top_countries'] ?? [])
        ->map(fn ($item) => [
            'name' => $item['name'] ?? '',
            'flag' => \Nafiur\WebAnalytics\Services\CountryFlag::emoji($item['name'] ?? null),
        ])
        ->filter(fn ($item) => $item['name'] !== '')
        ->take(8)
        ->values()
        ->all();
    $highlightCountries = collect($topCountryItems)
        ->map(fn ($item) => \Nafiur\WebAnalytics\Services\CountryFlag::code($item['name']))
        ->filter()
        ->unique()
        ->values()
        ->all();
@endphp
<div class="{{ empty($bare) ? 'lwa-card p-4' : '' }}">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="lwa-card-title">{{ $title ?? 'Visitors Map' }}</div>
            <p class="lwa-subtitle mt-0.5">{{ $subtitle ?? 'Approximate location — country-level when GeoIP data is limited, no external API calls at lookup time' }}</p>
        </div>
        <span id="{{ $mapId }}Count" class="lwa-chip font-mono">0 located</span>
    </div>
    <div id="{{ $mapId }}" data-lwa-map="{{ $mapId }}" data-geoip-ready="{{ !empty($geoipReady) ? '1' : '0' }}" data-highlight-countries="{{ json_encode($highlightCountries) }}" class="w-full h-64 rounded-md overflow-hidden bg-slate-50 dark:bg-slate-800/40 border lwa-border"></div>
    @php
        $mapHasMarkers = collect($analytics['recent_logs'] ?? [])
            ->filter(fn ($log) => !empty($log['latitude']) && !empty($log['longitude']))
            ->isNotEmpty();
    @endphp
    <p id="{{ $mapId }}Empty" class="lwa-map-empty lwa-subtitle text-center mt-2 {{ $mapHasMarkers ? 'hidden' : '' }}">
        @if(!empty($geoipReady))
            No located visitors in this period yet.
        @else
            Country-level locations are plotted from visitor country data. For city-level precision, install <code class="font-mono">maxmind-db/reader</code> and configure a local GeoLite2-City.mmdb.
        @endif
    </p>
    @if(!empty($topCountryItems))
        <div class="flex flex-wrap items-center gap-2 mt-3">
            @foreach($topCountryItems as $item)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    @if($item['flag'] !== '')@include('web-analytics::partials.flag', ['country' => $item['name'], 'emoji' => $item['flag']])@endif
                    <span>{{ $item['name'] }}</span>
                </span>
            @endforeach
        </div>
    @endif
</div>
