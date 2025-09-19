<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($stories as $story)
        <url>
            <loc>{{ $story['loc'] }}</loc>
            <lastmod>{{ $story['lastmod'] }}</lastmod>
            <changefreq>{{ $story['changefreq'] }}</changefreq>
            <priority>{{ $story['priority'] }}</priority>
        </url>
    @endforeach
</urlset>
