@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Feature Flags</h1>
        <p class="lwa-subtitle mt-1">Deterministic, website/tenant-scoped flags. Every evaluation is written to <code class="font-mono">lwa_events</code> as <code class="font-mono">feature_flag_evaluated</code>, with a hashed subject — never a raw user id.</p>
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
            <div class="lwa-card-title">Feature flag tables unavailable</div>
            <p class="lwa-subtitle mt-1">Enable flags and run migrations to create <code class="font-mono">lwa_feature_flags</code> and <code class="font-mono">lwa_flag_overrides</code>.</p>
        </div>
    @endunless

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border flex items-center justify-between gap-3">
            <div class="lwa-card-title">{{ $editing ? 'Edit flag' : 'Create flag' }}</div>
            @if($editing)<a href="{{ route('lwa_flags') }}" class="lwa-btn-ghost">Cancel edit</a>@endif
        </div>
        <form method="POST" action="{{ $editing ? route('lwa_flags_update', $editing->id) : route('lwa_flags_store') }}" class="p-4 space-y-3">
            @csrf
            @if($editing)@method('PUT')@endif
            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="lwa-field-label" for="flagKey">Key</label>
                    <input id="flagKey" name="key" required maxlength="100" class="lwa-input font-mono" value="{{ old('key', $editing->key ?? '') }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="flagName">Name</label>
                    <input id="flagName" name="name" maxlength="150" class="lwa-input" value="{{ old('name', $editing->name ?? '') }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="flagType">Type</label>
                    <select id="flagType" name="type" class="lwa-select">
                        @foreach(['boolean' => 'Boolean', 'multivariate' => 'Multivariate'] as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $editing->type ?? 'boolean') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid md:grid-cols-3 gap-3">
                <div class="flex items-end gap-2">
                    <input id="flagEnabled" type="checkbox" name="enabled" value="1" class="lwa-checkbox" {{ old('enabled', $editing->enabled ?? false) ? 'checked' : '' }}>
                    <label class="lwa-field-label" for="flagEnabled">Enabled</label>
                </div>
                <div>
                    <label class="lwa-field-label" for="flagRollout">Rollout %</label>
                    <input id="flagRollout" type="number" min="0" max="100" name="rollout" class="lwa-input" value="{{ old('rollout', $editing->rollout ?? 0) }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="flagDescription">Description</label>
                    <input id="flagDescription" name="description" maxlength="1000" class="lwa-input" value="{{ old('description', $editing->description ?? '') }}">
                </div>
            </div>
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="lwa-field-label" for="flagVariants">Variants (JSON)</label>
                    <textarea id="flagVariants" name="variants" rows="3" class="lwa-input font-mono" placeholder='[{"key":"control","weight":50},{"key":"test","weight":50}]'>{{ old('variants', $editing && $editing->variants ? json_encode($editing->variants) : '') }}</textarea>
                </div>
                <div>
                    <label class="lwa-field-label" for="flagTargeting">Targeting rules (JSON)</label>
                    <textarea id="flagTargeting" name="targeting" rows="3" class="lwa-input font-mono" placeholder='[{"attribute":"subject_key","operator":"in","value":["u_1","u_2"]}]'>{{ old('targeting', $editing && $editing->targeting ? json_encode($editing->targeting) : '') }}</textarea>
                </div>
            </div>
            <button type="submit" class="lwa-btn-primary">{{ $editing ? 'Update flag' : 'Create flag' }}</button>
        </form>
    </div>

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Flags in this scope</div></div>
        @if(empty($flags))
            <div class="p-4 lwa-empty"><div class="lwa-empty-title">No flags yet</div><p>Create one above, then evaluate it with <code class="font-mono">&#64;feature('key')</code> or the REST endpoint.</p></div>
        @else
            <div class="lwa-table-wrap">
                <table class="lwa-table w-full">
                    <thead><tr><th>Key</th><th>Type</th><th>Enabled</th><th>Rollout</th><th>Variants</th><th></th></tr></thead>
                    <tbody>
                        @foreach($flags as $flag)
                            <tr>
                                <td class="font-mono">{{ $flag->key }}</td>
                                <td>{{ $flag->type }}</td>
                                <td>{{ $flag->enabled ? 'Yes' : 'No' }}</td>
                                <td>{{ $flag->rollout }}%</td>
                                <td class="font-mono text-[11px]">{{ $flag->variants ? count($flag->variants) : '—' }}</td>
                                <td class="flex gap-2">
                                    <a href="{{ route('lwa_flags_edit', $flag->id) }}" class="lwa-btn-ghost">Edit</a>
                                    <form method="POST" action="{{ route('lwa_flags_destroy', $flag->id) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="lwa-btn-ghost" onclick="return confirm('Delete this flag?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="lwa-card p-4">
        <div class="lwa-card-title">Evaluation rules</div>
        <ul class="mt-2 space-y-1 text-xs" style="color: var(--lwa-muted)">
            <li>Scope comes from the active website and trusted tenant resolver — never from request input.</li>
            <li>Overrides win first (<code class="font-mono">off</code> disables); then all targeting rules must match; then enabled and the deterministic rollout bucket decide.</li>
            <li>Bucketing is <code class="font-mono">sha256(key:subject) % 100</code>, so the same subject sees the same variant every time.</li>
            <li>Each flag key + subject is logged once per process; exposure appears on Trends with metric <code class="font-mono">event count</code>.</li>
        </ul>
    </div>
@endsection
