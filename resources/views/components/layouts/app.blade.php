<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Bluntly - Say it. As it is.')</title>
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="@yield('og_title', 'Bluntly - Say it. As it is.')">
    <meta property="og:description" content="@yield('og_description', 'The space to say what you can\'t say anywhere else. Anonymous voices, unfiltered truths.')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('og_url', url()->current())">
    <meta property="og:site_name" content="Bluntly">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('og_title', 'Bluntly - Say it. As it is.')">
    <meta name="twitter:description" content="@yield('og_description', 'The space to say what you can\'t say anywhere else. Anonymous voices, unfiltered truths.')">
    
    <!-- Additional Meta Tags -->
    <meta name="description" content="@yield('meta_description', 'Anonymous stories, confessions, and rants. Share your truth without revealing your identity on Bluntly.')">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white text-black font-sans antialiased">
    {{ $slot }}
    
    <!-- Toast Notifications -->
    <x-toast />
</body>

</html>