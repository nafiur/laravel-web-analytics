@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Funnels</h1>
        <p class="lwa-subtitle mt-1">Ordered, per-user conversion paths from <code class="font-mono">lwa_events</code>. One conversion is credited per user.</p>
    </div>

    @unless($ready)
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Event store unavailable</div>
            <p class="lwa-subtitle mt-1">Enable the advanced schema and run the migrations to build funnels.</p>
        </div>
    @endunless

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_funnels') }}" class="space-y-3">
            <div>
                <span class="lwa-field-label">Steps (in order, 2–10)</span>
                <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-2">
                    @foreach($filters['steps'] as $index => $step)
                        <div class="relative">
                            <span class="absolute left-2.5 top-2 text-[10px] font-mono text-slate-400">{{ $index + 1 }}</span>
                            <input list="lwaFunnelEvents" name="steps[]" class="lwa-input pl-7" value="{{ $step }}" placeholder="event name" required maxlength="100">
                        </div>
                    @endforeach
                </div>
                <datalist id="lwaFunnelEvents">
                    @foreach($names as $name)<option value="{{ $name }}">@endforeach
                </datalist>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="lwa-field-label" for="funnelWindow">Conversion window</label>
                    <select id="funnelWindow" name="window" class="lwa-select">
                        @foreach(['1h' => '1 hour', '24h' => '24 hours', '7d' => '7 days', '30d' => '30 days', 'all' => 'No limit'] as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['window'] ?? '7d') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="funnelRange">Time window</label>
                    <select id="funnelRange" name="range" class="lwa-select">
                        @foreach(['1h' => 'Last hour', '24h' => 'Last 24 hours', '7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'all' => 'All time', 'custom' => 'Custom'] as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['range'] ?? '7d') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="funnelPropertyKey">Property key</label>
                    <input id="funnelPropertyKey" name="property_key" class="lwa-input" placeholder="plan" value="{{ $filters['property_key'] ?? '' }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="funnelPropertyValue">Property value</label>
                    <input id="funnelPropertyValue" name="property_value" class="lwa-input" placeholder="pro" value="{{ $filters['property_value'] ?? '' }}">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="lwa-btn-primary"><i data-lucide="filter" class="text-[13px]"></i> Run funnel</button>
                    <a href="{{ route('lwa_funnels') }}" class="lwa-btn-ghost">Clear</a>
                </div>
            </div>
            @if(($filters['range'] ?? '') === 'custom')
                <div class="grid grid-cols-2 gap-3 pt-3 border-t lwa-border">
                    <div>
                        <label class="lwa-field-label" for="funnelStart">Start date</label>
                        <input id="funnelStart" type="date" name="start_date" class="lwa-input" value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                    <div>
                        <label class="lwa-field-label" for="funnelEnd">End date</label>
                        <input id="funnelEnd" type="date" name="end_date" class="lwa-input" value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                </div>
            @endif
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div class="lwa-card lwa-kpi" title="Users who completed step 1">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Entered</span><span class="lwa-icon-tile"><i data-lucide="user-round-plus" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format($funnel['entered_users']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Users who completed every step">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Completed</span><span class="lwa-icon-tile"><i data-lucide="circle-check-big" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format($funnel['completed_users']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Completed / entered">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Overall conversion</span><span class="lwa-icon-tile"><i data-lucide="percent" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format((float) $funnel['overall_conversion_rate'], 2) }}%</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Median time from first to last step among completed users">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Median time</span><span class="lwa-icon-tile"><i data-lucide="timer" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">
                @if($funnel['median_seconds'] === null) — @else {{ number_format($funnel['median_seconds'] / 60, 1) }} min @endif
            </div>
        </div>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="px-4 py-3 border-b lwa-border">
            <div class="lwa-card-title">Funnel breakdown</div>
            <p class="lwa-subtitle mt-0.5">{{ $funnel['sample_size'] }} events scanned @if($funnel['capped']) · <span class="text-amber-500">sample capped</span>@endif · conversion window {{ $funnel['window'] }}</p>
        </div>
        @if(empty($funnel['steps']))
            <div class="px-4 py-10 text-center">
                <div class="mx-auto lwa-icon-tile mb-3" style="width:40px;height:40px"><i data-lucide="filter" class="text-lg"></i></div>
                <div class="lwa-card-title">No steps to display</div>
                <p class="lwa-subtitle mt-1">Enter at least two event names above.</p>
            </div>
        @else
            <div class="p-4 space-y-3">
                @foreach($funnel['steps'] as $step)
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="lwa-chip">{{ $step['step'] }}</span>
                                <span class="font-mono text-xs text-slate-700 dark:text-slate-200">{{ $step['event'] }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                                <span class="font-mono">{{ number_format($step['users']) }} users</span>
                                <span>{{ number_format((float) $step['overall_conversion_rate'], 2) }}% overall</span>
                                @if($step['drop_off'] !== null)
                                    <span class="text-amber-500">−{{ number_format($step['drop_off']) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="lwa-progress"><span style="width: {{ max(0, min(100, (float) $step['overall_conversion_rate'])) }}%"></span></div>
                    </div>
                @endforeach
            </div>

            <table class="w-full text-xs text-left lwa-table border-t lwa-border">
                <thead>
                    <tr>
                        <th class="py-2 px-4 font-semibold">Step</th>
                        <th class="py-2 px-4 font-semibold">Event</th>
                        <th class="py-2 px-4 font-semibold text-right">Users</th>
                        <th class="py-2 px-4 font-semibold text-right">Step conversion</th>
                        <th class="py-2 px-4 font-semibold text-right">Overall</th>
                        <th class="py-2 px-4 font-semibold text-right">Drop-off</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($funnel['steps'] as $step)
                        <tr>
                            <td class="py-2 px-4 text-slate-400">{{ $step['step'] }}</td>
                            <td class="py-2 px-4 font-mono">{{ $step['event'] }}</td>
                            <td class="py-2 px-4 text-right font-mono">{{ number_format($step['users']) }}</td>
                            <td class="py-2 px-4 text-right font-mono">{{ number_format((float) $step['step_conversion_rate'], 2) }}%</td>
                            <td class="py-2 px-4 text-right font-mono">{{ number_format((float) $step['overall_conversion_rate'], 2) }}%</td>
                            <td class="py-2 px-4 text-right font-mono">{{ $step['drop_off'] === null ? '—' : number_format($step['drop_off']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Metric definitions</div>
                <p class="lwa-subtitle mt-0.5">How these counts and rates were computed</p>
            </div>
            <ul class="px-4 py-3 space-y-2 text-xs text-slate-600 dark:text-slate-300">
                @foreach($funnel['definitions'] as $definition)
                    <li class="flex gap-2"><span class="text-slate-400">·</span><span>{{ $definition }}</span></li>
                @endforeach
            </ul>
        </div>

        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Saved funnels</div>
                <p class="lwa-subtitle mt-0.5">Stored in <code class="font-mono">lwa_insights</code>, scoped to the active website</p>
            </div>
            @if($saved->isEmpty())
                <div class="px-4 py-8 text-center lwa-subtitle">No saved funnels yet.</div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($saved as $report)
                        <div class="px-4 py-2.5 flex items-center justify-between gap-3">
                            <a href="{{ route('lwa_funnels') . '?' . http_build_query($report->config ?? []) }}" class="text-xs font-medium hover:underline">{{ $report->name }}</a>
                            <form method="POST" action="{{ route('lwa_funnels_destroy', $report->id) }}">
                                @csrf
                                <button type="submit" class="lwa-btn-ghost" title="Delete funnel"><i data-lucide="trash-2" class="text-[12px]"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('lwa_funnels_save') }}" class="p-4 flex flex-wrap items-end gap-2 border-t lwa-border">
                @csrf
                <input type="hidden" name="config" value="{{ json_encode(array_merge($filters, ['type' => 'funnel']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}">
                <div class="flex-1 min-w-48">
                    <label class="lwa-field-label" for="funnelName">Funnel name</label>
                    <input id="funnelName" name="name" class="lwa-input" required maxlength="150" placeholder="Checkout funnel">
                </div>
                <button type="submit" class="lwa-btn-primary"><i data-lucide="bookmark-plus" class="text-[13px]"></i> Save funnel</button>
            </form>
            @if($errors->any())
                <div class="px-4 pb-3 text-xs text-red-500">{{ $errors->first() }}</div>
            @endif
        </div>
    </div>
@endsection
