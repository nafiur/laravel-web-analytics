                <div>
                    <h1 class="lwa-page-title">Geography</h1>
                    <p class="lwa-subtitle mt-1">Where your visitors are located.</p>
                </div>

                @include('web-analytics::partials.visitors-map', [
                    'mapId' => 'lwaGeographyMap',
                    'title' => 'Visitors Map',
                    'subtitle' => 'Approximate visitor locations — country centroid when precise GeoIP is unavailable',
                ])

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">Top Countries</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? [], 'key' => 'top_countries'])
                    </div>
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">Top Cities</div>
                        @if(empty($analytics['breakdowns']['top_cities'] ?? []))
                            <div class="lwa-subtitle">No city data recorded for this period. City names appear when GeoIP resolution provides them.</div>
                        @else
                            @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_cities'] ?? [], 'key' => 'top_cities'])
                        @endif
                    </div>
                </div>
                <p class="lwa-subtitle">GeoIP location is approximate and respects IP anonymization settings.</p>
