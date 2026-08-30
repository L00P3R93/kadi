<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="theme-color" content="#f5c542">

{{-- Performance: Preconnect to asset origins --}}
<link rel="preconnect" href="{{ config('app.url') }}">
<link rel="dns-prefetch" href="{{ config('app.url') }}">

<x-seo
    :title="$title ?? null"
    :description="$description ?? null"
    :noindex="$noindex ?? false"
    :page="$page ?? 'home'"
/>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;800;900&family=Cinzel+Decorative:wght@400;700;900&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
