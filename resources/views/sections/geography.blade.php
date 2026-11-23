                @include('web-analytics::partials.visitors-map', [
                    'mapId' => 'lwaGeographyMap',
                    'title' => 'Visitors Map',
                    'subtitle' => 'Approximate visitor locations — country centroid when precise GeoIP is unavailable',
                ])

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Countries</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? []])
                    </div>
                    <div class="lwa-surface border lwa-border rounded-lg p-4 shadow-sm">
                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 mb-3">Top Cities</div>
                        @if(empty($analytics['breakdowns']['top_cities'] ?? []))
                            <div class="text-[11px] text-slate-400">No city data recorded for this period. City names appear when GeoIP resolution provides them.</div>
                        @else
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_cities'] ?? []])
                        @endif
                    </div>
                </div>
                <p class="text-[11px] text-slate-400">GeoIP location is approximate and respects IP anonymization settings.</p>
