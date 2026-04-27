<div x-data="{ tab: 'info' }" class="mx-auto max-w-5xl space-y-6">

    {{-- Flash messages --}}
    @if (session('profile_success'))
        <div class="rounded-lg border border-green-700 bg-green-900/30 p-4 text-sm text-green-400">
            {{ session('profile_success') }}
        </div>
    @endif
    @if (session('password_success'))
        <div class="rounded-lg border border-green-700 bg-green-900/30 p-4 text-sm text-green-400">
            {{ session('password_success') }}
        </div>
    @endif

    <h1 class="text-3xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">👤 My Profile</h1>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- CARD 1: Edit Profile Form (left, spans 2 cols) --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Tab switcher --}}
            <div class="flex gap-2 rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-1.5">
                <button
                    @click="tab = 'info'"
                    :class="tab === 'info' ? 'bg-[#f5c542] text-black' : 'text-[#6b6b6b] hover:text-[#f5f5f0]'"
                    class="flex-1 rounded-lg px-4 py-2.5 text-sm font-semibold transition"
                    style="font-family: 'Cinzel', serif;"
                >
                    Personal Info
                </button>
                <button
                    @click="tab = 'password'"
                    :class="tab === 'password' ? 'bg-[#f5c542] text-black' : 'text-[#6b6b6b] hover:text-[#f5f5f0]'"
                    class="flex-1 rounded-lg px-4 py-2.5 text-sm font-semibold transition"
                    style="font-family: 'Cinzel', serif;"
                >
                    Change Password
                </button>
            </div>

            {{-- Tab 1: Personal Info --}}
            <div x-show="tab === 'info'" class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-8">
                <h2 class="mb-6 text-lg font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Personal Information</h2>

                <form wire:submit="updateProfile" class="space-y-5">

                    <div>
                        <flux:input wire:model="name" :label="__('Full Name')" type="text" required />
                        @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <flux:input wire:model="email" :label="__('Email Address')" type="email" required />
                        @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-[#6b6b6b] mb-1.5" style="font-family: 'Outfit', sans-serif;">
                            Phone Number
                        </label>
                        <div class="rounded-lg border border-yellow-800/20 bg-[#111111] px-4 py-3 font-mono text-sm text-[#f5c542]">
                            {{ $phoneNo ?: '—' }}
                        </div>
                    </div>

                    {{-- Account No: read-only --}}
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-[#6b6b6b] mb-1.5" style="font-family: 'Outfit', sans-serif;">
                            Account No
                        </label>
                        <div class="rounded-lg border border-yellow-800/20 bg-[#111111] px-4 py-3 font-mono text-sm text-[#f5c542]">
                            {{ $kadiCustomer['account_no'] ?? auth()->user()->account_no ?? '—' }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn-casino-primary inline-flex items-center gap-2 rounded-xl px-8 py-3 text-sm"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    >
                        <span wire:loading wire:target="updateProfile" class="animate-spin">⟳</span>
                        <span wire:loading.remove wire:target="updateProfile">Save Changes</span>
                        <span wire:loading wire:target="updateProfile">Saving...</span>
                    </button>

                </form>
            </div>

            {{-- Tab 2: Change Password --}}
            <div x-show="tab === 'password'" x-cloak class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-8">
                <h2 class="mb-6 text-lg font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Change Password</h2>

                <form wire:submit="updatePassword" class="space-y-5">
                    <div>
                        <flux:input wire:model="currentPassword" :label="__('Current Password')" type="password" viewable />
                        @error('currentPassword') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <flux:input wire:model="newPassword" :label="__('New Password')" type="password" viewable />
                        @error('newPassword') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <flux:input wire:model="newPasswordConfirmation" :label="__('Confirm New Password')" type="password" viewable />
                    </div>
                    <flux:button
                        type="submit"
                        variant="primary"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    >
                        <span wire:loading wire:target="updatePassword" class="animate-spin mr-2">⟳</span>
                        Update Password
                    </flux:button>
                </form>
            </div>
        </div>

        {{-- Right column: Card 2 + Card 3 --}}
        <div class="space-y-6">

            {{-- CARD 2: Account Info --}}
            <div class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-6 text-center">
                {{-- Avatar --}}
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-[#f5c542] text-2xl font-bold text-black" style="font-family: 'Cinzel', serif;">
                    {{ auth()->user()->initials() }}
                </div>

                <h3 class="mb-0.5 font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">{{ auth()->user()->name }}</h3>
                <p class="mb-1 text-sm text-[#6b6b6b]">{{ auth()->user()->email }}</p>
                <p class="mb-4 font-mono text-xs text-[#f5c542]/70">{{ auth()->user()->account_no ?? '—' }}</p>

                <div class="mb-4 border-t border-yellow-800/20 pt-4 text-left space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-[#6b6b6b]">Member since</span>
                        <span class="text-[#f5f5f0]">{{ auth()->user()->created_at->format('M Y') }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center gap-2">
                    <span class="rounded-full bg-green-900/50 px-3 py-1 text-xs font-semibold text-green-400 border border-green-700">
                        ✓ Active
                    </span>
                    @if (auth()->user()->isLinked())
                        <span class="rounded-full bg-[#f5c542]/10 px-3 py-1 text-xs font-semibold text-[#f5c542] border border-[#f5c542]/30">
                            🔗 Kadi Linked
                        </span>
                    @else
                        <span class="rounded-full bg-red-900/20 px-3 py-1 text-xs font-semibold text-red-400 border border-red-700/40">
                            ⚠ Not Linked
                        </span>
                    @endif
                </div>
            </div>

            {{-- CARD 3: Stats --}}
            <div class="rounded-xl border border-yellow-800/30 bg-[#1a1a1a] p-6">
                <h3 class="mb-4 text-sm font-bold tracking-widest text-[#f5c542] uppercase" style="font-family: 'Cinzel', serif;">
                    Your Stats
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    @php
                        $stats = [
                            ['label' => 'Total Deposits',     'value' => session('currency.code', 'KES').' '.number_format($kadiCustomer['deposits'] ?? 0, 2)],
                            ['label' => 'Total Withdrawals',  'value' => session('currency.code', 'KES').' '.number_format($kadiCustomer['withdraws'] ?? 0, 2)],
                            ['label' => 'Singles Played',     'value' => $kadiCustomer['single_played'] ?? 0],
                            ['label' => 'Competitions',       'value' => $kadiCustomer['competition_played'] ?? 0],
                        ];
                    @endphp
                    @foreach ($stats as $stat)
                        <div class="rounded-lg border-t-2 border-[#f5c542]/40 bg-[#111111] p-3 text-center">
                            <div class="text-base font-black text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">
                                {{ $stat['value'] }}
                            </div>
                            <div class="mt-1 text-xs text-[#6b6b6b]" style="font-family: 'Outfit', sans-serif;">
                                {{ $stat['label'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>
