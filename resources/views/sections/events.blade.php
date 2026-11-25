                <div>
                    <h1 class="lwa-page-title">Events &amp; Goals</h1>
                    <p class="lwa-subtitle mt-1">Custom events and goals collected from your application.</p>
                </div>

                <div class="lwa-card overflow-hidden">
                    <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="text-xs font-semibold text-slate-900 dark:text-white">{{ $title ?? 'Recent Events' }}</div>
                        <p class="lwa-subtitle mt-0.5">Collected via <code class="font-mono">Analytics::event()</code>, <code class="font-mono">Analytics::goal()</code> or the REST API</p>
                    </div>
                    @if(empty($recentEvents))
                        <div class="px-4 py-10 text-center">
                            <div class="mx-auto w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-3">
                                <i data-lucide="target" class="text-lg text-amber-500"></i>
                            </div>
                            <div class="lwa-card-title">No events recorded yet</div>
                            <p class="lwa-subtitle mt-1">Track one from your application:</p>
                            <code class="inline-block mt-2 text-[11px] px-2 py-1 rounded bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300">Analytics::event('purchase', ['plan' =&gt; 'pro'], 1500);</code>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left lwa-table">
                                <thead>
                                    <tr>
                                        <th class="py-2.5 px-4 font-semibold">Event</th>
                                        <th class="py-2.5 px-4 font-semibold">Properties</th>
                                        <th class="py-2.5 px-4 font-semibold text-right">Value</th>
                                        <th class="py-2.5 px-4 font-semibold text-right">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach($recentEvents as $event)
                                        <tr>
                                            <td class="py-2 px-4 font-mono font-medium text-slate-800 dark:text-slate-200">{{ $event['name'] }}</td>
                                            <td class="py-2 px-4 font-mono text-slate-500 truncate max-w-md">{{ json_encode($event['properties']) }}</td>
                                            <td class="py-2 px-4 text-right font-mono text-slate-600 dark:text-slate-300">{{ $event['value'] !== null ? $event['value'] : '—' }}</td>
                                            <td class="py-2 px-4 text-right text-slate-400" data-utc="{{ $event['created_at'] ?? '' }}">{{ $event['created_at'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
