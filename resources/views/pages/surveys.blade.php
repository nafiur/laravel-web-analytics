@extends('web-analytics::layouts.app')

@section('content')
    <div>
        <h1 class="lwa-page-title">Surveys</h1>
        <p class="lwa-subtitle mt-1">NPS and text feedback collected through a build-free widget. Responses are scoped to the active website and tenant, and mirrored as safe events in <code class="font-mono">lwa_events</code>.</p>
    </div>

    @if(session('lwa_success'))<div class="lwa-card p-3 mb-3 text-xs" style="color: var(--lwa-success)">{{ session('lwa_success') }}</div>@endif
    @if(session('lwa_error'))<div class="lwa-card p-3 mb-3 text-xs" style="color: var(--lwa-warning)">{{ session('lwa_error') }}</div>@endif
    @if($errors->any())
        <div class="lwa-card p-3 mb-3 text-xs" style="color: var(--lwa-warning)">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="lwa-card p-4">
        <form method="GET" action="{{ route('lwa_surveys') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="lwa-field-label" for="surveyRange">Window</label>
                <select id="surveyRange" name="range" class="lwa-select">
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
            <div class="lwa-card-title">{{ $editing ? 'Edit survey' : 'Create survey' }}</div>
            @if($editing)<a href="{{ route('lwa_surveys') }}" class="lwa-btn-ghost">Cancel edit</a>@endif
        </div>
        <form method="POST" action="{{ $editing ? route('lwa_surveys_update', $editing->id) : route('lwa_surveys_store') }}" class="p-4 space-y-3">
            @csrf
            @if($editing)@method('PUT')@endif
            <div class="grid md:grid-cols-2 gap-3">
                <div>
                    <label class="lwa-field-label" for="surveyKey">Key</label>
                    <input id="surveyKey" name="key" required maxlength="100" class="lwa-input font-mono" value="{{ old('key', $editing->key ?? '') }}">
                </div>
                <div>
                    <label class="lwa-field-label" for="surveyName">Name</label>
                    <input id="surveyName" name="name" required maxlength="150" class="lwa-input" value="{{ old('name', $editing->name ?? '') }}">
                </div>
            </div>
            <div class="grid md:grid-cols-3 gap-3">
                <div>
                    <label class="lwa-field-label" for="surveyType">Type</label>
                    <select id="surveyType" name="type" class="lwa-select">
                        @foreach(['nps' => 'NPS 0–10', 'text' => 'Text feedback'] as $value => $label)
                            <option value="{{ $value }}" {{ old('type', $editing->type ?? 'nps') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="surveyStatus">Status</label>
                    <select id="surveyStatus" name="status" class="lwa-select">
                        @foreach(['draft' => 'Draft', 'running' => 'Running', 'stopped' => 'Stopped'] as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $editing->status ?? 'draft') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="lwa-field-label" for="surveyPath">Target path (optional)</label>
                    <input id="surveyPath" name="target_path" maxlength="500" class="lwa-input font-mono" value="{{ old('target_path', $editing->target_path ?? '') }}">
                </div>
            </div>
            <div>
                <label class="lwa-field-label" for="surveyQuestion">Question</label>
                <input id="surveyQuestion" name="question" required maxlength="1000" class="lwa-input" value="{{ old('question', $editing->question ?? '') }}">
            </div>
            <button type="submit" class="lwa-btn-primary">{{ $editing ? 'Update survey' : 'Create survey' }}</button>
        </form>
    </div>

    @if($editing && $result)
        <div class="lwa-card p-4">
            <div class="lwa-card-title">Results — {{ $editing->name }}</div>
            <p class="lwa-subtitle mt-1">Embed with <code class="font-mono">{{ "@lwaSurvey('" . $editing->key . "')" }}</code>. Only running surveys accept responses.</p>
            <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Responses</div><div class="lwa-kpi-value">{{ number_format($result['sample_size']) }}</div></div>
                @if($result['type'] === 'nps')
                    <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">NPS</div><div class="lwa-kpi-value">{{ $result['nps']['score'] }}</div></div>
                    <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Promoters</div><div class="lwa-kpi-value">{{ number_format($result['nps']['promoters']) }}</div></div>
                    <div class="lwa-card lwa-kpi"><div class="lwa-kpi-label">Detractors</div><div class="lwa-kpi-value">{{ number_format($result['nps']['detractors']) }}</div></div>
                @endif
            </div>
            @if($result['type'] === 'nps')
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach($result['nps']['buckets'] as $score => $count)
                        <span class="px-2 py-1 rounded text-[11px]" style="background: var(--lwa-surface-muted)" title="Score {{ $score }}">{{ $score }}: {{ $count }}</span>
                    @endforeach
                </div>
            @else
                <div class="mt-3 space-y-1">
                    @forelse($result['answers'] as $item)
                        <div class="flex justify-between gap-3 text-xs border-b lwa-border py-1"><span class="truncate">{{ $item['answer'] }}</span><span>{{ number_format($item['count']) }}</span></div>
                    @empty
                        <div class="lwa-subtitle">No text answers recorded.</div>
                    @endforelse
                </div>
            @endif
            @if($result['capped'] ?? false)
                <div class="mt-3 text-xs" style="color: var(--lwa-warning)">Sample cap reached: counts are lower bounds.</div>
            @endif
        </div>
    @endif

    <div class="lwa-card overflow-hidden">
        <div class="p-4 border-b lwa-border"><div class="lwa-card-title">Surveys</div></div>
        @if(empty($surveys))
            <div class="p-4 lwa-empty"><div class="lwa-empty-title">No surveys yet</div><p>Create one above, then embed it with the Blade directive.</p></div>
        @else
            <div class="lwa-table-wrap">
                <table class="lwa-table w-full">
                    <thead><tr><th>Key</th><th>Name</th><th>Type</th><th>Status</th><th>Responses</th><th></th></tr></thead>
                    <tbody>
                        @foreach($surveys as $item)
                            <tr>
                                <td class="font-mono">{{ $item->key }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->status }}</td>
                                <td>{{ number_format($item->responses_count) }}</td>
                                <td class="flex gap-2">
                                    <a href="{{ route('lwa_surveys_edit', $item->id) }}" class="lwa-btn-ghost">Results</a>
                                    <form method="POST" action="{{ route('lwa_surveys_destroy', $item->id) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="lwa-btn-ghost" onclick="return confirm('Delete this survey?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($result)
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
