@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">User Paths</h1>
        <p class="lwa-subtitle mt-1">The most common ordered event sequences in <code class="font-mono">lwa_events</code>. One path per user, with repeated adjacent events collapsed.</p>
    </div>

    @unless($ready)
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Event store unavailable</div>
            <p class="lwa-subtitle mt-1">Enable the advanced schema and run the migrations to build path reports.</p>
        </div>
    @endunless

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_paths') }}" class="space-y-3">
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="lwa-field-label" for="pathStart">Start event</label>
                    <input id="pathStart" list="lwaPathEvents" name="start_event" class="lwa-input" placeholder="any event" value="{{ $filters['start_event'] ?? '' }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="pathEnd">End event</label>
                    <input id="pathEnd" list="lwaPathEvents" name="end_event" class="lwa-input" placeholder="no limit" value="{{ $filters['end_event'] ?? '' }}">
                    <datalist id="lwaPathEvents">
                        @foreach($names as $name)<option value="{{ $name }}">@endforeach
                    </datalist>
                </div>
                <div>
                    <label class="lwa-field-label" for="pathDepth">Max steps</label>
                    <select id="pathDepth" name="depth" class="lwa-select">
                        @foreach([2 => '2', 3 => '3', 5 => '5', 8 => '8'] as $value => $label)
                            <option value="{{ $value }}" {{ (int) ($filters['depth'] ?? 5) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="pathLimit">Top paths</label>
                    <select id="pathLimit" name="limit" class="lwa-select">
                        @foreach([10 => '10', 20 => '20', 50 => '50'] as $value => $label)
                            <option value="{{ $value }}" {{ (int) ($filters['limit'] ?? 20) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="pathRange">Time window</label>
                    <select id="pathRange" name="range" class="lwa-select">
                        @foreach(['1h' => 'Last hour', '24h' => 'Last 24 hours', '7d' => 'Last 7 days', '30d' => 'Last 30 days', 'all' => 'All time', 'custom' => 'Custom'] as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['range'] ?? '7d') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="pathPropertyKey">Property key</label>
                    <input id="pathPropertyKey" name="property_key" class="lwa-input" placeholder="plan" value="{{ $filters['property_key'] ?? '' }}">
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="lwa-field-label" for="pathPropertyValue">Property value</label>
                    <input id="pathPropertyValue" name="property_value" class="lwa-input" placeholder="pro" value="{{ $filters['property_value'] ?? '' }}">
                </div>
                <div class="flex items-center gap-2 md:col-span-2">
                    <button type="submit" class="lwa-btn-primary"><i data-lucide="route" class="text-[13px]"></i> Run paths</button>
                    <a href="{{ route('lwa_paths') }}" class="lwa-btn-ghost">Clear</a>
                </div>
            </div>
            @if(($filters['range'] ?? '') === 'custom')
                <div class="grid grid-cols-2 gap-3 pt-3 border-t lwa-border">
                    <div>
                        <label class="lwa-field-label" for="pathStartDate">Start date</label>
                        <input id="pathStartDate" type="date" name="start_date" class="lwa-input" value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                    <div>
                        <label class="lwa-field-label" for="pathEndDate">End date</label>
                        <input id="pathEndDate" type="date" name="end_date" class="lwa-input" value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                </div>
            @endif
        </form>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div class="lwa-card lwa-kpi" title="Users with at least two distinct steps">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Sequenced users</span><span class="lwa-icon-tile"><i data-lucide="users" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format($paths['summary']['sequenced_users']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Distinct paths observed in the sample">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Paths found</span><span class="lwa-icon-tile"><i data-lucide="git-fork" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format($paths['summary']['paths_found']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Users with only one distinct event in the window">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Single-step users</span><span class="lwa-icon-tile"><i data-lucide="circle-dot" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format($paths['summary']['single_step_users']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Matching lwa_events rows read for this report">
            <div class="lwa-kpi-top"><span class="lwa-kpi-label">Events scanned</span><span class="lwa-icon-tile"><i data-lucide="scan-search" class="text-[13px]"></i></span></div>
            <div class="lwa-kpi-value">{{ number_format($paths['sample_size']) }}</div>
        </div>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="px-4 py-3 border-b lwa-border">
            <div class="lwa-card-title">Top paths</div>
            <p class="lwa-subtitle mt-0.5">
                {{ $paths['summary']['paths_shown'] }} of {{ $paths['summary']['paths_found'] }} paths · {{ $paths['sample_size'] }} events scanned
                @if($paths['capped']) · <span class="text-amber-500">sample capped</span>@endif
                · up to {{ $paths['depth'] }} steps
                @if(($filters['start_event'] ?? '') !== '') · {{ $paths['summary']['excluded_users'] }} users never started @endif
            </p>
        </div>
        @if(empty($paths['paths']))
            <div class="px-4 py-10 text-center">
                <div class="mx-auto lwa-icon-tile mb-3" style="width:40px;height:40px"><i data-lucide="route" class="text-lg"></i></div>
                <div class="lwa-card-title">No multi-step paths in this window</div>
                <p class="lwa-subtitle mt-1">Widen the time window or relax the start/end/property filters.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left lwa-table">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 font-semibold">Path</th>
                            <th class="py-2 px-4 font-semibold text-right">Users</th>
                            <th class="py-2 px-4 font-semibold text-right">Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($paths['paths'] as $path)
                            <tr>
                                <td class="py-2 px-4">
                                    <div class="flex flex-wrap items-center gap-1">
                                        @foreach($path['steps'] as $index => $step)
                                            @if($index > 0)<span class="text-slate-400">→</span>@endif
                                            <span class="lwa-chip font-mono">{{ $step }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-2 px-4 text-right font-mono">{{ number_format($path['users']) }}</td>
                                <td class="py-2 px-4 text-right">
                                    <span class="font-mono">{{ number_format((float) $path['share'], 1) }}%</span>
                                    <div class="lwa-progress mt-1" style="flex:none;width:64px"><span style="width: {{ max(0, min(100, (float) $path['share'])) }}%"></span></div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="px-4 py-3 border-b lwa-border">
            <div class="lwa-card-title">Events by position</div>
            <p class="lwa-subtitle mt-0.5">Top events at each step, counted once per user at that position</p>
        </div>
        @if(empty($paths['positions']))
            <div class="px-4 py-8 text-center lwa-subtitle">No step distribution available yet.</div>
        @else
            <div class="p-4 grid grid-cols-2 md:grid-cols-5 gap-3">
                @foreach($paths['positions'] as $position)
                    <div class="border lwa-border rounded-md p-3">
                        <div class="lwa-card-title mb-2">Step {{ $position['position'] }}</div>
                        <div class="space-y-2">
                            @foreach($position['events'] as $event)
                                <div>
                                    <div class="flex items-center justify-between gap-2 text-[11px]">
                                        <span class="font-mono truncate" title="{{ $event['name'] }}">{{ $event['name'] }}</span>
                                        <span class="text-slate-400 font-mono">{{ number_format($event['users']) }}</span>
                                    </div>
                                    <div class="lwa-progress mt-1" style="flex:none"><span style="width: {{ max(0, min(100, (float) $event['share'])) }}%"></span></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Metric definitions</div>
                <p class="lwa-subtitle mt-0.5">How these paths and shares were computed</p>
            </div>
            <ul class="px-4 py-3 space-y-2 text-xs text-slate-600 dark:text-slate-300">
                @foreach($paths['definitions'] as $definition)
                    <li class="flex gap-2"><span class="text-slate-400">·</span><span>{{ $definition }}</span></li>
                @endforeach
            </ul>
        </div>

        <div class="lwa-card overflow-hidden">
            <div class="px-4 py-3 border-b lwa-border">
                <div class="lwa-card-title">Saved path reports</div>
                <p class="lwa-subtitle mt-0.5">Stored in <code class="font-mono">lwa_insights</code>, scoped to the active website</p>
            </div>
            @if($saved->isEmpty())
                <div class="px-4 py-8 text-center lwa-subtitle">No saved path reports yet.</div>
            @else
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($saved as $report)
                        <div class="px-4 py-2.5 flex items-center justify-between gap-3">
                            <a href="{{ route('lwa_paths') . '?' . http_build_query($report->config ?? []) }}" class="text-xs font-medium hover:underline">{{ $report->name }}</a>
                            <form method="POST" action="{{ route('lwa_paths_destroy', $report->id) }}">
                                @csrf
                                <button type="submit" class="lwa-btn-ghost" title="Delete report"><i data-lucide="trash-2" class="text-[12px]"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('lwa_paths_save') }}" class="p-4 flex flex-wrap items-end gap-2 border-t lwa-border">
                @csrf
                <input type="hidden" name="config" value="{{ json_encode(array_merge($filters, ['type' => 'paths']), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}">
                <div class="flex-1 min-w-48">
                    <label class="lwa-field-label" for="pathsName">Report name</label>
                    <input id="pathsName" name="name" class="lwa-input" required maxlength="150" placeholder="Signup paths">
                </div>
                <button type="submit" class="lwa-btn-primary"><i data-lucide="bookmark-plus" class="text-[13px]"></i> Save report</button>
            </form>
            @if($errors->any())
                <div class="px-4 pb-3 text-xs text-red-500">{{ $errors->first() }}</div>
            @endif
        </div>
    </div>
@endsection
