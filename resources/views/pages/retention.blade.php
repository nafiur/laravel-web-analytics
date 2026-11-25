@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Retention &amp; Lifecycle</h1>
        <p class="lwa-subtitle mt-1">Cohort return rates and lifecycle stages from <code class="font-mono">lwa_events</code>. Each user belongs to one cohort; only fully elapsed periods are shown.</p>
    </div>

    @unless($ready)
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Event store unavailable</div>
            <p class="lwa-subtitle mt-1">Enable the advanced schema and run the migrations to build retention reports.</p>
        </div>
    @endunless

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_retention') }}" class="space-y-3">
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="lwa-field-label" for="retentionEvent">Cohort event</label>
                    <input id="retentionEvent" list="lwaRetentionEvents" name="event" class="lwa-input" placeholder="any event" value="{{ $filters['event'] ?? '' }}">
                    <datalist id="lwaRetentionEvents">
                        @foreach($names as $name)<option value="{{ $name }}">@endforeach
                    </datalist>
                </div>
                <div>
                    <label class="lwa-field-label" for="retentionGranularity">Granularity</label>
                    <select id="retentionGranularity" name="granularity" class="lwa-select">
                        @foreach(['day' => 'Daily cohorts', 'week' => 'Weekly cohorts'] as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['granularity'] ?? 'day') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="retentionPeriods">Periods</label>
                    <select id="retentionPeriods" name="periods" class="lwa-select">
                        @foreach([2 => '2', 4 => '4', 7 => '7', 12 => '12'] as $value => $label)
                            <option value="{{ $value }}" {{ (int) ($filters['periods'] ?? 7) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="retentionRange">Time window</label>
                    <select id="retentionRange" name="range" class="lwa-select">
                        @foreach(['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'all' => 'All time', 'custom' => 'Custom'] as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['range'] ?? '30d') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="retentionPropertyKey">Property key</label>
                    <input id="retentionPropertyKey" name="property_key" class="lwa-input" placeholder="plan" value="{{ $filters['property_key'] ?? '' }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="retentionPropertyValue">Property value</label>
                    <input id="retentionPropertyValue" name="property_value" class="lwa-input" placeholder="pro" value="{{ $filters['property_value'] ?? '' }}">
                </div>
            </div>
            @if(($filters['range'] ?? '') === 'custom')
                <div class="grid grid-cols-2 gap-3 pt-3 border-t lwa-border">
                    <div>
                        <label class="lwa-field-label" for="retentionStart">Start date</label>
                        <input id="retentionStart" type="date" name="start_date" class="lwa-input" value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                    <div>
                        <label class="lwa-field-label" for="retentionEnd">End date</label>
                        <input id="retentionEnd" type="date" name="end_date" class="lwa-input" value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                </div>
            @endif
            <div class="flex items-center gap-2">
                <button type="submit" class="lwa-btn-primary"><i data-lucide="users-round" class="text-[13px]"></i> Run report</button>
                <a href="{{ route('lwa_retention') }}" class="lwa-btn-ghost">Clear</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div class="lwa-card lwa-kpi" title="Cohorts with at least one matching user">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Cohorts shown</span><span class="lwa-icon-tile"><i data-lucide="calendar-days" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format($retention['summary']['cohorts']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Distinct users across the displayed cohorts">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Cohorted users</span><span class="lwa-icon-tile"><i data-lucide="users" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format($retention['summary']['cohorted_users']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Cohort-size-weighted share active in period 1">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Period 1 retention</span><span class="lwa-icon-tile"><i data-lucide="repeat-2" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">@if($retention['summary']['period_one_rate'] === null) — @else {{ number_format((float) $retention['summary']['period_one_rate'], 2) }}% @endif</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Matching lwa_events rows read for this report">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Events scanned</span><span class="lwa-icon-tile"><i data-lucide="scan-search" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format($retention['sample_size']) }}</div>
        </div>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="px-4 py-3 border-b lwa-border">
            <div class="lwa-card-title">Cohort retention</div>
            <p class="lwa-subtitle mt-0.5">
                {{ $retention['summary']['cohorts'] }} cohorts · {{ $retention['sample_size'] }} events scanned
                @if($retention['capped']) · <span class="text-amber-500">sample capped</span>@endif
                · {{ $retention['granularity'] }} buckets
            </p>
        </div>
        @if(empty($retention['cohorts']))
            <div class="px-4 py-10 text-center">
                <div class="mx-auto lwa-icon-tile mb-3" style="width:40px;height:40px"><i data-lucide="users-round" class="text-lg"></i></div>
                <div class="lwa-card-title">No cohorts in this window</div>
                <p class="lwa-subtitle mt-1">Widen the time window or remove the event/property filters.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left lwa-table">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 font-semibold whitespace-nowrap">Cohort</th>
                            <th class="py-2 px-4 font-semibold text-right">Users</th>
                            @foreach($retention['periods'] as $period)
                                <th class="py-2 px-4 font-semibold text-right whitespace-nowrap">{{ $retention['granularity'] === 'week' ? 'W' : 'D' }}{{ $period }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($retention['cohorts'] as $cohort)
                            <tr>
                                <td class="py-2 px-4 whitespace-nowrap">
                                    <span class="font-mono">{{ $cohort['label'] }}</span>
                                    @if($cohort['partial'])<span class="lwa-chip ml-1" title="Bucket only partly inside the window">partial</span>@endif
                                </td>
                                <td class="py-2 px-4 text-right font-mono">{{ number_format($cohort['users']) }}</td>
                                @foreach($cohort['cells'] as $cell)
                                    <td class="py-2 px-4 text-right whitespace-nowrap">
                                        @if(! $cell['observed'])
                                            <span class="text-slate-400">—</span>
                                        @else
                                            <span class="font-mono">{{ number_format((float) $cell['rate'], 1) }}%</span>
                                            <span class="block text-[10px] text-slate-400">{{ number_format($cell['users']) }} users</span>
                                            <div class="lwa-progress mt-1" style="flex:none;width:56px"><span style="width: {{ max(0, min(100, (float) $cell['rate'])) }}%"></span></div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Lifecycle by period</div>
                <p class="lwa-subtitle mt-0.5">Fully elapsed {{ $retention['granularity'] }} buckets, newest last</p>
            </div>
            @if(empty($retention['lifecycle']))
                <div class="px-4 py-8 text-center lwa-subtitle">No completed lifecycle periods in this window.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left lwa-table">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 font-semibold">Period</th>
                                <th class="py-2 px-4 font-semibold text-right">New</th>
                                <th class="py-2 px-4 font-semibold text-right">Returning</th>
                                <th class="py-2 px-4 font-semibold text-right">Resurrecting</th>
                                <th class="py-2 px-4 font-semibold text-right">Dormant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($retention['lifecycle'] as $row)
                                <tr>
                                    <td class="py-2 px-4 font-mono">{{ $row['label'] }}</td>
                                    <td class="py-2 px-4 text-right font-mono">{{ number_format($row['new']) }}</td>
                                    <td class="py-2 px-4 text-right font-mono">{{ number_format($row['returning']) }}</td>
                                    <td class="py-2 px-4 text-right font-mono">{{ number_format($row['resurrecting']) }}</td>
                                    <td class="py-2 px-4 text-right font-mono">{{ number_format($row['dormant']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Metric definitions</div>
                <p class="lwa-subtitle mt-0.5">How these counts and rates were computed</p>
            </div>
            <ul class="px-4 py-3 space-y-2 text-xs text-slate-600 dark:text-slate-300">
                @foreach($retention['definitions'] as $definition)
                    <li class="flex gap-2"><span class="text-slate-400">·</span><span>{{ $definition }}</span></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="px-4 py-3 border-b lwa-border">
            <div class="lwa-card-title">Saved retention reports</div>
            <p class="lwa-subtitle mt-0.5">Stored in <code class="font-mono">lwa_insights</code>, scoped to the active website</p>
        </div>
        @if($saved->isEmpty())
            <div class="px-4 py-8 text-center lwa-subtitle">No saved retention reports yet.</div>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($saved as $report)
                    <div class="px-4 py-2.5 flex items-center justify-between gap-3">
                        <a href="{{ route('lwa_retention') . '?' . http_build_query($report->config ?? []) }}" class="text-xs font-medium hover:underline">{{ $report->name }}</a>
                        <form method="POST" action="{{ route('lwa_retention_destroy', $report->id) }}">
                            @csrf
                            <button type="submit" class="lwa-btn-ghost" title="Delete report"><i data-lucide="trash-2" class="text-[12px]"></i></button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('lwa_retention_save') }}" class="p-4 flex flex-wrap items-end gap-2 border-t lwa-border">
            @csrf
            <input type="hidden" name="config" value="{{ json_encode(array_merge($filters, ['type' => 'retention']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}">
            <div class="flex-1 min-w-48">
                <label class="lwa-field-label" for="retentionName">Report name</label>
                <input id="retentionName" name="name" class="lwa-input" required maxlength="150" placeholder="Weekly signup retention">
            </div>
            <button type="submit" class="lwa-btn-primary"><i data-lucide="bookmark-plus" class="text-[13px]"></i> Save report</button>
        </form>
        @if($errors->any())
            <div class="px-4 pb-3 text-xs text-red-500">{{ $errors->first() }}</div>
        @endif
    </div>
@endsection
