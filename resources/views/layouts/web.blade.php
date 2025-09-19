<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Bluntly - Say it. As it is.')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Anonymous stories, confessions, and rants. Share your truth without revealing your identity on Bluntly.')">
    <meta name="keywords" content="@yield('meta_keywords', 'anonymous stories, confessions, rants, anonymous posting, unfiltered content, share secrets')">
    <meta name="author" content="Bluntly">
    <meta name="robots" content="@yield('meta_robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('og_title', 'Bluntly - Say it. As it is.')">
    <meta property="og:description" content="@yield('og_description', 'The space to say what you can\'t say anywhere else. Anonymous voices, unfiltered truths.')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:site_name" content="Bluntly">
    <meta property="og:locale" content="en_US">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
        <meta property="og:image:width" content="@yield('og_image_width', '1200')">
        <meta property="og:image:height" content="@yield('og_image_height', '630')">
        <meta property="og:image:alt" content="@yield('og_image_alt', 'Bluntly - Anonymous Stories Platform')">
    @else
        <meta property="og:image" content="{{ asset('apple-touch-icon.png') }}">
        <meta property="og:image:width" content="192">
        <meta property="og:image:height" content="192">
        <meta property="og:image:alt" content="Bluntly - Anonymous Stories Platform">
    @endif

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="@yield('twitter_card_type', 'summary_large_image')">
    <meta name="twitter:title" content="@yield('og_title', 'Bluntly - Say it. As it is.')">
    <meta name="twitter:description" content="@yield('og_description', 'The space to say what you can\'t say anywhere else. Anonymous voices, unfiltered truths.')">
    @hasSection('og_image')
        <meta name="twitter:image" content="@yield('og_image')">
        <meta name="twitter:image:alt" content="@yield('og_image_alt', 'Bluntly - Anonymous Stories Platform')">
    @else
        <meta name="twitter:image" content="{{ asset('apple-touch-icon.png') }}">
        <meta name="twitter:image:alt" content="Bluntly - Anonymous Stories Platform">
    @endif

    <!-- Article specific meta tags -->
    @hasSection('article_published_time')
        <meta property="article:published_time" content="@yield('article_published_time')">
    @endif
    @hasSection('article_modified_time')
        <meta property="article:modified_time" content="@yield('article_modified_time')">
    @endif
    @hasSection('article_author')
        <meta property="article:author" content="@yield('article_author')">
    @endif
    @if(trim(view()->yieldContent('article_section')))
        <meta property="article:section" content="@yield('article_section')">
    @endif
    @if(trim(view()->yieldContent('article_tag')))
        <meta property="article:tag" content="@yield('article_tag')">
    @endif

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Favicon and app icons -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- JSON-LD Structured Data -->
    @stack('structured-data')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Hreflang tags for internationalization -->
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}" />
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}" />

    <!-- Additional head content -->
    @stack('head')
</head>

<body class="bg-white text-black font-sans antialiased">
    @yield('content')
</body>

</html>
