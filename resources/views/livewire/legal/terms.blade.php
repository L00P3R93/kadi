<div>
    {{-- ===================== HERO ===================== --}}
    <section class="relative overflow-hidden bg-[#0a0a0a] min-h-[320px] md:min-h-[380px] flex items-center">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full pointer-events-none"
             style="background: radial-gradient(circle, rgba(245,197,66,0.08) 0%, transparent 70%);"></div>

        <div class="relative z-10 w-full max-w-5xl mx-auto px-6 py-14 md:py-20 text-center">
            <div class="inline-flex items-center gap-2 bg-[#f5c542]/10 border border-[#f5c542]/20 rounded-full px-4 py-1.5 mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-[#f5c542] animate-pulse"></span>
                <span class="font-cinzel text-[10px] text-[#f5c542] uppercase tracking-[0.2em] font-semibold">Legal</span>
            </div>

            <h1 class="font-cinzel font-black text-3xl md:text-5xl text-[#f5c542] leading-tight tracking-wide mb-3"
                style="text-shadow: 0 0 30px rgba(245,197,66,0.35);">
                TERMS &amp; CONDITIONS
            </h1>
            <p class="text-gray-400 text-sm md:text-base leading-relaxed max-w-xl mx-auto">
                The rules of the house. By using Kadi, you agree to play by these terms — no exceptions.
            </p>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                <span class="rounded-full border border-[#f5c542]/20 bg-white/[0.03] px-4 py-1.5 text-xs text-[#f5f5f0]/70">
                    Last updated: {{ date('F Y') }}
                </span>
                <span class="rounded-full border border-[#f5c542]/20 bg-white/[0.03] px-4 py-1.5 text-xs text-[#f5f5f0]/70">
                    ♠ {{ strtoupper(config('app.name')) }}
                </span>
            </div>
        </div>
    </section>

    {{-- ===================== ACCEPTANCE ===================== --}}
    <section id="acceptance" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="glass-card p-8 md:p-10 flex flex-col md:flex-row items-center gap-8 border-l-4 !border-l-[#f5c542]">
                <div class="flex-shrink-0 w-20 h-20 md:w-24 md:h-24 rounded-full flex items-center justify-center bg-gradient-to-b from-[#1a1200] to-[#0a0a0a] border-2 border-[#f5c542]/40 text-4xl md:text-5xl"
                     style="box-shadow: 0 0 40px rgba(245,197,66,0.2);">
                    📜
                </div>
                <div>
                    <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Before You Play</div>
                    <h2 class="font-cinzel font-bold text-xl md:text-2xl text-[#f5f5f0] mb-3">Acceptance of Terms</h2>
                    <p class="text-sm md:text-base text-[#6b6b6b] leading-relaxed">
                        By accessing or using Kadi (the "Platform"), you agree to be bound by these Terms &amp; Conditions.
                        If you do not agree, do not use the Platform. We may update these terms at any time — continued use
                        means you accept the changes.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== ELIGIBILITY ===================== --}}
    <section id="eligibility" class="scroll-mt-24 py-16 md:py-20" style="background-color:#111111;background-image:repeating-linear-gradient(45deg,transparent,transparent 40px,rgba(245,197,66,0.03) 40px,rgba(245,197,66,0.03) 41px);">
        <div class="mx-auto max-w-6xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Who Can Play</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Eligibility</h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ([
                    ['icon' => '🔞', 'title' => '18+ Only', 'desc' => 'You must be at least 18 years old to create an account or play on Kadi. No exceptions.'],
                    ['icon' => '🇰🇪', 'title' => 'Legal Jurisdiction', 'desc' => 'Kadi operates under Kenyan law (License BK-0001273). You must comply with the laws of your own jurisdiction.'],
                    ['icon' => '✋', 'title' => 'One Account Per Person', 'desc' => 'Each person may hold only one account. Duplicate accounts will be suspended without notice.'],
                ] as $item)
                    <div class="glass-card glass-card-hover p-6 relative isolate overflow-hidden">
                        <div class="relative z-10">
                            <div class="text-3xl mb-4">{{ $item['icon'] }}</div>
                            <h3 class="font-cinzel text-sm font-bold text-[#f5c542] uppercase tracking-wide mb-2">{{ $item['title'] }}</h3>
                            <p class="text-xs text-[#6b6b6b] leading-relaxed">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== ACCOUNT ===================== --}}
    <section id="account" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Your Identity</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Account &amp; Registration</h2>
            </div>

            <div class="space-y-4">
                @foreach ([
                    ['title' => 'Accurate Information', 'desc' => 'You must provide truthful and accurate information during registration. Fake or misleading details are grounds for immediate account termination.'],
                    ['title' => 'Account Security', 'desc' => 'You are responsible for keeping your password and login credentials secure. Kadi is not liable for unauthorized access to your account.'],
                    ['title' => 'Account Ownership', 'desc' => 'Your account is personal to you. You may not transfer, sell, or share your account with anyone else.'],
                    ['title' => 'Verification', 'desc' => 'We may require identity verification at any time. Failure to verify may result in account restrictions or suspension.'],
                ] as $item)
                    <div class="glass-card p-6 flex items-start gap-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-gradient-to-b from-[#1a1200] to-[#0a0a0a] border border-[#f5c542]/30 text-[#f5c542] font-cinzel text-xs font-bold">→</div>
                        <div>
                            <h3 class="font-cinzel text-sm font-bold text-[#f5f5f0] uppercase tracking-wide mb-1">{{ $item['title'] }}</h3>
                            <p class="text-sm text-[#6b6b6b] leading-relaxed">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== GAMEPLAY & FAIR PLAY ===================== --}}
    <section id="gameplay" class="scroll-mt-24 py-16 md:py-20" style="background: linear-gradient(160deg, #0a0a0a 0%, #120d00 50%, #0a0a0a 100%);">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">The Arena</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Gameplay &amp; Fair Play</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="glass-card p-8 relative overflow-hidden">
                    <div class="text-4xl mb-4">⚖️</div>
                    <h3 class="font-cinzel font-bold text-lg text-[#f5c542] mb-3">Fair Play Policy</h3>
                    <p class="text-sm text-[#f5f5f0]/70 leading-relaxed">
                        Every game on Kadi is fair and random. The server manages all shuffling, dealing, and card resolution.
                        Any attempt to manipulate game outcomes, exploit bugs, or use automated play tools will result in permanent ban.
                    </p>
                </div>

                <div class="glass-card p-8 relative overflow-hidden">
                    <div class="text-4xl mb-4">🚫</div>
                    <h3 class="font-cinzel font-bold text-lg text-[#ff6b6b] mb-3">Prohibited Conduct</h3>
                    <ul class="text-sm text-[#f5f5f0]/70 leading-relaxed space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="text-[#ff6b6b] mt-0.5">✗</span>
                            <span>Using bots, scripts, or any automated tools to play</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#ff6b6b] mt-0.5">✗</span>
                            <span>Exploiting bugs or vulnerabilities for unfair advantage</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#ff6b6b] mt-0.5">✗</span>
                            <span>Colluding with other players in any form</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#ff6b6b] mt-0.5">✗</span>
                            <span>Harassing, abusing, or threatening other players</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#ff6b6b] mt-0.5">✗</span>
                            <span>Attempting to access another player's account</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== VIRTUAL CURRENCY ===================== --}}
    <section id="currency" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Coins &amp; Purchases</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Virtual Currency</h2>
            </div>

            <div class="space-y-4">
                @foreach ([
                    ['title' => 'Coins Have No Cash Value', 'desc' => 'Virtual coins purchased or earned on Kadi hold no real-world monetary value and cannot be exchanged for cash, goods, or services outside the Platform.'],
                    ['title' => 'No Refunds on Virtual Purchases', 'desc' => 'All purchases of virtual coins are final. We do not offer refunds, exchanges, or reversals once a transaction is confirmed via M-Pesa.'],
                    ['title' => 'Platform Use Only', 'desc' => 'Coins may only be used within the Kadi platform for gameplay purposes. Selling or trading coins outside the platform is strictly prohibited.'],
                ] as $item)
                    <div class="glass-card p-6 flex items-start gap-4 border-l-4 !border-l-[#f5c542]">
                        <div>
                            <h3 class="font-cinzel text-sm font-bold text-[#f5c542] uppercase tracking-wide mb-1">{{ $item['title'] }}</h3>
                            <p class="text-sm text-[#6b6b6b] leading-relaxed">{{ $item['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== INTELLECTUAL PROPERTY ===================== --}}
    <section id="ip" class="scroll-mt-24 py-16 md:py-20" style="background-color:#111111;background-image:repeating-linear-gradient(45deg,transparent,transparent 40px,rgba(245,197,66,0.03) 40px,rgba(245,197,66,0.03) 41px);">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Ownership</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Intellectual Property</h2>
            </div>

            <div class="glass-card p-8 md:p-10">
                <p class="text-sm md:text-base text-[#6b6b6b] leading-relaxed mb-6">
                    All content, branding, game designs, graphics, logos, and software on Kadi are the property of
                    Kadi and protected by Kenyan and international intellectual property laws.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ([
                        'The Kadi name, logo, and branding',
                        'Game software, algorithms, and server logic',
                        'Visual design, graphics, and animations',
                        'User interface and experience design',
                        'Rules, gameplay mechanics, and card designs',
                        'Marketing materials and promotional content',
                    ] as $item)
                        <div class="flex items-center gap-2 text-sm text-[#f5f5f0]/70">
                            <span class="text-[#f5c542]">♠</span>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== LIABILITY ===================== --}}
    <section id="liability" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Limits</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Limitation of Liability</h2>
            </div>

            <div class="glass-card p-8 md:p-10 border-l-4 !border-l-[#ff6b6b]">
                <p class="text-sm md:text-base text-[#6b6b6b] leading-relaxed mb-4">
                    Kadi is provided "as is" without warranties of any kind. We do not guarantee uninterrupted or
                    error-free service. To the fullest extent permitted by law:
                </p>
                <ul class="text-sm text-[#f5f5f0]/70 leading-relaxed space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="text-[#ff6b6b] mt-0.5">•</span>
                        <span>We are not liable for any loss of virtual coins, data, or game progress due to technical issues.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-[#ff6b6b] mt-0.5">•</span>
                        <span>We are not responsible for the actions or conduct of other players on the Platform.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-[#ff6b6b] mt-0.5">•</span>
                        <span>Our total liability shall not exceed the amount you paid to Kadi in the past 12 months.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- ===================== TERMINATION ===================== --}}
    <section id="termination" class="scroll-mt-24 py-16 md:py-20" style="background: linear-gradient(160deg, #0a0a0a 0%, #120d00 50%, #0a0a0a 100%);">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">End of the Game</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Termination</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="glass-card p-8 relative overflow-hidden">
                    <div class="text-4xl mb-4">👋</div>
                    <h3 class="font-cinzel font-bold text-lg text-[#f5c542] mb-3">Your Right to Leave</h3>
                    <p class="text-sm text-[#f5f5f0]/70 leading-relaxed">
                        You may close your account at any time by contacting us at
                        <span class="text-[#f5c542]">support@kadikings.co.ke</span>.
                        Once closed, any remaining virtual coins will be forfeited and cannot be recovered.
                    </p>
                </div>

                <div class="glass-card p-8 relative overflow-hidden border-l-4 !border-l-[#ff6b6b]">
                    <div class="text-4xl mb-4">🔨</div>
                    <h3 class="font-cinzel font-bold text-lg text-[#ff6b6b] mb-3">Our Right to Suspend</h3>
                    <p class="text-sm text-[#f5f5f0]/70 leading-relaxed">
                        We reserve the right to suspend or permanently ban any account that violates these terms,
                        engages in fraudulent activity, or disrupts the Platform. Suspended accounts may lose
                        access to virtual currency without refund.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== GOVERNING LAW ===================== --}}
    <section id="law" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="glass-card p-8 md:p-10 flex flex-col md:flex-row items-center gap-8 border-l-4 !border-l-[#f5c542]">
                <div class="flex-shrink-0 w-20 h-20 md:w-24 md:h-24 rounded-full flex items-center justify-center bg-gradient-to-b from-[#1a1200] to-[#0a0a0a] border-2 border-[#f5c542]/40 text-4xl md:text-5xl"
                     style="box-shadow: 0 0 40px rgba(245,197,66,0.2);">
                    ⚖️
                </div>
                <div>
                    <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Jurisdiction</div>
                    <h2 class="font-cinzel font-bold text-xl md:text-2xl text-[#f5f5f0] mb-3">Governing Law</h2>
                    <p class="text-sm md:text-base text-[#6b6b6b] leading-relaxed">
                        These Terms are governed by and construed in accordance with the laws of the Republic of Kenya.
                        Any disputes arising from these terms shall be subject to the exclusive jurisdiction of Kenyan courts.
                        License No. <span class="text-[#f5c542]">BK-0001273</span>.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CONTACT CTA ===================== --}}
    <section class="py-16 md:py-20" style="background: linear-gradient(135deg, #1a1000, #2a1f00, #1a1000);">
        <div class="mx-auto max-w-4xl px-6 text-center">
            <div class="mb-4 inline-flex rounded-full border border-[#f5c542]/40 bg-[#f5c542]/10 px-3 py-1 text-xs tracking-widest text-[#f5c542]">
                ♠ QUESTIONS?
            </div>
            <h2 class="mb-3 text-3xl md:text-4xl font-bold text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                Need Clarification?
            </h2>
            <p class="mb-8 text-[#f5f5f0]/60 max-w-lg mx-auto" style="font-family: 'Outfit', sans-serif;">
                If anything in these terms is unclear, reach out. We're here to help.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="mailto:support@kadikings.co.ke"
                   class="btn-casino-primary inline-block rounded-full px-8 py-4 no-underline">
                    Contact Support →
                </a>
                <a href="{{ route('rules') }}" wire:navigate
                   class="btn-casino-ghost inline-block rounded-full px-8 py-4 no-underline">
                    Read the Rules
                </a>
            </div>
        </div>
    </section>
</div>
