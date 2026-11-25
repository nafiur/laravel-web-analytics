@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Event Explorer</h1>
        <p class="lwa-subtitle mt-1">Query raw product events by name, property, identity and time window.</p>
    </div>

    @unless($ready)
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Event store unavailable</div>
            <p class="lwa-subtitle mt-1">Run the analytics schema migration to enable event exploration.</p>
        </div>
    @endunless

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_explorer') }}" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 items-end">
            <div>
                <label class="lwa-field-label" for="explorerEvent">Event</label>
                <select id="explorerEvent" name="event" class="lwa-select">
                    <option value="">All events</option>
                    @foreach($names as $name)
                        <option value="{{ $name }}" {{ ($filters['event'] ?? '') === $name ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="lwa-field-label" for="explorerPropertyKey">Property key</label>
                <input id="explorerPropertyKey" name="property_key" class="lwa-input" placeholder="plan"
                       value="{{ $filters['property_key'] ?? '' }}">
            </div>
            <div>
                <label class="lwa-field-label" for="explorerPropertyValue">Property value</label>
                <input id="explorerPropertyValue" name="property_value" class="lwa-input" placeholder="pro"
                       value="{{ $filters['property_value'] ?? '' }}">
            </div>
            <div>
                <label class="lwa-field-label" for="explorerUser">User / visitor</label>
                <input id="explorerUser" name="user" class="lwa-input" placeholder="u_42 or f_abc"
                       value="{{ $filters['user'] ?? '' }}">
            </div>
            <div>
                <label class="lwa-field-label" for="explorerRange">Time window</label>
                <select id="explorerRange" name="range" class="lwa-select">
                    @foreach(['1h' => 'Last hour', '24h' => 'Last 24 hours', '7d' => 'Last 7 days', '30d' => 'Last 30 days', 'all' => 'All time', 'custom' => 'Custom'] as $value => $label)
                        <option value="{{ $value }}" {{ ($filters['range'] ?? '7d') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="lwa-btn-primary">
                    <i data-lucide="search" class="text-[13px]"></i> Query
                </button>
                <a href="{{ route('lwa_explorer') }}" class="lwa-btn-ghost">Clear</a>
            </div>
        </form>

        @if(($filters['range'] ?? '') === 'custom')
            <div class="grid grid-cols-2 gap-3 mt-3 pt-3 border-t lwa-border">
                <div>
                    <label class="lwa-field-label" for="explorerStart">Start date</label>
                    <input id="explorerStart" type="date" name="start_date" class="lwa-input" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="explorerEnd">End date</label>
                    <input id="explorerEnd" type="date" name="end_date" class="lwa-input" value="{{ $filters['end_date'] ?? '' }}">
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="lwa-card lwa-kpi" title="Events matching the current filters">
            <div class="lwa-kpi-top">
                <span class="lwa-kpi-label">Matching events</span>
                <span class="lwa-icon-tile"><i data-lucide="list-filter" class="text-[13px]"></i></span>
            </div>
            <div class="lwa-kpi-value">{{ number_format($stats['events']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Distinct visitors in the matched events">
            <div class="lwa-kpi-top">
                <span class="lwa-kpi-label">Visitors</span>
                <span class="lwa-icon-tile"><i data-lucide="users-round" class="text-[13px]"></i></span>
            </div>
            <div class="lwa-kpi-value">{{ number_format($stats['users']) }}</div>
        </div>
        <div class="lwa-card lwa-kpi" title="Sum of the value attached to matched events">
            <div class="lwa-kpi-top">
                <span class="lwa-kpi-label">Total value</span>
                <span class="lwa-icon-tile"><i data-lucide="circle-dollar-sign" class="text-[13px]"></i></span>
            </div>
            <div class="lwa-kpi-value">{{ number_format($stats['value'], 2) }}</div>
        </div>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="px-4 py-3 border-b lwa-border">
            <div class="lwa-card-title">Matched events</div>
            <p class="lwa-subtitle mt-0.5">Showing {{ number_format(count($rows)) }} of {{ number_format($totalRows) }} matching events · capped at 20,000 scanned rows · every row comes from <code class="font-mono">lwa_events</code></p>
        </div>

        @if(empty($rows))
            <div class="px-4 py-10 text-center">
                <div class="mx-auto lwa-icon-tile mb-3" style="width:40px;height:40px">
                    <i data-lucide="scan-search" class="text-lg"></i>
                </div>
                <div class="lwa-card-title">No events matched</div>
                <p class="lwa-subtitle mt-1">Try widening the time window or clearing a property filter.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left lwa-table">
                    <thead>
                        <tr>
                            <th class="py-2.5 px-4 font-semibold">Event</th>
                            <th class="py-2.5 px-4 font-semibold">Visitor</th>
                            <th class="py-2.5 px-4 font-semibold">Properties</th>
                            <th class="py-2.5 px-4 font-semibold text-right">Value</th>
                            <th class="py-2.5 px-4 font-semibold text-right">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($rows as $row)
                            <tr>
                                <td class="py-2 px-4 font-mono font-medium text-slate-800 dark:text-slate-200">{{ $row['name'] }}</td>
                                <td class="py-2 px-4 font-mono text-slate-500">{{ $row['visitor'] }}</td>
                                <td class="py-2 px-4 font-mono text-slate-500 max-w-md truncate" title="{{ json_encode($row['properties']) }}">{{ json_encode($row['properties']) }}</td>
                                <td class="py-2 px-4 text-right font-mono text-slate-600 dark:text-slate-300">{{ $row['value'] !== null ? number_format((float) $row['value'], 2) : '—' }}</td>
                                <td class="py-2 px-4 text-right text-slate-400" data-utc="{{ $row['created_at'] ?? '' }}">{{ $row['created_at'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
