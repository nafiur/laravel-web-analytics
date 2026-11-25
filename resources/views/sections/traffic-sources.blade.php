                <div>
                    <h1 class="lwa-page-title">Traffic Sources</h1>
                    <p class="lwa-subtitle mt-1">Referrers and countries sending traffic.</p>
                </div>

                <div class="lwa-card p-4">
                    <div class="lwa-card-title mb-3">Top Countries</div>
                    @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_countries'] ?? [], 'key' => 'top_countries'])
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">Top Referrers</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_referrers'] ?? [], 'key' => 'top_referrers'])
                    </div>
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">UTM Sources</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_sources'] ?? [], 'key' => 'top_utm_sources'])
                    </div>
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">UTM Mediums</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_mediums'] ?? [], 'key' => 'top_utm_mediums'])
                    </div>
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">Top Client IPs</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_ips'] ?? [], 'key' => 'top_ips'])
                    </div>
                </div>
