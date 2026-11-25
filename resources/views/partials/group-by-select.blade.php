@php
    $chartId = $chartId ?? '';
    $defaultGroup = $default ?? '';
@endphp
<div class="flex items-center gap-1.5 text-[11px]">
    <span class="text-slate-400 hidden sm:inline">Group by:</span>
    <select data-lwa-group-by data-chart-id="{{ $chartId }}"
            class="lwa-control">
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
