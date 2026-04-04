<x-layouts::auth :title="__('Verify Your Email')">
    <div class="flex flex-col gap-6 text-center">

        {{-- Icon --}}
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full border border-[#f5c542]/30 bg-[#f5c542]/10">
            <span class="text-3xl">✉️</span>
        </div>

        {{-- Heading --}}
        <div>
            <h2 class="text-2xl font-black text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                Verify Your Email
            </h2>
            <p class="mt-2 text-sm text-[#6b6b6b]" style="font-family: 'Outfit', sans-serif;">
                Before entering the casino, please verify your email address by clicking the link we sent you.
            </p>
        </div>

        {{-- Success flash --}}
        @if (session('status') == 'verification-link-sent')
            <div class="rounded-lg border border-green-700/40 bg-green-900/20 px-4 py-3 text-sm text-green-400" style="font-family: 'Outfit', sans-serif;">
                ✓ A new verification link has been sent to your email address.
            </div>
        @endif

        {{-- Resend button --}}
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                class="btn-casino-primary w-full rounded-xl px-6 py-3 text-sm">
                Resend Verification Email
            </button>
        </form>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="text-sm text-[#6b6b6b] transition hover:text-[#f5c542]"
                style="font-family: 'Outfit', sans-serif;">
                Log out of your account →
            </button>
        </form>

    </div>
</x-layouts::auth>
