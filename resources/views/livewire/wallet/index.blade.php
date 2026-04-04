<div class="space-y-6">

    {{-- Flash messages --}}
    @if (session('wallet_success'))
        <div class="rounded-lg border border-green-700 bg-green-900/30 p-4 text-sm text-green-400">
            {{ session('wallet_success') }}
        </div>
    @endif

    <h1 class="text-3xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">💰 Wallet</h1>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Left panel: Balance cards & Actions --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Card 1: Main Wallet Balance --}}
            <div class="rounded-xl border border-[#f5c542]/40 bg-[#1a1a1a] p-6 shadow-[0_0_30px_rgba(245,197,66,0.08)]">
                <div class="mb-1 text-xs font-semibold uppercase tracking-widest text-[#6b6b6b]">Wallet Balance</div>
                <div class="mb-1 text-4xl font-black text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                    {{ session('currency.code', 'KES') }} {{ number_format($kadiCustomer['balance'] ?? 0, 2) }}
                </div>
                <div class="mb-4 text-xs text-[#6b6b6b]">
                    Account No: <span class="text-[#f5f5f0]/60 font-mono">{{ $kadiCustomer['account_no'] ?? '—' }}</span>
                </div>

                <div class="mb-2 h-px bg-yellow-800/20"></div>

                <div class="mt-4 space-y-3">
                    <button
                        wire:click="$set('showDepositModal', true)"
                        class="btn-casino-primary flex w-full items-center justify-center gap-2 rounded-full py-3 text-sm"
                    >
                        + Deposit Funds
                    </button>
                    <button
                        wire:click="$set('showWithdrawModal', true)"
                        class="btn-casino-ghost flex w-full items-center justify-center gap-2 rounded-full py-3 text-sm"
                    >
                        - Withdraw Funds
                    </button>
                </div>

                <p class="mt-4 text-center text-xs text-[#6b6b6b]">Minimum deposit/withdrawal: KES 10</p>
            </div>

            {{-- Card 2: Coins Wallet --}}
            <div class="rounded-xl border border-amber-600/30 bg-[#1a1a1a] p-6 shadow-[0_0_20px_rgba(251,191,36,0.05)]">
                <div class="mb-1 text-xs font-semibold uppercase tracking-widest text-amber-600/70">Coins Balance</div>
                <div class="mb-1 text-3xl font-black text-amber-400" style="font-family: 'Cinzel', serif;">
                    {{ number_format($kadiCustomer['coins'] ?? 0, 2) }}
                    <span class="text-base font-semibold text-amber-600/60">Coins</span>
                </div>
                <div class="text-xs text-[#6b6b6b]">
                    Coin Wallet ID: <span class="text-[#f5f5f0]/60 font-mono">{{ $kadiCustomer['coin_wallet_id'] ?? '—' }}</span>
                </div>
            </div>

        </div>

        {{-- Right panel: Transaction History --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-6">
                <h3 class="mb-5 text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Transaction History</h3>

                {{-- Filter tabs --}}
                <div class="mb-6 flex gap-2">
                    @foreach (['all' => 'All', 'deposits' => 'Deposits', 'withdrawals' => 'Withdrawals'] as $key => $label)
                        <button
                            wire:click="setFilter('{{ $key }}')"
                            @class([
                                'rounded-full px-4 py-1.5 text-sm font-semibold transition',
                                'btn-casino-primary'                                                 => $filter === $key,
                                'border border-yellow-800/40 text-[#6b6b6b] hover:text-[#f5f5f0]'  => $filter !== $key,
                            ])
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Loading state --}}
                <div wire:loading wire:target="setFilter, loadTransactions" class="py-8 text-center text-[#6b6b6b] text-sm">
                    <span class="animate-spin inline-block mr-2">⟳</span> Loading transactions...
                </div>

                {{-- Table --}}
                <div wire:loading.remove wire:target="setFilter, loadTransactions">
                    @if (empty($transactions))
                        <div class="py-16 text-center">
                            <div class="mb-3 text-4xl">🪙</div>
                            <p class="text-[#6b6b6b]">No transactions yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-yellow-800/20 text-left text-xs uppercase tracking-wider text-[#6b6b6b]">
                                        <th class="pb-3">Date</th>
                                        <th class="pb-3">Type</th>
                                        <th class="pb-3">Amount</th>
                                        <th class="pb-3">Status</th>
                                        <th class="pb-3">Reference</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-yellow-800/10">
                                    @foreach ($transactions as $tx)
                                        @php
                                            $isWithdrawal = str_contains($tx['payment_type'] ?? '', 'Withdraw');
                                            $status       = (int) ($tx['status'] ?? 0);
                                        @endphp
                                        <tr class="text-[#f5f5f0]/80">
                                            <td class="py-3 text-xs text-[#6b6b6b]">{{ $tx['created_at'] ?? '—' }}</td>
                                            <td class="py-3">
                                                @if ($isWithdrawal)
                                                    <span class="rounded-full bg-red-900/50 px-2.5 py-1 text-xs text-red-400 border border-red-700">Withdrawal</span>
                                                @else
                                                    <span class="rounded-full bg-green-900/50 px-2.5 py-1 text-xs text-green-400 border border-green-700">Deposit</span>
                                                @endif
                                            </td>
                                            <td class="py-3 font-semibold {{ $isWithdrawal ? 'text-red-400' : 'text-green-400' }}">
                                                {{ $isWithdrawal ? '-' : '+' }}{{ session('currency.code', 'KES') }} {{ number_format($tx['amount'] ?? 0, 2) }}
                                            </td>
                                            <td class="py-3">
                                                @if ($status === 2)
                                                    <span class="rounded-full bg-green-900/50 px-2.5 py-1 text-xs text-green-400">Completed</span>
                                                @elseif ($status === 1)
                                                    <span class="rounded-full bg-yellow-900/50 px-2.5 py-1 text-xs text-yellow-400">Pending</span>
                                                @else
                                                    <span class="rounded-full bg-red-900/50 px-2.5 py-1 text-xs text-red-400">Failed</span>
                                                @endif
                                            </td>
                                            <td class="py-3 font-mono text-xs text-[#6b6b6b]">{{ $tx['reference'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Deposit Modal (UI placeholder) --}}
    <flux:modal wire:model="showDepositModal" class="max-w-sm">
        <div class="p-6 space-y-5">
            <h3 class="text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">💰 Deposit Funds</h3>
            <p class="text-sm text-[#6b6b6b]">Deposit functionality coming soon. Please contact support to add funds to your account.</p>
            <flux:button wire:click="$set('showDepositModal', false)" variant="ghost" class="w-full">Close</flux:button>
        </div>
    </flux:modal>

    {{-- Withdraw Modal (UI placeholder) --}}
    <flux:modal wire:model="showWithdrawModal" class="max-w-sm">
        <div class="p-6 space-y-5">
            <h3 class="text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">💸 Withdraw Funds</h3>
            <p class="text-sm text-[#6b6b6b]">Withdrawal functionality coming soon. Please contact support to withdraw from your account.</p>
            <flux:button wire:click="$set('showWithdrawModal', false)" variant="ghost" class="w-full">Close</flux:button>
        </div>
    </flux:modal>

</div>
