<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="color-scheme" content="light" />

@php
    $appName = config('app.name', 'Before You Buy');
    $pageTitle = filled($title ?? null) ? $title.' - '.$appName : $appName;
    $pageDescription = $description ?? 'Keep track of what you and the people you care about already own before buying something twice.';
    $pageUrl = $canonical ?? request()->url();
    $socialImage = asset('social-card.png');
@endphp

<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDescription }}">
<link rel="canonical" href="{{ $pageUrl }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:image" content="{{ $socialImage }}">
<meta property="og:image:secure_url" content="{{ $socialImage }}">
<meta property="og:image:type" content="image/png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="Before You Buy — Know what you have before you buy.">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $socialImage }}">
<meta name="twitter:image:alt" content="Before You Buy — Know what you have before you buy.">

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
