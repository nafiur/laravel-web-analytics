@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Form Analytics</h1>
        <p class="lwa-subtitle mt-1">Field engagement and submission signals from masked replay markers. No field values or validation messages are recorded.</p>
    </div>

    @unless($ready)
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Replay markers unavailable</div>
            <p class="lwa-subtitle mt-1">Enable session replay and run migrations to build form reports.</p>
        </div>
    @endunless

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_forms') }}" class="flex flex-wrap items-end gap-3">
            <div class="min-w-[220px] flex-1">
                <label class="lwa-field-label" for="formPath">Path</label>
                <input id="formPath" list="lwaFormPaths" name="path" class="lwa-input" placeholder="any recorded path" value="{{ $path ?? '' }}">
                <datalist id="lwaFormPaths">
                    @foreach($paths as $candidate)<option value="{{ $candidate }}">@endforeach
                </datalist>
            </div>
            <div>
                <label class="lwa-field-label" for="formRange">Window</label>
                <select id="formRange" name="range" class="lwa-select">
                    @foreach(['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'all' => 'All time'] as $value => $label)
                        <option value="{{ $value }}" {{ $range === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="lwa-btn-primary">Apply</button>
            <a href="{{ route('lwa_forms') }}" class="lwa-btn-ghost">Clear</a>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Forms seen</div><div class="lwa-kpi-value">{{ number_format(count($result['forms'])) }}</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Markers scanned</div><div class="lwa-kpi-value">{{ number_format($result['sample_size']) }}</div></div>
        <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Total starts</div><div class="lwa-kpi-value">{{ number_format(array_sum(array_column($result['forms'], 'starts'))) }}</div></div>
    </div>

    @if($result['capped'] ?? false)
        <div class="lwa-card p-4 text-xs" style="color: var(--lwa-warning)">Sample cap reached: only the 20,000 most recent matching form markers were read. Counts are lower bounds.</div>
    @endif

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border">
            <div class="lwa-card-title">Forms</div>
            <p class="lwa-subtitle mt-1">One row per structural form identifier. Starts, submits and errors are distinct session counts.</p>
        </div>
        @if(empty($result['forms']))
            <div class="p-4 lwa-empty">
                <div class="lwa-empty-title">No form markers recorded</div>
                <p>Focus, change and submit markers will appear here after replay sessions interact with a form.</p>
            </div>
        @else
            <div class="lwa-table-wrap">
                <table class="lwa-table w-full">
                    <thead>
                        <tr>
                            <th>Form</th><th>Starts</th><th>Submits</th><th>Conversion</th><th>Errors</th><th>Invalid submits</th><th>Error rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($result['forms'] as $form)
                            <tr>
                                <td class="font-mono">{{ $form['form'] }}</td>
                                <td>{{ number_format($form['starts']) }}</td>
                                <td>{{ number_format($form['submits']) }}</td>
                                <td>{{ number_format($form['conversion_rate'], 2) }}%</td>
                                <td>{{ number_format($form['errors']) }}</td>
                                <td>{{ number_format($form['invalid_submits']) }}</td>
                                <td>{{ number_format($form['error_rate'], 2) }}%</td>
                            </tr>
                            @if(!empty($form['fields']))
                                <tr>
                                    <td colspan="7" class="pb-3">
                                        <div class="space-y-1 pl-3 border-l lwa-border">
                                            @foreach($form['fields'] as $field)
                                                <div class="flex justify-between gap-3 text-[11px]" style="color: var(--lwa-muted)">
                                                    <span class="truncate font-mono">{{ $field['field'] }}</span>
                                                    <span>{{ number_format($field['focus_sessions']) }} focused · {{ number_format($field['change_sessions']) }} changed · {{ number_format($field['change_rate'], 2) }}%</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="lwa-card p-4">
        <div class="lwa-card-title">Definitions</div>
        <ul class="mt-2 space-y-1 text-xs" style="color: var(--lwa-muted)">
            @foreach($result['definitions'] as $key => $definition)
                <li><span class="font-mono">{{ $key }}</span>: {{ $definition }}</li>
            @endforeach
        </ul>
    </div>
@endsection
