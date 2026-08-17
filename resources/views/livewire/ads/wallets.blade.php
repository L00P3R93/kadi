<div class="min-h-screen bg-[#0a0a0a] pt-14 pb-20" @if ($this->hasPendingTopUp) wire:poll.5s="refreshWallet" @endif>

    {{-- Ambient background glow --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 h-[420px] -z-0"
         style="background: radial-gradient(60% 60% at 50% 0%, rgba(245,197,66,0.06) 0%, transparent 70%);"></div>

    <div class="relative mx-auto max-w-5xl px-6">

        {{-- ═══ Header ═══ --}}
        <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="font-cinzel text-3xl font-bold text-[#f5f5f0]">Ad Wallet</h1>
                <p class="mt-1 text-sm text-[#6b6b6b]">Fund your campaigns and track every charge, top-up, and refund.</p>
            </div>

            <a href="{{ url('/marketing') }}" class="btn-casino-ghost flex items-center gap-2 rounded-full px-5 py-2.5 text-xs whitespace-nowrap">
                <span class="text-base leading-none">
                    <svg class="w-5 h-5 flex-shrink-0"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor"
                         stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M4 11v2a2 2 0 002 2h2l3 4h2l-1.5-4H15l5 3V6l-5 3H6a2 2 0 00-2 2z"/>
                    </svg>
                </span> Manage Campaigns
            </a>

            <button type="button" wire:click="openTopUpModal" class="btn-casino-primary flex items-center gap-2 rounded-full px-5 py-2.5 text-xs whitespace-nowrap">
                <span class="text-base leading-none">+</span> Add Funds
            </button>
        </div>

        {{-- ═══ Balance card ═══ --}}
        <div class="glass-card mb-8 flex flex-col items-center justify-between gap-4 p-6 sm:flex-row">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-full border-2 border-dashed border-[#f5c542]/40"
                     style="background: radial-gradient(circle at 50% 40%, rgba(245,197,66,0.22), rgba(245,197,66,0.04));">
                    <span class="text-2xl">💼</span>
                </div>
                <div>
                    <div class="text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Available Balance</div>
                    <div class="font-cinzel text-2xl font-black text-[#f5c542]">
                        {{ $wallet->currency }} {{ number_format($wallet->balance, 2) }}
                    </div>
                </div>
            </div>

            @if ($this->hasPendingTopUp)
                <div class="flex items-center gap-2 rounded-lg border border-orange-700 bg-orange-900/30 px-4 py-2.5 text-xs text-orange-400">
                    <span class="inline-block animate-spin">⟳</span> Waiting for M-Pesa confirmation...
                </div>
            @else
                <div class="flex items-center gap-4 text-[11px] text-[#6b6b6b]">
                    <div class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 text-green-500" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        Funds spend only on campaigns you approve
                    </div>
                </div>
            @endif
        </div>

        {{-- ═══ Transactions ═══ --}}
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-cinzel text-lg font-bold text-[#f5f5f0]">Transaction History</h2>

            <select wire:model.live="typeFilter"
                    class="rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-xs text-[#f5f5f0] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                <option value="all">All Types</option>
                <option value="topup">Top Up</option>
                <option value="campaign_reserve">Campaign Reserve</option>
                <option value="campaign_release">Campaign Release</option>
                <option value="view_charge">View Charge</option>
                <option value="click_charge">Click Charge</option>
                <option value="refund">Refund</option>
                <option value="adjustment">Adjustment</option>
            </select>
        </div>

        <div class="glass-card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] border-collapse text-left">
                    <thead>
                    <tr class="border-b border-[#f5c542]/10">
                        <th class="px-6 py-3 text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Type</th>
                        <th class="px-6 py-3 text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Description</th>
                        <th class="px-6 py-3 text-right text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Amount</th>
                        <th class="px-6 py-3 text-right text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Balance After</th>
                        <th class="px-6 py-3 text-right text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Date</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                    @forelse ($transactions as $transaction)
                        @php
                            $typeMeta = [
                                'topup' => ['label' => 'Top Up', 'badge' => 'border-green-800 bg-green-950/30 text-green-400'],
                                'campaign_reserve' => ['label' => 'Campaign Reserve', 'badge' => 'border-blue-800 bg-blue-950/30 text-blue-400'],
                                'campaign_release' => ['label' => 'Campaign Release', 'badge' => 'border-blue-800 bg-blue-950/30 text-blue-400'],
                                'view_charge' => ['label' => 'View Charge', 'badge' => 'border-red-800 bg-red-950/30 text-red-400'],
                                'click_charge' => ['label' => 'Click Charge', 'badge' => 'border-red-800 bg-red-950/30 text-red-400'],
                                'refund' => ['label' => 'Refund', 'badge' => 'border-green-800 bg-green-950/30 text-green-400'],
                                'adjustment' => ['label' => 'Adjustment', 'badge' => 'border-purple-800 bg-purple-950/30 text-purple-400'],
                            ][$transaction->type] ?? ['label' => ucfirst(str_replace('_', ' ', $transaction->type)), 'badge' => 'border-gray-700 bg-gray-900/30 text-gray-400'];
                        @endphp
                        <tr class="transition hover:bg-white/[0.02]" wire:key="txn-{{ $transaction->id }}">
                            <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full border {{ $typeMeta['badge'] }} px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
                                        {{ $typeMeta['label'] }}
                                    </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-[#f5f5f0]/70">
                                {{ $transaction->description ?: '—' }}
                            </td>
                            <td class="px-6 py-4 text-right font-cinzel text-sm font-bold {{ $transaction->amount >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-[#f5f5f0]/70">
                                {{ $wallet->currency }} {{ number_format($transaction->balance_after, 2) }}
                            </td>
                            <td class="px-6 py-4 text-right text-[11px] text-[#6b6b6b]">
                                {{ $transaction->created_at->format('d M, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-[#6b6b6b]">
                                @if ($typeFilter !== 'all')
                                    No {{ str_replace('_', ' ', $typeFilter) }} transactions yet.
                                @else
                                    No transactions yet — top up your wallet to get started.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ═══ Pagination footer ═══ --}}
            @if ($transactions->total() > 0)
                <div class="flex flex-col items-center justify-between gap-3 border-t border-[#f5c542]/10 px-6 py-4 sm:flex-row">
                    <p class="text-[11px] text-[#6b6b6b]">
                        Showing <span class="font-semibold text-[#f5f5f0]">{{ $transactions->firstItem() }}</span>
                        to <span class="font-semibold text-[#f5f5f0]">{{ $transactions->lastItem() }}</span>
                        of <span class="font-semibold text-[#f5f5f0]">{{ $transactions->total() }}</span> transactions
                    </p>

                    <div class="flex items-center gap-1">
                        <button type="button" wire:click="previousPage" @disabled($transactions->onFirstPage())
                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#f5c542]/15 text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40 hover:text-[#f5c542] disabled:cursor-not-allowed disabled:opacity-30">
                            ‹
                        </button>

                        @php
                            $current = $transactions->currentPage();
                            $last = $transactions->lastPage();
                            $window = collect(range(max(1, $current - 1), min($last, $current + 1)));
                            if (! $window->contains(1)) $window->prepend(1);
                            if (! $window->contains($last)) $window->push($last);
                            $pages = $window->unique()->sort()->values();
                        @endphp

                        @foreach ($pages as $i => $page)
                            @if ($i > 0 && $page - $pages[$i - 1] > 1)
                                <span class="px-1.5 text-[11px] text-[#6b6b6b]">…</span>
                            @endif
                            <button type="button" wire:click="gotoPage({{ $page }})"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg text-[11px] font-semibold transition
                                           {{ $page === $current ? 'bg-[#f5c542] text-black' : 'border border-[#f5c542]/15 text-[#f5f5f0]/70 hover:border-[#f5c542]/40 hover:text-[#f5c542]' }}">
                                {{ $page }}
                            </button>
                        @endforeach

                        <button type="button" wire:click="nextPage" @disabled($transactions->onLastPage())
                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-[#f5c542]/15 text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40 hover:text-[#f5c542] disabled:cursor-not-allowed disabled:opacity-30">
                            ›
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ Top-up modal ═══ --}}
    @if ($showTopUpModal)
        <div
            x-data
            x-on:keydown.escape.window="$wire.closeTopUpModal()"
            wire:click.self="closeTopUpModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4 backdrop-blur-sm"
        >
            <div class="glass-card relative w-full max-w-sm rounded-2xl border border-[#f5c542]/30 p-6">
                <button type="button" wire:click="closeTopUpModal"
                        class="absolute right-4 top-4 text-[#6b6b6b] transition hover:text-[#f5c542]">✕</button>

                @if ($topUpSuccess)
                    {{-- ═══ Success state ═══ --}}
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border-2 border-[#f5c542]/40 bg-black/40">
                            <span class="text-2xl">📲</span>
                        </div>
                        <h3 class="mb-2 font-cinzel text-lg font-bold text-[#f5f5f0]">Check Your Phone</h3>
                        <p class="mb-5 text-xs leading-relaxed text-[#6b6b6b]">
                            We've sent an M-Pesa STK push to <span class="text-[#f5f5f0]">{{ $phone_number }}</span>.
                            Enter your PIN to complete the payment — your balance updates automatically once it's confirmed.
                        </p>
                        <button type="button" wire:click="closeTopUpModal"
                                class="btn-casino-primary w-full rounded-lg py-2.5 text-xs">
                            Done
                        </button>
                    </div>
                @elseif (! $confirmStep)
                    {{-- ═══ Step 1: amount + phone ═══ --}}
                    <h3 class="mb-5 font-cinzel text-lg font-bold text-[#f5f5f0]">Add Funds</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">Amount (KES)</label>
                            <input type="number" step="1" min="10" wire:model.live="amount" placeholder="e.g. 5000"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('amount') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-widest text-[#6b6b6b]">M-Pesa Phone Number</label>
                            <input type="text" wire:model.live="phone_number" placeholder="07XXXXXXXX"
                                   class="w-full rounded-lg border border-[#f5c542]/15 bg-black/30 px-3 py-2.5 text-sm text-[#f5f5f0] placeholder:text-[#6b6b6b] focus:border-[#f5c542]/50 focus:outline-none focus:ring-1 focus:ring-[#f5c542]/30">
                            @error('phone_number') <p class="mt-1 text-[11px] text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <button type="button" wire:click="proceedToConfirm"
                                wire:loading.attr="disabled" wire:target="proceedToConfirm"
                                class="btn-casino-primary flex w-full items-center justify-center gap-2 rounded-lg py-2.5 text-xs disabled:cursor-not-allowed disabled:opacity-60">
                            Review Payment
                        </button>
                    </div>
                @else
                    {{-- ═══ Step 2: confirm ═══ --}}
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full border-2 border-[#f5c542]/40 bg-black/40">
                            <span class="text-2xl">💼</span>
                        </div>

                        <h3 class="font-cinzel text-lg font-bold text-[#f5f5f0]">Confirm Top-Up</h3>

                        <div class="my-4 rounded-xl border border-[#f5c542]/15 bg-black/30 p-4">
                            <div class="font-cinzel text-xl font-black text-[#f5c542]">KES {{ number_format((float) $amount, 2) }}</div>
                            <div class="mt-1 text-sm text-[#6b6b6b]">to {{ $phone_number }}</div>
                        </div>

                        <p class="mb-5 text-xs leading-relaxed text-[#6b6b6b]">
                            We'll send an M-Pesa STK push to this number. Enter your M-Pesa PIN on your phone to complete the payment.
                        </p>

                        <div class="flex gap-3">
                            <button type="button" wire:click="backToForm"
                                    wire:loading.attr="disabled" wire:target="initiateTopUp"
                                    class="flex-1 rounded-lg border border-[#f5c542]/20 py-2.5 text-xs font-semibold text-[#f5f5f0]/70 transition hover:border-[#f5c542]/40 disabled:cursor-not-allowed disabled:opacity-40">
                                Back
                            </button>
                            <button type="button" wire:click="initiateTopUp"
                                    wire:loading.attr="disabled" wire:target="initiateTopUp"
                                    class="btn-casino-primary flex flex-1 items-center justify-center gap-2 rounded-lg py-2.5 text-xs disabled:cursor-not-allowed disabled:opacity-60">
                                <span wire:loading.remove wire:target="initiateTopUp">Confirm &amp; Pay</span>
                                <span wire:loading wire:target="initiateTopUp" class="inline-flex items-center gap-1.5">
                                    <span class="inline-block animate-spin">⟳</span> Sending...
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
