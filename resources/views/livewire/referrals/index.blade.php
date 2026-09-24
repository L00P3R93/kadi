@php
    $kes = fn ($amount) => 'KES '.number_format((float) $amount, 2);
    $when = fn (?string $at) => $at ? \Illuminate\Support\Carbon::parse($at)->timezone(config('app.timezone'))->format('j M Y') : '';
    $shareText = $share
        ? __('Join me on Kadi and play cards online. Sign up with my code :code: :link (18+ only)', ['code' => $share['code'], 'link' => $share['link']])
        : '';
    $badge = [
        'pending_verification' => 'border-amber-600/50 bg-amber-900/20 text-amber-400',
        'verified' => 'border-sky-600/50 bg-sky-900/20 text-sky-400',
        'deposited' => 'border-green-700/60 bg-green-900/20 text-green-400',
        'pending' => 'border-amber-600/50 bg-amber-900/20 text-amber-400',
        'processing' => 'border-sky-600/50 bg-sky-900/20 text-sky-400',
        'completed' => 'border-green-700/60 bg-green-900/20 text-green-400',
        'failed' => 'border-red-700/60 bg-red-900/20 text-red-400',
    ];
@endphp

<div class="space-y-6" wire:init="load">

    @if ($successMessage)
        <div class="rounded-lg border border-green-700 bg-green-900/30 p-4 text-sm text-green-400" role="status" data-test="referral-success">
            {{ $successMessage }}
        </div>
    @endif

    @if ($noticeMessage)
        <div class="rounded-lg border border-amber-600/60 bg-amber-900/30 p-4 text-sm text-amber-400" role="status" data-test="referral-notice">
            {{ $noticeMessage }}
        </div>
    @endif

    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="flex items-center gap-2 text-3xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">
                <flux:icon.user-plus variant="outline" class="size-7 shrink-0 text-[#f5c542]" aria-hidden="true" />
                Invite &amp; Earn
            </h1>
            <p class="mt-1 text-sm text-[#6b6b6b]">
                Earn KES 10 when a friend you invite confirms their email and phone, and another KES 10 on their first deposit.
                Withdraw to M-Pesa from KES {{ $this->minimumWithdrawal() }}.
            </p>
        </div>

        <button type="button" wire:click="refresh" wire:loading.attr="disabled" wire:target="refresh, load"
                class="btn-casino-ghost shrink-0 rounded-full px-4 py-1.5 text-sm">
            <span wire:loading.remove wire:target="refresh">Refresh</span>
            <span wire:loading wire:target="refresh">Refreshing…</span>
        </button>
    </div>

    @if (! $loaded)
        <div class="flex items-center justify-center gap-2 rounded-xl border border-yellow-800/30 bg-[#1a1a1a] py-16 text-sm text-[#6b6b6b]">
            <flux:icon.loading class="size-4" aria-hidden="true" /> Loading your referrals...
        </div>
    @elseif (! auth()->user()->linked_id)
        <div class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-6 text-sm text-[#6b6b6b]">
            Your account is still being set up. Please check back in a few minutes.
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-3">

            {{-- ═══ Code, link, share, QR ═══ --}}
            <section class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-4 sm:p-6 lg:col-span-2" aria-labelledby="referral-share-title">
                <h2 id="referral-share-title" class="mb-4 text-lg font-semibold text-[#f5f5f0]">Your invite</h2>

                @if ($share)
                    <div class="flex flex-col gap-6 sm:flex-row"
                         x-data="{
                            copied: null,
                            copy(text, what) {
                                navigator.clipboard?.writeText(text).then(() => { this.copied = what; setTimeout(() => this.copied = null, 2000) });
                            },
                            async shareLink() {
                                if (navigator.share) {
                                    try { await navigator.share({ title: 'Kadi', text: @js($shareText) }); } catch (e) {}
                                } else {
                                    this.copy(@js($shareText), 'text');
                                }
                            },
                            downloadQr() {
                                const img = new Image();
                                img.onload = () => {
                                    const canvas = document.createElement('canvas');
                                    canvas.width = canvas.height = 640;
                                    const ctx = canvas.getContext('2d');
                                    ctx.fillStyle = '#ffffff';
                                    ctx.fillRect(0, 0, 640, 640);
                                    ctx.drawImage(img, 0, 0, 640, 640);
                                    const a = document.createElement('a');
                                    a.href = canvas.toDataURL('image/png');
                                    a.download = 'kadi-invite-{{ $share['code'] }}.png';
                                    a.click();
                                };
                                img.src = @js($share['qr_code']);
                            }
                         }">
                        <div class="flex-1 space-y-4">
                            <div>
                                <p class="text-xs uppercase tracking-widest text-[#6b6b6b]">Code</p>
                                <div class="mt-1 flex items-center gap-3">
                                    <span class="font-mono text-2xl font-bold tracking-widest text-[#f5c542]" data-test="referral-code">{{ $share['code'] }}</span>
                                    <button type="button" x-on:click="copy(@js($share['code']), 'code')" class="text-xs font-semibold text-[#6b6b6b] hover:text-[#f5f5f0]">
                                        <span x-show="copied !== 'code'">Copy</span><span x-show="copied === 'code'" x-cloak>Copied</span>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label for="referral-link" class="text-xs uppercase tracking-widest text-[#6b6b6b]">Link</label>
                                <div class="mt-1 flex gap-2">
                                    <input id="referral-link" type="text" readonly value="{{ $share['link'] }}" x-on:focus="$el.select()"
                                           class="min-w-0 flex-1 rounded-lg border border-[#2a2a2a] bg-[#111] px-3 py-2 text-sm text-[#f5f5f0]/80 focus:outline-none" />
                                    <button type="button" x-on:click="copy(@js($share['link']), 'link')" class="btn-casino-primary shrink-0 rounded-lg px-4 py-2 text-sm font-semibold">
                                        <span x-show="copied !== 'link'">Copy link</span><span x-show="copied === 'link'" x-cloak>Copied</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button type="button" x-on:click="shareLink()" class="btn-casino-ghost inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm">
                                    <flux:icon.share variant="micro" aria-hidden="true" /> Share
                                </button>
                                <a href="https://wa.me/?text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener"
                                   class="btn-casino-ghost inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm">
                                    <flux:icon.chat-bubble-left-right variant="micro" aria-hidden="true" /> WhatsApp
                                </a>
                                <a href="sms:?&body={{ rawurlencode($shareText) }}"
                                   class="btn-casino-ghost inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm">
                                    <flux:icon.chat-bubble-bottom-center-text variant="micro" aria-hidden="true" /> SMS
                                </a>
                            </div>
                            <p class="text-xs text-[#6b6b6b]" x-show="copied === 'text'" x-cloak>Invite text copied. Paste it anywhere.</p>
                        </div>

                        <div class="flex shrink-0 flex-col items-center gap-2">
                            <img src="{{ $share['qr_code'] }}" alt="QR code for your invite link" width="160" height="160"
                                 class="h-40 w-40 rounded-lg bg-white p-2" data-test="referral-qr" />
                            <button type="button" x-on:click="downloadQr()" class="inline-flex items-center gap-1 text-xs font-semibold text-[#f5c542] hover:underline">
                                <flux:icon.arrow-down-tray variant="micro" aria-hidden="true" /> Download QR
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-red-400">Your invite code is not available right now. Please try again shortly.</p>
                @endif
            </section>

            {{-- ═══ Referral wallet + withdraw ═══ --}}
            <section class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-4 sm:p-6" aria-labelledby="referral-wallet-title">
                <h2 id="referral-wallet-title" class="mb-4 text-lg font-semibold text-[#f5f5f0]">Referral wallet</h2>

                @if ($wallet)
                    <p class="text-3xl font-bold text-[#f5c542]" data-test="referral-balance">{{ $kes($wallet['balance'] ?? 0) }}</p>
                    <p class="mt-1 text-xs text-[#6b6b6b]">Total earned {{ $kes($wallet['total_earned'] ?? 0) }}</p>

                    @if ($confirmingWithdraw)
                        <div class="mt-5 rounded-lg border border-yellow-800/30 bg-[#111] p-4">
                            <p class="text-sm text-[#f5f5f0]/80">
                                Withdraw <strong class="text-[#f5c542]">KES {{ number_format((int) $withdrawAmount) }}</strong> to your M-Pesa number
                                <strong class="text-[#f5f5f0]">{{ \App\Services\TextSmsService::mask(auth()->user()->phone) }}</strong>?
                            </p>
                            <div class="mt-4 flex gap-2">
                                <button type="button" wire:click="confirmWithdraw" wire:loading.attr="disabled" wire:target="confirmWithdraw"
                                        class="btn-casino-primary flex-1 rounded-lg py-2 text-sm font-semibold disabled:opacity-50" data-test="referral-confirm-withdraw">
                                    <span wire:loading.remove wire:target="confirmWithdraw">Confirm</span>
                                    <span wire:loading wire:target="confirmWithdraw">Sending…</span>
                                </button>
                                <button type="button" wire:click="cancelWithdraw" wire:loading.attr="disabled" wire:target="confirmWithdraw"
                                        class="btn-casino-ghost flex-1 rounded-lg py-2 text-sm">Cancel</button>
                            </div>
                        </div>
                    @else
                        <form wire:submit="requestWithdraw" class="mt-5 space-y-3">
                            <label for="referral-withdraw-amount" class="block text-xs uppercase tracking-widest text-[#6b6b6b]">Withdraw to M-Pesa</label>
                            <div class="flex gap-2">
                                <input id="referral-withdraw-amount" type="number" inputmode="numeric" min="{{ $this->minimumWithdrawal() }}" step="1"
                                       wire:model="withdrawAmount" placeholder="{{ $this->minimumWithdrawal() }}"
                                       class="min-w-0 flex-1 rounded-lg border border-[#2a2a2a] bg-[#111] px-3 py-2 text-sm text-[#f5f5f0] focus:border-[#f5c542]/60 focus:outline-none" />
                                <button type="submit" wire:loading.attr="disabled" wire:target="requestWithdraw"
                                        @disabled(! ($wallet['withdrawable'] ?? false) || $this->withdrawableBalance() < $this->minimumWithdrawal())
                                        class="btn-casino-primary shrink-0 rounded-lg px-4 py-2 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-50">
                                    Withdraw
                                </button>
                            </div>
                            <p class="text-xs text-[#6b6b6b]">Minimum KES {{ $this->minimumWithdrawal() }}, whole shillings.</p>
                        </form>
                    @endif

                    @if ($withdrawError)
                        <p class="mt-3 text-sm text-red-400" role="alert" data-test="referral-withdraw-error">{{ $withdrawError }}</p>
                    @endif
                @else
                    <p class="text-sm text-red-400">We could not load your referral wallet right now.</p>
                @endif
            </section>
        </div>

        {{-- ═══ Stats ═══ --}}
        <section class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-4 sm:p-6" aria-labelledby="referral-stats-title">
            <h2 id="referral-stats-title" class="mb-4 text-lg font-semibold text-[#f5f5f0]">Your referrals</h2>

            @if ($stats)
                @php
                    $tiles = [
                        ['Invited', $stats['referrals']['total'] ?? 0],
                        ['Verified', $stats['referrals']['verified'] ?? 0],
                        ['Deposited', $stats['referrals']['deposited'] ?? 0],
                        ['Pending', $stats['referrals']['pending_verification'] ?? 0],
                        ['Earned', $kes($stats['earned']['total'] ?? 0)],
                        ['This month', $kes($stats['earned']['this_month'] ?? 0)],
                    ];
                @endphp
                <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6" data-test="referral-stats">
                    @foreach ($tiles as [$label, $value])
                        <div class="rounded-lg border border-yellow-800/20 bg-[#111]/60 p-3" wire:key="stat-{{ $loop->index }}">
                            <dt class="text-xs text-[#6b6b6b]">{{ $label }}</dt>
                            <dd class="mt-1 text-lg font-bold text-[#f5f5f0]">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <p class="mt-3 text-xs text-[#6b6b6b]">
                    Today {{ $stats['referrals']['today'] ?? 0 }} · this week {{ $stats['referrals']['this_week'] ?? 0 }} · this month {{ $stats['referrals']['this_month'] ?? 0 }}.
                    Sign-up bonuses {{ $kes($stats['earned']['signup'] ?? 0) }}, first-deposit bonuses {{ $kes($stats['earned']['first_deposit'] ?? 0) }}.
                </p>
            @elseif ($statsFailed)
                <p class="text-sm text-red-400">We could not load your stats right now.</p>
            @endif

            {{-- Filter --}}
            <div class="mb-4 mt-6 flex flex-wrap gap-2" role="tablist">
                @foreach (['' => 'All'] + $statuses as $key => $label)
                    <button type="button" role="tab" aria-selected="{{ $status === $key ? 'true' : 'false' }}"
                            wire:click="setStatus('{{ $key }}')" wire:key="status-{{ $key ?: 'all' }}"
                            @class([
                                'rounded-full px-4 py-1.5 text-sm font-semibold transition',
                                'btn-casino-primary' => $status === $key,
                                'border border-yellow-800/40 text-[#6b6b6b] hover:text-[#f5f5f0]' => $status !== $key,
                            ])>
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @if ($referralsFailed)
                <p class="text-sm text-red-400">We could not load your referrals right now.</p>
            @elseif ($referrals === [])
                <p class="py-6 text-center text-sm text-[#6b6b6b]">No referrals yet. Share your link to get started.</p>
            @else
                <ul class="divide-y divide-yellow-800/20" data-test="referral-list">
                    @foreach ($referrals as $referral)
                        <li class="flex items-center justify-between gap-3 py-3" wire:key="referral-{{ $referral['id'] ?? $loop->index }}">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-[#f5f5f0]">{{ $referral['referred_name'] ?? 'Player' }}</p>
                                <p class="text-xs text-[#6b6b6b]">{{ $referral['referred_phone'] ?? '' }} · joined {{ $when($referral['created_at'] ?? null) }}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <span class="rounded-full border px-2 py-0.5 text-xs {{ $badge[$referral['status'] ?? ''] ?? 'border-zinc-700 text-zinc-400' }}">
                                    {{ $statuses[$referral['status'] ?? ''] ?? ucfirst((string) ($referral['status'] ?? '')) }}
                                </span>
                                <span class="w-24 text-right text-sm font-semibold text-[#f5c542]">{{ $kes($referral['earned'] ?? 0) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if (($referralsMeta['last_page'] ?? 1) > 1)
                    <div class="mt-4 flex items-center justify-between text-sm text-[#6b6b6b]">
                        <button type="button" wire:click="goToPage({{ $page - 1 }})" @disabled($page <= 1) class="hover:text-[#f5f5f0] disabled:opacity-40">Previous</button>
                        <span>Page {{ $page }} of {{ $referralsMeta['last_page'] }}</span>
                        <button type="button" wire:click="goToPage({{ $page + 1 }})" @disabled($page >= ($referralsMeta['last_page'] ?? 1)) class="hover:text-[#f5f5f0] disabled:opacity-40">Next</button>
                    </div>
                @endif
            @endif
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- ═══ Bonus history ═══ --}}
            <section class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-4 sm:p-6" aria-labelledby="referral-bonuses-title">
                <h2 id="referral-bonuses-title" class="mb-4 text-lg font-semibold text-[#f5f5f0]">Bonuses</h2>
                @php $bonuses = array_values(array_filter((array) ($wallet['bonuses'] ?? []), 'is_array')); @endphp
                @if ($bonuses === [])
                    <p class="text-sm text-[#6b6b6b]">No bonuses yet.</p>
                @else
                    <ul class="divide-y divide-yellow-800/20">
                        @foreach ($bonuses as $bonus)
                            <li class="flex items-center justify-between py-2 text-sm" wire:key="bonus-{{ $bonus['id'] ?? $loop->index }}">
                                <span class="text-[#f5f5f0]/80">
                                    {{ ($bonus['milestone'] ?? '') === 'first_deposit' ? 'First deposit' : 'Sign-up' }} · {{ $bonus['referred_name'] ?? 'Player' }}
                                    <span class="text-xs text-[#6b6b6b]">{{ $when($bonus['created_at'] ?? null) }}</span>
                                </span>
                                <span class="font-semibold text-green-400">+{{ $kes($bonus['amount'] ?? 0) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- ═══ Withdrawals ═══ --}}
            <section class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-4 sm:p-6" aria-labelledby="referral-withdrawals-title">
                <h2 id="referral-withdrawals-title" class="mb-4 text-lg font-semibold text-[#f5f5f0]">Withdrawals</h2>
                @if ($withdrawalsFailed)
                    <p class="text-sm text-red-400">We could not load your withdrawals right now.</p>
                @elseif ($withdrawals === [])
                    <p class="text-sm text-[#6b6b6b]">No withdrawals yet.</p>
                @else
                    <ul class="divide-y divide-yellow-800/20" data-test="referral-withdrawals">
                        @foreach ($withdrawals as $withdrawal)
                            <li class="flex items-center justify-between gap-3 py-2 text-sm" wire:key="withdrawal-{{ $withdrawal['id'] ?? $loop->index }}">
                                <span class="text-[#f5f5f0]/80">
                                    {{ $kes($withdrawal['amount'] ?? 0) }}
                                    <span class="text-xs text-[#6b6b6b]">{{ $when($withdrawal['created_at'] ?? null) }}@if (! empty($withdrawal['mpesa_receipt'])) · {{ $withdrawal['mpesa_receipt'] }}@endif</span>
                                </span>
                                <span class="rounded-full border px-2 py-0.5 text-xs {{ $badge[$withdrawal['status'] ?? ''] ?? 'border-zinc-700 text-zinc-400' }}">
                                    {{ $withdrawalStatuses[$withdrawal['status'] ?? ''] ?? ucfirst((string) ($withdrawal['status'] ?? '')) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <p class="text-center text-xs text-[#6b6b6b]">18+ only. Play responsibly.</p>
    @endif
</div>
