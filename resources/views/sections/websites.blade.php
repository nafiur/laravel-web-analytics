                <section id="settings-websites" class="lwa-card overflow-hidden">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="text-xs font-semibold text-slate-900 dark:text-white">Websites</div>
                        <p class="lwa-subtitle mt-0.5">Register properties and switch the active dashboard scope</p>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                        @forelse($websites as $website)
                            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-slate-800 dark:text-slate-200 truncate">{{ $website->domain }}</span>
                                        @if(($activeWebsite?->id ?? null) === $website->id)
                                            <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300">Active</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5 font-mono">{{ $website->tracking_id }}</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if(($activeWebsite?->id ?? null) !== $website->id)
                                        <form method="POST" action="{{ route('lwa_websites_switch') }}">
                                            @csrf
                                            <input type="hidden" name="website" value="{{ $website->id }}">
                                            <button type="submit" class="px-2.5 py-1.5 text-[11px] font-medium text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-md hover:bg-slate-50 dark:hover:bg-slate-800">Switch</button>
                                        </form>
                                    @endif
                                    @if($websites->count() > 1)
                                        <form method="POST" action="{{ route('lwa_websites_destroy', $website->id) }}" onsubmit="return confirm('Remove this website?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="confirm" value="DELETE">
                                            <button type="submit" class="px-2.5 py-1.5 text-[11px] font-medium text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900 rounded-md hover:bg-rose-50 dark:hover:bg-rose-950/30">Remove</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-4 lwa-subtitle">No websites registered yet.</div>
                        @endforelse

                        <form method="POST" action="{{ route('lwa_websites_store') }}" class="px-4 py-3 flex flex-wrap items-end gap-2 bg-slate-50/60 dark:bg-slate-800/30">
                            @csrf
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-[10px] font-medium text-slate-500 dark:text-slate-400 mb-1">Name</label>
                                <input type="text" name="name" required maxlength="120" placeholder="My website"
                                    class="w-full text-xs px-2 py-1.5 border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                            </div>
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-[10px] font-medium text-slate-500 dark:text-slate-400 mb-1">Domain</label>
                                <input type="text" name="domain" required maxlength="190" placeholder="example.com"
                                    class="w-full text-xs px-2 py-1.5 border border-slate-200 dark:border-slate-700 rounded bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                            </div>
                            <button type="submit" class="lwa-btn-primary">Add website</button>
                        </form>
                    </div>
                </section>
