{{-- Full-table detail view opened from a breakdown card's grid icon. --}}
<div id="lwaBreakdownModal" class="fixed inset-0 z-50 hidden" data-lwa-breakdown-modal
     data-observability-url="{{ route('lwa_realtime') }}" role="dialog" aria-modal="true" aria-label="Breakdown details">
    <div class="absolute inset-0 bg-slate-900/40" data-lwa-modal-backdrop></div>
    <div class="relative mx-auto my-6 flex max-h-[88vh] w-[min(1100px,94vw)] flex-col overflow-hidden rounded-lg border lwa-border bg-white shadow-xl dark:bg-slate-900">
        <div class="flex items-start justify-between gap-3 border-b lwa-border px-5 py-3">
            <div>
                <div class="text-sm font-semibold text-slate-900 dark:text-white" data-lwa-modal-title>Breakdown</div>
                <div class="text-[11px] text-slate-400 mt-0.5" data-lwa-modal-subtitle></div>
            </div>
            <button type="button" data-lwa-modal-close
                    class="p-1 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition"
                    aria-label="Close details">
                <i data-lucide="x" class="text-lg"></i>
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2 border-b lwa-border px-5 py-3">
            <input type="search" data-lwa-modal-search placeholder="Search"
                   class="flex-1 min-w-[200px] rounded-md border lwa-border bg-white dark:bg-slate-800 px-3 py-1.5 text-xs text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500">
            <select data-lwa-modal-sort
                    class="rounded-md border lwa-border bg-white dark:bg-slate-800 px-2 py-1.5 text-xs text-slate-700 dark:text-slate-200">
                <option value="proportion">Proportion</option>
                <option value="value">Value</option>
                <option value="name">Name</option>
            </select>
            <select data-lwa-modal-limit
                    class="rounded-md border lwa-border bg-white dark:bg-slate-800 px-2 py-1.5 text-xs text-slate-700 dark:text-slate-200">
                <option value="10">10 items</option>
                <option value="25" selected>25 items</option>
                <option value="50">50 items</option>
            </select>
        </div>

        <div class="flex-1 overflow-auto">
            <table class="w-full text-xs">
                <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800/80">
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400">
                        <th class="w-10 px-4 py-2"><input type="checkbox" data-modal-select-all aria-label="Select all"></th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2 w-1/3">Proportion</th>
                        <th class="px-4 py-2 w-24 text-right">Value</th>
                        <th class="w-12 px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody data-lwa-modal-body class="divide-y divide-slate-100 dark:divide-slate-800"></tbody>
            </table>
            <div data-lwa-modal-empty class="hidden px-5 py-8 text-center text-xs text-slate-400">No matching rows</div>
        </div>
    </div>
</div>
