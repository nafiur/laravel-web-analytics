                <div>
                    <h1 class="lwa-page-title">Campaigns</h1>
                    <p class="lwa-subtitle mt-1">UTM sources, mediums and campaigns.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">UTM Sources</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_sources'] ?? [], 'key' => 'top_utm_sources'])
                    </div>
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">UTM Mediums</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_mediums'] ?? [], 'key' => 'top_utm_mediums'])
                    </div>
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">UTM Campaigns</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_utm_campaigns'] ?? [], 'key' => 'top_utm_campaigns'])
                    </div>
                </div>
