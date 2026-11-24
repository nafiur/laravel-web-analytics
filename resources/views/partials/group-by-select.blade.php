@php
    $chartId = $chartId ?? '';
    $defaultGroup = $default ?? '';
@endphp
<div class="flex items-center gap-2 text-[11px]">
    <span class="text-slate-400">Group by:</span>
    <select data-lwa-group-by data-chart-id="{{ $chartId }}"
            class="text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded px-1.5 py-0.5 text-slate-700 dark:text-slate-300 focus:outline-none">
        <option value="" {{ $defaultGroup === '' ? 'selected' : '' }}>None</option>
        <option value="top_countries">Country</option>
        <option value="top_paths">Path</option>
        <option value="top_status_codes">Origin status code</option>
        <option value="top_browsers">Browser</option>
        <option value="top_os">Operating system</option>
        <option value="top_devices">Device type</option>
        <option value="top_methods">HTTP protocol</option>
        <option value="top_referrers">Referrer</option>
        <option value="top_cities">Data center</option>
        <option value="top_hosts">Host</option>
        <option value="top_cache_statuses" {{ $defaultGroup === 'top_cache_statuses' ? 'selected' : '' }}>Cache status (all)</option>
    </select>
</div>
