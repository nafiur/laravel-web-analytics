                 @php
                     $dashboards = $dashboards ?? collect();
                     $templates = $templates ?? [];
                 @endphp

                 <div class="space-y-4" data-lwa-dashboards-index>
                     <div class="flex flex-wrap items-start justify-between gap-3">
                         <div>
                             <div class="flex items-center gap-2">
                                 <h2 class="lwa-page-title">Dashboards</h2>
                                 <span class="text-[10px] px-1.5 py-0.5 rounded border lwa-border text-slate-400">View docs</span>
                             </div>
                             <p class="lwa-subtitle mt-0.5">Create custom dashboards to monitor the metrics you care about.</p>
                         </div>
                         <button type="button" data-lwa-create-dashboard
                                 class="lwa-btn-primary">
                             <i data-lucide="plus" class="text-sm"></i> Create a Dashboard
                         </button>
                     </div>

                     <div class="flex flex-wrap items-center gap-2">
                         <input type="search" data-dashboard-search placeholder="Search dashboards..."
                                class="lwa-card lwa-control flex-1 min-w-[220px]">
                         <span class="lwa-chip">Grid view</span>
                     </div>

                     @if($dashboards->isEmpty())
                         <div class="lwa-card p-10 text-center">
                             <div class="mx-auto lwa-icon-tile mb-3" style="width:40px;height:40px">
                                 <i data-lucide="layout-dashboard" class="text-lg"></i>
                             </div>
                             <div class="lwa-card-title">You don't have any dashboards yet</div>
                             <p class="lwa-subtitle mt-1">Pick a template or build your own from scratch.</p>
                             <button type="button" data-lwa-create-dashboard
                                     class="mt-4 lwa-btn-primary">
                                 <i data-lucide="plus" class="text-sm"></i> Create a Dashboard
                             </button>
                         </div>
                     @else
                         <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                             @foreach($dashboards as $dashboard)
                                 <div class="lwa-card p-4" data-dashboard-item="{{ strtolower($dashboard->name) }}">
                                     <div class="flex items-start justify-between gap-2">
                                         <a href="{{ route('lwa_dashboard_view', $dashboard->id) }}" class="lwa-card-title hover:text-blue-600 truncate">{{ $dashboard->name }}</a>
                                         <button type="button" data-lwa-dashboard-delete="{{ $dashboard->id }}" class="text-slate-400 hover:text-rose-500" aria-label="Delete dashboard">
                                             <i data-lucide="trash-2" class="text-sm"></i>
                                         </button>
                                     </div>
                                     <div class="lwa-subtitle mt-1">{{ count($dashboard->widgets ?? []) }} widgets · {{ $dashboard->template ?: 'Custom' }}</div>
                                     <div class="flex items-center gap-3 mt-3 text-[11px]">
                                         <a href="{{ route('lwa_dashboard_view', $dashboard->id) }}" class="text-blue-600 dark:text-blue-400">View</a>
                                         <a href="{{ route('lwa_dashboard_edit', $dashboard->id) }}" class="text-blue-600 dark:text-blue-400">Edit</a>
                                         <span class="text-slate-400">{{ $dashboard->updated_at?->diffForHumans() }}</span>
                                     </div>
                                 </div>
                             @endforeach
                         </div>
                     @endif

                     <div>
                         <div class="lwa-section-label mb-2">Recommended templates</div>
                         <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                             @foreach($templates as $key => $template)
                                 <button type="button" data-lwa-template="{{ $key }}" class="lwa-card p-4 text-left hover:border-blue-400 transition">
                                     <i data-lucide="chart-line" class="text-lg text-slate-400"></i>
                                     <div class="lwa-card-title mt-2">{{ $template['label'] }}</div>
                                     <p class="lwa-subtitle mt-1">{{ $template['description'] }}</p>
                                 </button>
                             @endforeach
                         </div>
                     </div>
                 </div>

                 <!-- Create modal -->
                 <div data-lwa-create-modal class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-label="Create a Dashboard">
                     <div class="absolute inset-0 bg-slate-900/40" data-lwa-create-backdrop></div>
                     <div class="relative mx-auto my-8 w-[min(620px,94vw)] max-h-[85vh] overflow-hidden lwa-card flex flex-col" style="box-shadow: var(--lwa-shadow-pop)">
                         <div class="flex items-center justify-between px-4 py-3 border-b lwa-border">
                             <div class="lwa-card-title">Create a Dashboard</div>
                             <button type="button" data-lwa-create-close class="lwa-btn-ghost p-1" aria-label="Close">
                                 <i data-lucide="x" class="text-lg"></i>
                             </button>
                         </div>
                         <div class="p-4 overflow-auto space-y-3">
                             <input type="search" data-create-search placeholder="Search..." class="lwa-control w-full">
                             <input type="text" data-create-name placeholder="Dashboard name" class="lwa-control w-full">
                             <div class="lwa-section-label">Create new</div>
                             <button type="button" data-lwa-template="" class="flex items-center gap-3 w-full text-left px-3 py-2 rounded-md hover:bg-slate-50 dark:hover:bg-slate-800 border lwa-border">
                                 <i data-lucide="sparkles" class="text-lg text-slate-400"></i>
                                 <span><span class="block lwa-card-title">Start from a Blank Dashboard</span>
                                 <span class="block lwa-subtitle">Add widgets to monitor metrics</span></span>
                             </button>
                             <div class="lwa-section-label">Templates</div>
                             @foreach($templates as $key => $template)
                                 <button type="button" data-lwa-template="{{ $key }}" class="flex items-center gap-3 w-full text-left px-3 py-2 rounded-md hover:bg-slate-50 dark:hover:bg-slate-800 border lwa-border" data-create-item="{{ strtolower($template['label']) }}">
                                     <i data-lucide="chart-line" class="text-lg text-slate-400"></i>
                                     <span><span class="block lwa-card-title">{{ $template['label'] }}</span>
                                     <span class="block lwa-subtitle">{{ $template['description'] }}</span></span>
                                 </button>
                             @endforeach
                         </div>
                     </div>
                 </div>

                 <script type="application/json" id="lwaDashboardStoreUrl">{!! json_encode(route('lwa_dashboards_store')) !!}</script>
                 <script type="application/json" id="lwaDashboardDeleteUrl">{!! json_encode(route('lwa_dashboard_destroy', ['dashboard' => '__ID__'])) !!}</script>
                 <script type="application/json" id="lwaDashboardViewUrl">{!! json_encode(route('lwa_dashboard_view', ['dashboard' => '__ID__'])) !!}</script>
