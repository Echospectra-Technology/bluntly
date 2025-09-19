<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemaps = [
            [
                'loc' => route('sitemap.pages'),
                'lastmod' => now()->toISOString(),
            ],
            [
                'loc' => route('sitemap.stories'),
                'lastmod' => Story::where('status', 'published')->latest('updated_at')->value('updated_at')?->toISOString() ?? now()->toISOString(),
            ],
        ];

        return response()->view('sitemaps.index', compact('sitemaps'))
            ->header('Content-Type', 'application/xml');
    }

    public function pages()
    {
        $pages = [
            [
                'loc' => route('index'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('feed'),
                'lastmod' => Story::where('status', 'published')->latest('created_at')->value('created_at')?->toISOString() ?? now()->toISOString(),
                'changefreq' => 'hourly',
                'priority' => '0.9',
            ],
            [
                'loc' => route('post.create'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('about'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ],
            [
                'loc' => route('terms'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => route('privacy'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => route('cookies'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => route('rules'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
            [
                'loc' => route('safety'),
                'lastmod' => now()->toISOString(),
                'changefreq' => 'yearly',
                'priority' => '0.3',
            ],
        ];

        return response()->view('sitemaps.pages', compact('pages'))
            ->header('Content-Type', 'application/xml');
    }

    public function stories()
    {
        $stories = Story::where('status', 'published')
            ->select('slug', 'created_at', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($story) {
                return [
                    'loc' => route('post', $story->slug),
                    'lastmod' => $story->updated_at->toISOString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.7',
                ];
            });

        return response()->view('sitemaps.stories', compact('stories'))
            ->header('Content-Type', 'application/xml');
    }
}
