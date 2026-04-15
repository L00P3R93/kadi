@props([
    'title'       => null,
    'description' => null,
    'noindex'     => false,
    'page'        => 'home',
])

@php
    $siteName      = config('app.name', 'Angel Palace');
    $appUrl        = rtrim(config('app.url', 'https://kadikings.co.ke'), '/');
    $resolvedTitle = filled($title) ? $title : $siteName;
    $resolvedDesc  = filled($description)
        ? $description
        : 'Kenya\'s premier online casino — kadi, casino, sports betting and instant payouts. Play now at Angel Palace.';
    $ogImage   = $appUrl . '/images/og-default-angel.png';
    $canonical = $appUrl . request()->getPathInfo();
@endphp

<title>{{ $resolvedTitle }}</title>

<meta name="description" content="{{ $resolvedDesc }}">
@if ($noindex)
<meta name="robots" content="noindex,nofollow">
@else
<meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">
@endif
<link rel="canonical" href="{{ $canonical }}">

<meta property="og:type"         content="website">
<meta property="og:locale"       content="en_KE">
<meta property="og:site_name"    content="{{ $siteName }}">
<meta property="og:title"        content="{{ $resolvedTitle }}">
<meta property="og:description"  content="{{ $resolvedDesc }}">
<meta property="og:url"          content="{{ $canonical }}">
<meta property="og:image"        content="{{ $ogImage }}">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt"    content="{{ $siteName }} — Online Casino Kenya">

<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $resolvedTitle }}">
<meta name="twitter:description" content="{{ $resolvedDesc }}">
<meta name="twitter:image"       content="{{ $ogImage }}">

<meta name="geo.region"    content="KE">
<meta name="geo.placename" content="Nairobi, Kenya">
<meta name="language"      content="English">
