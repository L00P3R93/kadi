@props([
    'page'    => 'home',
    'noindex' => false,
])
@php
    $appUrl   = rtrim(config('app.url', 'https://kadikings.co.ke'), '/');
    $siteName = config('app.name', 'Kadi Kings');
    $emit     = ! $noindex;
    $isHome   = $emit && $page === 'home';
    $isGames  = $emit && $page === 'games';
@endphp

@if ($emit)
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "{{ $siteName }}",
    "url": "{{ $appUrl }}",
    "logo": {
        "@@type": "ImageObject",
        "url": "{{ $appUrl }}/favicon.svg",
        "width": 512,
        "height": 512
    },
    "contactPoint": {
        "@@type": "ContactPoint",
        "contactType": "customer support",
        "email": "support@kadikings.co.ke",
        "availableLanguage": ["English", "Swahili"]
    },
    "sameAs": []
}
</script>
@endif

@if ($isHome)
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebSite",
    "name": "{{ $siteName }}",
    "url": "{{ $appUrl }}",
    "potentialAction": {
        "@@type": "SearchAction",
        "target": {
            "@@type": "EntryPoint",
            "urlTemplate": "{{ $appUrl }}/lobby?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    }
}
</script>
@endif

@if ($isGames)
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "WebPage",
    "name": "Casino Games \u2014 Slots & Table Games | {{ $siteName }}",
    "description": "Browse all casino games at Kadi Kings \u2014 slots, blackjack, roulette, and more.",
    "url": "{{ $appUrl }}/lobby",
    "breadcrumb": {
        "@@type": "BreadcrumbList",
        "itemListElement": [
            { "@@type": "ListItem", "position": 1, "name": "Home",  "item": "{{ $appUrl }}" },
            { "@@type": "ListItem", "position": 2, "name": "Games", "item": "{{ $appUrl }}/lobby" }
        ]
    }
}
</script>
@endif
