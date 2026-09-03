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
                PRIVACY POLICY
            </h1>
            <p class="text-gray-400 text-sm md:text-base leading-relaxed max-w-xl mx-auto">
                Your data is yours. Here's how we collect, use, and protect it — no fine print, no surprises.
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

    {{-- ===================== OVERVIEW ===================== --}}
    <section id="overview" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="glass-card p-8 md:p-10 flex flex-col md:flex-row items-center gap-8 border-l-4 !border-l-[#f5c542]">
                <div class="flex-shrink-0 w-20 h-20 md:w-24 md:h-24 rounded-full flex items-center justify-center bg-gradient-to-b from-[#1a1200] to-[#0a0a0a] border-2 border-[#f5c542]/40 text-4xl md:text-5xl"
                     style="box-shadow: 0 0 40px rgba(245,197,66,0.2);">
                    🔒
                </div>
                <div>
                    <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">The Short Version</div>
                    <h2 class="font-cinzel font-bold text-xl md:text-2xl text-[#f5f5f0] mb-3">Your Privacy Matters</h2>
                    <p class="text-sm md:text-base text-[#6b6b6b] leading-relaxed">
                        Kadi Kings collects only the information needed to provide you with a fair, secure, and enjoyable gaming
                        experience. We never sell your personal data to third parties. This policy explains what we collect,
                        why we collect it, and how we protect it.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== INFORMATION WE COLLECT ===================== --}}
    <section id="collection" class="scroll-mt-24 py-16 md:py-20" style="background-color:#111111;background-image:repeating-linear-gradient(45deg,transparent,transparent 40px,rgba(245,197,66,0.03) 40px,rgba(245,197,66,0.03) 41px);">
        <div class="mx-auto max-w-6xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">What We Collect</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Information We Collect</h2>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                @foreach ([
                    ['icon' => '👤', 'title' => 'Account Info', 'desc' => 'Name, email address, phone number, and date of birth when you create an account.'],
                    ['icon' => '💳', 'title' => 'Payment Data', 'desc' => 'M-Pesa transaction references for purchases. We never store your M-Pesa PIN or full phone number.'],
                    ['icon' => '📊', 'title' => 'Game Activity', 'desc' => 'Game history, match results, win/loss records, and gameplay statistics to power leaderboards and features.'],
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

    {{-- ===================== HOW WE USE YOUR DATA ===================== --}}
    <section id="usage" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Purpose</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">How We Use Your Data</h2>
            </div>

            <div class="space-y-4">
                @foreach ([
                    ['title' => 'To Operate the Platform', 'desc' => 'Processing game sessions, managing accounts, handling deposits and withdrawals via M-Pesa, and maintaining fair gameplay.'],
                    ['title' => 'To Improve Your Experience', 'desc' => 'Personalizing game recommendations, tracking leaderboards, and developing new features based on usage patterns.'],
                    ['title' => 'To Communicate With You', 'desc' => 'Sending account notifications, game updates, promotional offers (with your consent), and responding to support requests.'],
                    ['title' => 'To Ensure Security &amp; Fair Play', 'desc' => 'Detecting fraud, preventing cheating, enforcing our Terms &amp; Conditions, and protecting the integrity of the Platform.'],
                ] as $item)
                    <div class="glass-card p-6 flex items-start gap-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center bg-gradient-to-b from-[#1a1200] to-[#0a0a0a] border border-[#f5c542]/30 text-[#f5c542] font-cinzel text-xs font-bold">→</div>
                        <div>
                            <h3 class="font-cinzel text-sm font-bold text-[#f5f5f0] uppercase tracking-wide mb-1">{!! $item['title'] !!}</h3>
                            <p class="text-sm text-[#6b6b6b] leading-relaxed">{!! $item['desc'] !!}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===================== DATA SHARING ===================== --}}
    <section id="sharing" class="scroll-mt-24 py-16 md:py-20" style="background: linear-gradient(160deg, #0a0a0a 0%, #120d00 50%, #0a0a0a 100%);">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Third Parties</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Data Sharing</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="glass-card p-8 relative overflow-hidden">
                    <div class="text-4xl mb-4">🤝</div>
                    <h3 class="font-cinzel font-bold text-lg text-[#f5c542] mb-3">We Share With</h3>
                    <ul class="text-sm text-[#f5f5f0]/70 leading-relaxed space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="text-[#f5c542] mt-0.5">♠</span>
                            <span><strong class="text-[#f5f5f0]">M-Pesa / Safaricom</strong> — to process deposits and withdrawals</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#f5c542] mt-0.5">♠</span>
                            <span><strong class="text-[#f5f5f0]">Google</strong> — for OAuth login (only with your explicit consent)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#f5c542] mt-0.5">♠</span>
                            <span><strong class="text-[#f5f5f0]">Analytics providers</strong> — anonymized usage data to improve the platform</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#f5c542] mt-0.5">♠</span>
                            <span><strong class="text-[#f5f5f0]">Law enforcement</strong> — only when legally required</span>
                        </li>
                    </ul>
                </div>

                <div class="glass-card p-8 relative overflow-hidden border-l-4 !border-l-[#ff6b6b]">
                    <div class="text-4xl mb-4">🛡️</div>
                    <h3 class="font-cinzel font-bold text-lg text-[#ff6b6b] mb-3">We Never</h3>
                    <ul class="text-sm text-[#f5f5f0]/70 leading-relaxed space-y-2">
                        <li class="flex items-start gap-2">
                            <span class="text-[#ff6b6b] mt-0.5">✗</span>
                            <span>Sell your personal data to advertisers or data brokers</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#ff6b6b] mt-0.5">✗</span>
                            <span>Share your data for others' direct marketing</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#ff6b6b] mt-0.5">✗</span>
                            <span>Use your gameplay data to profile you for third parties</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#ff6b6b] mt-0.5">✗</span>
                            <span>Store your M-Pesa PIN or full payment credentials</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== SECURITY ===================== --}}
    <section id="security" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Protection</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Data Security</h2>
            </div>

            <div class="glass-card p-8 md:p-10">
                <p class="text-sm md:text-base text-[#6b6b6b] leading-relaxed mb-6">
                    We take the security of your data seriously. Here's what we do to keep it safe:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ([
                        'SSL/TLS encryption for all data in transit',
                        'Secure password hashing (bcrypt)',
                        'WebAuthn / Passkey support for login',
                        'Regular security audits and updates',
                        'Encrypted storage for sensitive data',
                        'Access controls and audit logging',
                    ] as $item)
                        <div class="flex items-center gap-2 text-sm text-[#f5f5f0]/70">
                            <span class="text-[#f5c542]">✓</span>
                            <span>{{ $item }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-[#6b6b6b] leading-relaxed mt-6 border-t border-[#f5c542]/10 pt-4">
                    While we implement strong security measures, no method of transmission over the Internet is 100% secure.
                    We encourage you to use a strong password and enable passkeys for added protection.
                </p>
            </div>
        </div>
    </section>

    {{-- ===================== COOKIES ===================== --}}
    <section id="cookies" class="scroll-mt-24 py-16 md:py-20" style="background-color:#111111;background-image:repeating-linear-gradient(45deg,transparent,transparent 40px,rgba(245,197,66,0.03) 40px,rgba(245,197,66,0.03) 41px);">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Tracking</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Cookies &amp; Tracking</h2>
            </div>

            <div class="space-y-4">
                @foreach ([
                    ['title' => 'Essential Cookies', 'desc' => 'Required for the platform to function — session management, authentication, and security. These cannot be disabled.'],
                    ['title' => 'Analytics Cookies', 'desc' => 'Help us understand how players use Kadi Kings so we can improve the experience. All data is anonymized.'],
                    ['title' => 'Preference Cookies', 'desc' => 'Remember your settings like theme preferences and language. You can clear these at any time from your browser.'],
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

    {{-- ===================== YOUR RIGHTS ===================== --}}
    <section id="rights" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Control</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Your Rights</h2>
                <p class="mt-3 text-sm text-[#6b6b6b] max-w-2xl mx-auto">You have full control over your personal data.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ([
                    ['icon' => '👁️', 'title' => 'Access', 'desc' => 'Request a copy of all personal data we hold about you.'],
                    ['icon' => '✏️', 'title' => 'Correct', 'desc' => 'Update or fix any inaccurate information in your profile.'],
                    ['icon' => '🗑️', 'title' => 'Delete', 'desc' => 'Request deletion of your account and all associated data.'],
                    ['icon' => '📤', 'title' => 'Export', 'desc' => 'Download your game history and personal data in a portable format.'],
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

            <div class="mt-6 glass-card px-6 py-5 flex items-center gap-4">
                <div class="text-2xl flex-shrink-0">📧</div>
                <p class="text-sm text-[#6b6b6b] leading-relaxed">
                    To exercise any of these rights, contact us at
                    <span class="text-[#f5c542]">support@kadikings.co.ke</span>.
                    We will respond within 30 days of your request.
                </p>
            </div>
        </div>
    </section>

    {{-- ===================== DATA RETENTION ===================== --}}
    <section id="retention" class="scroll-mt-24 py-16 md:py-20" style="background: linear-gradient(160deg, #0a0a0a 0%, #120d00 50%, #0a0a0a 100%);">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">How Long</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Data Retention</h2>
            </div>

            <div class="glass-card p-8 md:p-10">
                <div class="space-y-4">
                    @foreach ([
                        ['item' => 'Account data', 'duration' => 'Retained while your account is active, deleted within 30 days of account closure.'],
                        ['item' => 'Game history', 'duration' => 'Kept for 12 months to maintain leaderboards and statistics.'],
                        ['item' => 'Payment records', 'duration' => 'Retained for 7 years as required by Kenyan tax and financial regulations.'],
                        ['item' => 'Support tickets', 'duration' => 'Kept for 24 months after resolution for quality assurance.'],
                        ['item' => 'Analytics data', 'duration' => 'Anonymized and retained indefinitely for platform improvement.'],
                    ] as $row)
                        <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4 pb-4 border-b border-[#f5c542]/10 last:border-0 last:pb-0">
                            <span class="font-cinzel text-sm font-bold text-[#f5c542] uppercase tracking-wide min-w-[180px]">{{ $row['item'] }}</span>
                            <span class="text-sm text-[#6b6b6b] leading-relaxed">{{ $row['duration'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CHILDREN ===================== --}}
    <section id="children" class="scroll-mt-24 py-16 md:py-20 bg-[#0a0a0a]">
        <div class="mx-auto max-w-5xl px-6">
            <div class="glass-card p-8 md:p-10 flex flex-col md:flex-row items-center gap-8 border-l-4 !border-l-[#ff6b6b]">
                <div class="flex-shrink-0 w-20 h-20 md:w-24 md:h-24 rounded-full flex items-center justify-center bg-gradient-to-b from-[#1a0000] to-[#0a0a0a] border-2 border-[#ff6b6b]/40 text-4xl md:text-5xl"
                     style="box-shadow: 0 0 40px rgba(255,107,107,0.2);">
                    🔞
                </div>
                <div>
                    <div class="font-cinzel text-[10px] text-[#ff6b6b]/60 uppercase tracking-[0.25em] mb-2">Important</div>
                    <h2 class="font-cinzel font-bold text-xl md:text-2xl text-[#f5f5f0] mb-3">Children's Privacy</h2>
                    <p class="text-sm md:text-base text-[#6b6b6b] leading-relaxed">
                        Kadi Kings is not intended for anyone under the age of 18. We do not knowingly collect data from
                        minors. If we discover that a minor has created an account, we will immediately terminate it
                        and delete all associated data.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CHANGES ===================== --}}
    <section id="changes" class="scroll-mt-24 py-16 md:py-20" style="background-color:#111111;background-image:repeating-linear-gradient(45deg,transparent,transparent 40px,rgba(245,197,66,0.03) 40px,rgba(245,197,66,0.03) 41px);">
        <div class="mx-auto max-w-5xl px-6">
            <div class="mb-12 text-center">
                <div class="font-cinzel text-[10px] text-[#f5c542]/60 uppercase tracking-[0.25em] mb-2">Updates</div>
                <h2 class="text-3xl md:text-4xl font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">Changes to This Policy</h2>
            </div>

            <div class="glass-card p-8 md:p-10">
                <p class="text-sm md:text-base text-[#6b6b6b] leading-relaxed">
                    We may update this Privacy Policy from time to time. When we make significant changes, we will
                    notify you through the Platform or via email. Your continued use of Kadi Kings after changes
                    are posted means you accept the updated policy. We recommend checking this page periodically.
                </p>
            </div>
        </div>
    </section>

    {{-- ===================== CONTACT CTA ===================== --}}
    <section class="py-16 md:py-20" style="background: linear-gradient(135deg, #1a1000, #2a1f00, #1a1000);">
        <div class="mx-auto max-w-4xl px-6 text-center">
            <div class="mb-4 inline-flex rounded-full border border-[#f5c542]/40 bg-[#f5c542]/10 px-3 py-1 text-xs tracking-widest text-[#f5c542]">
                ♠ YOUR DATA, YOUR CHOICE
            </div>
            <h2 class="mb-3 text-3xl md:text-4xl font-bold text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                Questions About Your Data?
            </h2>
            <p class="mb-8 text-[#f5f5f0]/60 max-w-lg mx-auto" style="font-family: 'Outfit', sans-serif;">
                We're transparent about how we handle your information. Reach out anytime.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4">
                <a href="mailto:support@kadikings.co.ke"
                   class="btn-casino-primary inline-block rounded-full px-8 py-4 no-underline">
                    Contact Support →
                </a>
                <a href="{{ route('legal.terms') }}" wire:navigate
                   class="btn-casino-ghost inline-block rounded-full px-8 py-4 no-underline">
                    Read the Terms
                </a>
            </div>
        </div>
    </section>
</div>
