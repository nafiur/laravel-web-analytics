                <div>
                    <h1 class="lwa-page-title">Pages &amp; Screens</h1>
                    <p class="lwa-subtitle mt-1">Most requested paths and entry points.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">Top Pages</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths'] ?? [], 'key' => 'top_paths'])
                    </div>
                    <div class="lwa-card p-4">
                        <div class="lwa-card-title mb-3">Top Paths by Bandwidth</div>
                        @include('web-analytics::partials.breakdown-list', ['items' => $analytics['breakdowns']['top_paths_bandwidth'] ?? [], 'key' => 'top_paths_bandwidth'])
                    </div>
                </div>
