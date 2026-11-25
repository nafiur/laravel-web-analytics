                 @php
                     $widgets = $dashboard->widgets ?? [];
                     $catalogGroups = collect($catalog)->groupBy('group');
                 @endphp

                 <div class="space-y-4" data-dashboard-view data-dashboard-id="{{ $dashboard->id }}">
                     <div class="flex flex-wrap items-start justify-between gap-3">
                         <div>
                             <div class="flex items-center gap-2">
                                 <h2 class="lwa-page-title">{{ $dashboard->name }}</h2>
                                 @if($dashboard->template)
                                     <span class="lwa-chip">{{ $dashboard->template }}</span>
                                 @endif
                             </div>
                             <p class="lwa-subtitle mt-1">{{ count($widgets) }} widgets · {{ $editing ? 'Editing layout' : 'Read only' }}</p>
                         </div>
                         <div class="flex items-center gap-2">
                             <a href="{{ route('lwa_dashboards') }}" class="lwa-btn">All dashboards</a>
                             <a href="{{ $editing ? route('lwa_dashboard_view', $dashboard->id) : route('lwa_dashboard_edit', $dashboard->id) }}"
                                class="lwa-btn-primary">
                                 {{ $editing ? 'Done editing' : 'Edit dashboard' }}
                             </a>
                             @if($editing)
                                 <button type="button" data-dashboard-save class="lwa-btn-primary">Save</button>
                             @endif
                         </div>
                     </div>

                     <div class="{{ $editing ? 'grid grid-cols-1 lg:grid-cols-[200px_minmax(0,1fr)] gap-4 items-start' : '' }}">
                         @if($editing)
                             <aside class="lwa-card overflow-hidden">
                                 <div class="px-3 py-2 border-b lwa-border text-[11px] font-semibold text-slate-500 dark:text-slate-400">Add widgets</div>
                                 @foreach($catalogGroups as $group => $items)
                                     <details class="border-b lwa-border last:border-0" open>
                                         <summary class="px-3 py-2 text-xs font-medium text-slate-700 dark:text-slate-200 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800/60">{{ $group }}</summary>
                                         <div class="px-2 pb-2 space-y-0.5">
                                             @foreach($items as $id => $definition)
                                                 <button type="button" data-widget-add="{{ $definition['id'] ?? $id }}"
                                                         class="w-full text-left px-2 py-1.5 rounded text-[11px] text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
                                                     {{ $definition['label'] }}
                                                 </button>
                                             @endforeach
                                         </div>
                                     </details>
                                 @endforeach
                             </aside>
                         @endif

                         <div class="grid grid-cols-12 gap-4 items-start" data-widget-canvas>
                             @forelse($widgets as $index => $widget)
                                 @include('web-analytics::partials.dashboard-widget', [
                                     'widget'   => $widget,
                                     'widgetId' => 'dw' . $index,
                                     'analytics'=> $analytics,
                                     'editing'  => $editing,
                                 ])
                             @empty
                                 <div class="col-span-12 lwa-card p-8 text-center text-xs text-slate-400">
                                     No widgets yet. {{ $editing ? 'Add one from the catalog.' : 'Switch to edit mode to add widgets.' }}
                                 </div>
                             @endforelse
                         </div>
                     </div>
                 </div>

                 <script type="application/json" id="lwaDashboardState">{!! json_encode([
                     'id'      => $dashboard->id,
                     'widgets' => array_values($widgets),
                     'url'     => route('lwa_dashboard_update', $dashboard->id),
                     'editing' => (bool) $editing,
                 ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
