{{-- Full-table detail view opened from a breakdown card's grid icon. --}}
<div id="lwaBreakdownModal" class="fixed inset-0 z-50 hidden" data-lwa-breakdown-modal
     data-observability-url="{{ route('lwa_realtime') }}" role="dialog" aria-modal="true" aria-label="Breakdown details">
    <div class="absolute inset-0 bg-slate-900/40" data-lwa-modal-backdrop></div>
    <div class="relative mx-auto my-6 flex max-h-[88vh] w-[min(1100px,94vw)] flex-col overflow-hidden lwa-card" style="box-shadow: var(--lwa-shadow-pop)">
        <div class="flex items-start justify-between gap-3 border-b lwa-border px-5 py-3">
            <div>
                <div class="lwa-card-title" data-lwa-modal-title>Breakdown</div>
                <div class="lwa-subtitle mt-0.5" data-lwa-modal-subtitle></div>
            </div>
            <button type="button" data-lwa-modal-close
                    class="lwa-btn-ghost p-1"
                    aria-label="Close details">
                <i data-lucide="x" class="text-lg"></i>
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-2 border-b lwa-border px-5 py-3">
            <input type="search" data-lwa-modal-search placeholder="Search"
                   class="lwa-control flex-1 min-w-[200px]">
            <select data-lwa-modal-sort class="lwa-control">
                <option value="proportion">Proportion</option>
                <option value="value">Value</option>
                <option value="name">Name</option>
            </select>
            <select data-lwa-modal-limit class="lwa-control">
                <option value="10">10 items</option>
                <option value="25" selected>25 items</option>
                <option value="50">50 items</option>
            </select>
        </div>

        <div class="flex-1 overflow-auto">
            <table class="lwa-table">
                <thead>
                    <tr>
                        <th class="w-10"><input type="checkbox" data-modal-select-all aria-label="Select all"></th>
                        <th>Name</th>
                        <th class="w-1/3">Proportion</th>
                        <th class="w-24 text-right">Value</th>
                        <th class="w-12"></th>
                    </tr>
                </thead>
                <tbody data-lwa-modal-body class="divide-y divide-slate-100 dark:divide-slate-800"></tbody>
            </table>
            <div data-lwa-modal-empty class="hidden px-5 py-8 text-center lwa-subtitle">No matching rows</div>
        </div>
    </div>
</div>
