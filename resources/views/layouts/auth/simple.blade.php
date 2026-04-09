<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @php
            $noindex     = true;
            $description = 'Sign in or create your Kadi Kings account to play casino games and claim your welcome bonus.';
            $page        = 'auth';
        @endphp
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#0a0a0a] antialiased">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10"
             style="background: radial-gradient(ellipse at center, #1a1200 0%, #0a0a0a 70%);">
            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-2" wire:navigate>
                    <span class="text-2xl tracking-widest text-[#f5c542]" style="font-family: 'Cinzel', serif;">♠ KADI KINGS</span>
                </a>

                <div class="flex flex-col gap-6">
                    <div class="rounded-2xl border border-yellow-800/40 bg-[#1a1a1a] px-10 py-8 shadow-2xl">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
