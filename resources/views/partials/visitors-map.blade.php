<div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
    <div class="flex items-center justify-between mb-2">
        <div>
            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ $title ?? 'Visitors Map' }}</div>
            <p class="text-[11px] text-slate-400">{{ $subtitle ?? 'Approximate location from GeoIP data — no external API calls at lookup time' }}</p>
        </div>
        <span id="{{ $mapId }}Count" class="text-[11px] font-mono text-slate-500">0 located</span>
    </div>
    <div id="{{ $mapId }}" data-lwa-map="{{ $mapId }}" class="w-full h-64 rounded-md overflow-hidden bg-slate-50 dark:bg-slate-800/40"></div>
    <p id="{{ $mapId }}Empty" class="text-[11px] text-slate-400 text-center mt-2">
        Install <code class="font-mono">maxmind-db/reader</code> and set <code class="font-mono">WEB_ANALYTICS_GEOIP_PROVIDER=maxmind</code> with a local GeoLite2-City.mmdb to plot visitors.
    </p>
</div>
