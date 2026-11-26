@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">A/B Experiments</h1>
        <p class="lwa-subtitle mt-1">Results are read only from recorded <code class="font-mono">lwa_events</code>: flag exposures and goal events carrying the same variant property. No outcome is inferred.</p>
    </div>

    @if(session('lwa_success'))<div class="lwa-card p-3 mb-3 text-xs" style="color: var(--lwa-success)">{{ session('lwa_success') }}</div>@endif
    @if(session('lwa_error'))<div class="lwa-card p-3 mb-3 text-xs" style="color: var(--lwa-warning)">{{ session('lwa_error') }}</div>@endif
    @if($errors->any())
        <div class="lwa-card p-3 mb-3 text-xs" style="color: var(--lwa-warning)">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    @unless($ready)
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Experiment data unavailable</div>
            <p class="lwa-subtitle mt-1">Enable feature flags and run migrations to create <code class="font-mono">lwa_experiments</code>.</p>
        </div>
    @endunless

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_experiments') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="lwa-field-label" for="experimentRange">Window</label>
                <select id="experimentRange" name="range" class="lwa-select">
                    @foreach(['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'all' => 'All time'] as $value => $label)
                        <option value="{{ $value }}" {{ $range === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="lwa-btn-primary">Apply</button>
        </form>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border flex items-center justify-between gap-3">
            <div class="lwa-card-title">{{ $editing ? 'Edit experiment' : 'Create experiment' }}</div>
            @if($editing)<a href="{{ route('lwa_experiments') }}" class="lwa-btn-ghost">Cancel edit</a>@endif
        </div>
        <form method="POST" action="{{ $editing ? route('lwa_experiments_update', $editing->id) : route('lwa_experiments_store') }}" class="p-4 space-y-3">
            @csrf
            @if($editing)@method('PUT')@endif
            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="lwa-field-label" for="experimentName">Name</label>
                    <input id="experimentName" name="name" required maxlength="150" class="lwa-input" value="{{ old('name', $editing->name ?? '') }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="experimentFlag">Flag key</label>
                    <select id="experimentFlag" name="flag_key" class="lwa-select">
                        @foreach($flags as $flag)
                            <option value="{{ $flag->key }}" {{ old('flag_key', $editing->flag_key ?? '') === $flag->key ? 'selected' : '' }}>{{ $flag->key }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="experimentGoal">Goal event</label>
                    <input id="experimentGoal" name="goal_event" required maxlength="100" class="lwa-input font-mono" value="{{ old('goal_event', $editing->goal_event ?? '') }}" placeholder="purchase">
                </div>
            </div>
            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="lwa-field-label" for="experimentVariantProperty">Variant property</label>
                    <input id="experimentVariantProperty" name="variant_property" maxlength="100" class="lwa-input font-mono" value="{{ old('variant_property', $editing->variant_property ?? 'variant') }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="experimentControl">Control variant</label>
                    <input id="experimentControl" name="control_variant" maxlength="100" class="lwa-input font-mono" value="{{ old('control_variant', $editing->control_variant ?? 'control') }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="experimentStatus">Status</label>
                    <select id="experimentStatus" name="status" class="lwa-select">
                        @foreach(['draft' => 'Draft', 'running' => 'Running', 'stopped' => 'Stopped'] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $editing->status ?? 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="lwa-btn-primary">{{ $editing ? 'Update experiment' : 'Create experiment' }}</button>
        </form>
    </div>

    @forelse($result['experiments'] as $item)
        <div class="lwa-card overflow-hidden">
            <div class="p-4 border-b lwa-border flex items-center justify-between gap-3">
                <div>
                    <div class="lwa-card-title">{{ $item['name'] }}</div>
                    <p class="lwa-subtitle mt-1">
                        Flag <code class="font-mono">{{ $item['flag_key'] }}</code> · goal <code class="font-mono">{{ $item['goal_event'] }}</code> with <code class="font-mono">{{ $item['variant_property'] }}</code> · {{ $item['status'] }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('lwa_experiments_edit', $item['id']) }}" class="lwa-btn-ghost">Edit</a>
                    <form method="POST" action="{{ route('lwa_experiments_destroy', $item['id']) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="lwa-btn-ghost" onclick="return confirm('Delete this experiment?')">Delete</button>
                    </form>
                </div>
            </div>
            <div class="lwa-table-wrap">
                <table class="lwa-table w-full">
                    <thead><tr><th>Variant</th><th>Exposures</th><th>Goals</th><th>Rate</th><th>Lift</th><th>p-value</th><th>Significant</th></tr></thead>
                    <tbody>
                        @foreach($item['variants'] as $variant)
                            <tr>
                                <td class="font-mono">{{ $variant['variant'] }}{{ $variant['is_control'] ? ' (control)' : '' }}</td>
                                <td>{{ number_format($variant['exposures']) }}</td>
                                <td>{{ number_format($variant['conversions']) }}</td>
                                <td>{{ number_format($variant['rate'], 2) }}%</td>
                                <td>{{ $variant['lift_percent'] === null ? '—' : number_format($variant['lift_percent'], 2) . '%' }}</td>
                                <td>{{ $variant['p_value'] === null ? '—' : number_format($variant['p_value'], 4) }}</td>
                                <td>{{ $variant['significant'] ? 'Yes' : 'No / insufficient sample' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 text-[11px]" style="color: var(--lwa-muted)">
                Exposure rows scanned: {{ number_format($item['exposure_events']) }} · goal rows scanned: {{ number_format($item['goal_events']) }}
            </div>
        </div>
    @empty
        <div class="lwa-card p-4 lwa-empty">
            <div class="lwa-empty-title">No experiments yet</div>
            <p>Link a flag to a recorded goal event to see variant results.</p>
        </div>
    @endforelse

    @if(!empty($result['definitions']))
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Definitions</div>
            <ul class="mt-2 space-y-1 text-xs" style="color: var(--lwa-muted)">
                @foreach($result['definitions'] as $key => $definition)
                    <li><span class="font-mono">{{ $key }}</span>: {{ $definition }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
