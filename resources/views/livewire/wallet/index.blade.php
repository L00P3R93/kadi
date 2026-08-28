<div class="space-y-6" @if ($needsLoad) wire:init="refreshCustomer" @endif>

    {{-- Flash messages --}}
    @if (session('wallet_success'))
        <div class="rounded-lg border border-green-700 bg-green-900/30 p-4 text-sm text-green-400">
            {{ session('wallet_success') }}
        </div>
    @endif

    @if ($purchaseError)
        <div class="rounded-lg border border-red-700 bg-red-900/30 p-4 text-sm text-red-400">
           {{ $purchaseError }}
        </div>
    @endif

    @if ($withdrawError)
        <div class="rounded-lg border border-red-700 bg-red-900/30 p-4 text-sm text-red-400">
           {{ $withdrawError }}
        </div>
    @endif

    @if ($successMessage)
        <div class="rounded-lg border border-green-700 bg-green-900/30 p-4 text-sm text-green-400">
           {{ $successMessage }}
        </div>
    @endif

    <h1 class="text-3xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">💰 Vault</h1>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Left panel: Balance cards & Actions --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Card 1: Main Wallet Balance --}}
            <div class="rounded-xl border border-[#f5c542]/40 bg-[#1a1a1a] p-6 shadow-[0_0_30px_rgba(245,197,66,0.08)]">
                <div class="mb-1 text-xs font-semibold uppercase tracking-widest text-[#6b6b6b]">Vault Balance</div>
                <div class="mb-1 text-4xl font-black text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                    {{ $walletCurrencyLabel }} {{ number_format($balance) }}
                </div>
                <div class="mb-4 text-xs text-[#6b6b6b]">
                    Account No: <span class="text-[#f5f5f0]/60 font-mono">{{ $kadiCustomer['account_no'] ?? '—' }}</span>
                </div>


                <div class="mb-2 h-px bg-yellow-800/20"></div>
                @if($coinsLogicEnabled)
                    <div class="mt-4 space-y-3">
                        <a
                            href="{{ route('buy-coins') }}"
                            wire:navigate
                            class="btn-casino-primary flex w-full items-center justify-center gap-2 rounded-full py-3 text-sm"
                        >
                            + Buy Coins
                        </a>
                        <a
                            href="{{ route('earn-coins') }}"
                            wire:navigate
                            class="btn-casino-ghost flex w-full items-center justify-center gap-2 rounded-full py-3 text-sm"
                        >
                            + Earn Coins
                        </a>
                    </div>
                @endif

                @if($depositWithdrawEnabled)
                    <div class="mt-4 space-y-3">
                        <button
                            wire:click="openDeposit"
                            class="btn-casino-primary flex w-full items-center justify-center gap-2 rounded-full py-3 text-sm"
                        >
                            + Deposit Funds
                        </button>
                        <button
                            wire:click="openWithdraw"
                            class="btn-casino-ghost flex w-full items-center justify-center gap-2 rounded-full py-3 text-sm"
                        >
                            - Withdraw Funds
                        </button>
                    </div>

                    <p class="mt-4 text-center text-xs text-[#6b6b6b]">Minimum deposit: KES 10 · Minimum withdrawal: KES 100</p>
                @endif
            </div>

            {{-- Card 2: Coins Wallet --}}
            @if($coinsWalletCardEnabled)
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
            @endif

            {{-- Card 3: Load Wallet / Purchases
            <div class="load-wallet-card rounded-xl border border-[#f5c542]/30 bg-[#1a1a1a] p-6">
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Load Wallet</h3>
                    <span class="stat-badge">
                        <span class="mpesa-dot"></span>
                        M-Pesa
                    </span>
                </div>
                <p class="mb-5 text-xs text-[#6b6b6b]">Tap a pack to pay instantly via M-Pesa STK push.</p>

                <div
                    wire:loading
                    wire:target="initiatePurchase"
                    class="mb-4 flex items-center justify-center gap-2 rounded-lg border border-orange-700 bg-orange-900/30 p-4 text-xs text-orange-400"
                >
                    <span class="animate-spin inline-block">⟳</span> Sending STK push to your phone...
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($purchaseOptions as $index => $option)
                        <button
                            type="button"
                            wire:click="initiatePurchase({{ $index }})"
                            wire:loading.attr="disabled"
                            wire:target="initiatePurchase"
                            class="purchase-tile purchase-tile--{{ $option['type'] === 'coins' ? 'coins' : 'perk' }}"
                        >
                            @if ($option['best'] ?? false)
                                <span class="purchase-tile__ribbon">Best Value</span>
                            @endif

                            <span class="purchase-tile__shine"></span>

                            <span class="purchase-tile__icon-wrap">
                                <span class="purchase-tile__icon">
                                    @if ($option['type'] === 'emoji')
                                        😊
                                    @elseif ($option['type'] === 'gift')
                                        🎁
                                    @else
                                        💰
                                    @endif
                                </span>
                            </span>

                            <span class="purchase-tile__body">
                                @if ($option['type'] === 'emoji')
                                    <span class="purchase-tile__amount">Emojis</span>
                                    <span class="purchase-tile__meta">Pack</span>
                                @elseif ($option['type'] === 'gift')
                                    <span class="purchase-tile__amount">Gifts</span>
                                    <span class="purchase-tile__meta">Pack</span>
                                @else
                                    <span class="purchase-tile__amount">+{{ number_format($option['coins']) }}</span>
                                    <span class="purchase-tile__meta">Coins</span>
                                @endif
                            </span>

                            <span class="purchase-tile__footer">
                                <span class="purchase-tile__price">KSH {{ number_format($option['price']) }}</span>
                                <span class="purchase-tile__arrow">→</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
            --}}
        </div>

        {{-- Right panel: Transaction History --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-6">
                <h3 class="mb-5 text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Transaction History</h3>

                {{-- Filter tabs --}}
                <div class="mb-6 flex gap-2">
                    @foreach (['all' => 'All', 'deposit' => 'Deposits', 'withdrawal' => 'Withdrawals'] as $key => $label)
                        @continue($key === 'withdrawal' && ! $withdrawalsTabEnabled)
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
{{--                                        <th class="pb-3">Status</th>--}}
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
                                            <td class="py-3 text-xs text-[#6b6b6b]">{{ \Illuminate\Support\Carbon::parse($tx['created_at'])->diffForHumans()  ?? '—' }}</td>
                                            <td class="py-3">
                                                @if ($isWithdrawal)
                                                    <span class="rounded-full bg-red-900/50 px-2.5 py-1 text-xs text-red-400 border border-red-700">Withdrawal</span>
                                                @else
                                                    <span class="rounded-full bg-green-900/50 px-2.5 py-1 text-xs text-green-400 border border-green-700">Deposit</span>
                                                @endif
                                            </td>
                                            <td class="py-3 font-semibold {{ $isWithdrawal ? 'text-red-400' : 'text-green-400' }}">
                                                {{ $isWithdrawal ? '-' : '+' }}{{-- session('currency.code', 'KES') --}} {{ number_format($tx['amount'] ?? 0, 2) }}
                                            </td>
                                            {{--
                                            <td class="py-3">
                                                @if ($status === 2)
                                                    <span class="rounded-full bg-green-900/50 px-2.5 py-1 text-xs text-green-400">Completed</span>
                                                @elseif ($status === 1)
                                                    <span class="rounded-full bg-yellow-900/50 px-2.5 py-1 text-xs text-yellow-400">Pending</span>
                                                @else
                                                    <span class="rounded-full bg-red-900/50 px-2.5 py-1 text-xs text-red-400">Failed</span>
                                                @endif
                                            </td>
                                            --}}
                                            <td class="py-3 font-mono text-xs text-[#6b6b6b]">{{ $tx['payment_ref'] ?? '—' }}</td>
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

    {{-- Deposit Modal --}}
    <flux:modal wire:model.self="showDepositModal" class="kadi-bottom-sheet sm:max-w-sm">
        <div class="p-6 space-y-5">
            <div>
                <h3 class="text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">💰 Deposit Funds</h3>
                <p class="mt-1 text-sm text-[#6b6b6b]">Confirm the M-Pesa prompt on your phone to complete the deposit.</p>
            </div>

            @if (! $confirmingDeposit)
                {{-- Step 1: amount entry --}}
                <flux:field>
                    <flux:label>Amount (KES)</flux:label>
                    <flux:input
                        type="number"
                        wire:model="depositAmount"
                        min="10"
                        step="1"
                        inputmode="numeric"
                        placeholder="e.g. 100"
                    />
                    <flux:text class="mt-1 text-xs">Minimum deposit: <span class="font-semibold text-[#f5c542]">KES 10</span></flux:text>
                    <flux:error name="depositAmount" />
                </flux:field>

                {{-- Quick amounts --}}
                <div>
                    <p class="mb-2 text-xs uppercase tracking-widest text-[#6b6b6b]">Quick amounts</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ([20, 50, 100, 500, 1000, 5000] as $quick)
                            <button
                                type="button"
                                wire:click="$set('depositAmount', '{{ $quick }}')"
                                @class([
                                    'rounded-full border px-4 py-1.5 text-sm font-semibold transition',
                                    'border-[#f5c542]/25 text-[#f5f5f0]/80 hover:border-[#f5c542]/60 hover:bg-[#f5c542]/10' => (float) $depositAmount !== (float) $quick,
                                    'border-[#f5c542] bg-[#f5c542]/15 text-[#f5c542]' => (float) $depositAmount === (float) $quick,
                                ])
                            >
                                {{ number_format($quick) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                @if ($depositError)
                    <p class="text-sm font-medium text-red-400" role="alert">{{ $depositError }}</p>
                @endif

                <div class="flex gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="w-full">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button
                        variant="primary"
                        class="w-full"
                        wire:click="requestDeposit"
                        wire:loading.attr="disabled"
                        wire:target="requestDeposit"
                    >
                        Deposit
                    </flux:button>
                </div>
            @else
                {{-- Step 2: confirmation --}}
                <div class="space-y-1 rounded-xl border border-[#f5c542]/30 bg-[#f5c542]/5 p-4 text-center">
                    <p class="text-xs uppercase tracking-widest text-[#6b6b6b]">You are about to deposit</p>
                    <p class="text-3xl font-black text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                        KES {{ number_format((float) $depositAmount) }}
                    </p>
                    <p class="text-xs text-[#6b6b6b]">
                        via M-Pesa to <span class="font-semibold text-[#f5f5f0]/80">{{ $mpesa_phone ?? 'your registered number' }}</span>
                    </p>
                </div>

                <div class="flex gap-2">
                    <flux:button
                        variant="ghost"
                        class="w-full"
                        wire:click="cancelDeposit"
                        wire:loading.attr="disabled"
                        wire:target="confirmDeposit"
                    >
                        Back
                    </flux:button>

                    <flux:button
                        variant="primary"
                        class="w-full"
                        wire:click="confirmDeposit"
                        :disabled="$processingDeposit"
                        wire:loading.attr="disabled"
                        wire:target="confirmDeposit"
                    >
                        {{ $processingDeposit ? 'Sending…' : 'Confirm Deposit' }}
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:modal>

    {{-- Withdraw Modal --}}
    <flux:modal wire:model.self="showWithdrawModal" class="kadi-bottom-sheet sm:max-w-sm">
        <div class="p-6 space-y-5">
            <div>
                <h3 class="text-xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">💸 Withdraw Funds</h3>
                <p class="mt-1 text-sm text-[#6b6b6b]">
                    Available: <span class="font-semibold text-[#f5c542]">{{ $walletCurrencyLabel }} {{ number_format($balance) }}</span>
                </p>
            </div>

            @if (! $confirmingWithdraw)
                {{-- Step 1: amount entry --}}
                <flux:field>
                    <flux:label>Amount (KES)</flux:label>
                    <flux:input
                        type="number"
                        wire:model="withdrawAmount"
                        min="100"
                        step="1"
                        inputmode="numeric"
                        placeholder="Min KES 100"
                    />
                    <flux:text class="mt-1 text-xs">Minimum withdrawal: <span class="font-semibold text-[#f5c542]">KES 100</span></flux:text>
                    <flux:error name="withdrawAmount" />
                </flux:field>

                @if ($withdrawError)
                    <p class="text-sm font-medium text-red-400" role="alert">{{ $withdrawError }}</p>
                @endif

                <div class="flex gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost" class="w-full">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button
                        variant="primary"
                        class="w-full"
                        wire:click="requestWithdraw"
                        wire:loading.attr="disabled"
                        wire:target="requestWithdraw"
                    >
                        Withdraw
                    </flux:button>
                </div>
            @else
                {{-- Step 2: confirmation — only reachable with a valid amount --}}
                <div class="space-y-1 rounded-xl border border-[#f5c542]/30 bg-[#f5c542]/5 p-4 text-center">
                    <p class="text-xs uppercase tracking-widest text-[#6b6b6b]">You are about to withdraw</p>
                    <p class="text-3xl font-black text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                        KES {{ number_format((float) $withdrawAmount) }}
                    </p>
                    <p class="text-xs text-[#6b6b6b]">
                        via M-Pesa to <span class="font-semibold text-[#f5f5f0]/80">{{ $mpesa_phone ?? 'your registered number' }}</span>
                    </p>
                </div>

                <div class="flex gap-2">
                    <flux:button
                        variant="ghost"
                        class="w-full"
                        wire:click="cancelWithdraw"
                        wire:loading.attr="disabled"
                        wire:target="confirmWithdraw"
                    >
                        Back
                    </flux:button>

                    <flux:button
                        variant="primary"
                        class="w-full"
                        wire:click="confirmWithdraw"
                        :disabled="$processingWithdraw"
                        wire:loading.attr="disabled"
                        wire:target="confirmWithdraw"
                    >
                        {{ $processingWithdraw ? 'Processing…' : 'Confirm Withdrawal' }}
                    </flux:button>
                </div>
            @endif
        </div>
    </flux:modal>

    <livewire:phone-required />

</div>
