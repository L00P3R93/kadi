@php
    $title = 'Page Not Found | Kadi';
    $description = 'The page you were looking for doesn\'t exist. Head back to Kadi and keep playing.';
    $noindex = true;
    $page = '404';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#0a0a0a] antialiased" style="font-family: 'Outfit', sans-serif;">
        <nav class="border-b border-[#f5c542]/10 px-6 py-4">
            <a href="{{ route('home') }}" class="text-xl tracking-widest text-[#f5c542]" style="font-family: 'Cinzel', serif;">
                ♠ {{ strtoupper(config('app.name')) }}
            </a>
        </nav>

        <main class="flex min-h-[70vh] flex-col items-center justify-center px-6 py-24 text-center">
            <div class="text-sm font-semibold uppercase tracking-[0.3em] text-[#f5c542]/70" style="font-family: 'Cinzel', serif;">
                Error 404
            </div>
            <h1 class="mt-4 text-4xl font-bold text-[#f5f5f0] md:text-5xl" style="font-family: 'Cinzel', serif;">
                This Hand's Not in Play
            </h1>
            <p class="mt-4 max-w-md text-base text-[#6b6b6b]">
                The page you're looking for doesn't exist or may have moved. Let's get you back to the table.
            </p>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('home') }}" wire:navigate
                   class="btn-casino-primary inline-block rounded-full px-8 py-4 no-underline">
                    Back to Home
                </a>
                <a href="{{ route('rules') }}" wire:navigate
                   class="btn-casino-ghost inline-block rounded-full px-8 py-4 no-underline">
                    Read the Rules
                </a>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-6 text-sm text-[#6b6b6b]">
                <a href="{{ route('game-guide') }}" class="transition hover:text-[#f5c542]">Games & Prizes</a>
                <a href="{{ route('legal.terms') }}" class="transition hover:text-[#f5c542]">Terms</a>
                <a href="{{ route('legal.privacy') }}" class="transition hover:text-[#f5c542]">Privacy</a>
            </div>
        </main>
    </body>
</html>
