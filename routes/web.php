<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Volt::route('/', 'index')->name('index');
Route::get('/', function () {
    // Get trending stories for homepage
    $trendingStories = \App\Models\Story::with(['tags', 'theme'])
        ->where('status', 'published')
        ->where('created_at', '>=', now()->subWeek())
        ->orderByRaw('(upvotes - downvotes) DESC')
        ->orderBy('views', 'desc')
        ->take(3)
        ->get();

    // Get current weekly theme
    $currentTheme = \App\Models\WeeklyTheme::current()->first();

    return view('index', compact('trendingStories', 'currentTheme'));
})->name('index');

// Health check endpoint
Route::get('/health', function () {
    try {
        // Check database connection
        DB::connection()->getPdo();

        // Check if we can read stories
        $storyCount = \App\Models\Story::count();

        return response()->json([
            'status'        => 'healthy',
            'database'      => 'connected',
            'stories_count' => $storyCount,
            'timestamp'     => now()->toISOString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status'    => 'unhealthy',
            'error'     => $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
});

Volt::route('/posts', 'pages.stories')->name('feed');
Volt::route('/post/create', 'pages.create-post')->name('post.create');
Volt::route('/themes', 'pages.themes')->name('themes');
Volt::route('/theme/{slug}', 'pages.theme-details')->name('theme.details');

// Story details route with Open Graph support
Route::get('/post/{slug}', function ($slug) {
    $story = \App\Models\Story::with(['tags', 'comments.replies'])
        ->where('slug', $slug)
        ->where('status', 'published')
        ->firstOrFail();

    return view('story-details', compact('story'));
})->name('post');

// Trending route - redirect to stories with trending filter
Route::get('/trending', function () {
    return redirect()->route('feed', ['filter' => 'trending']);
})->name('trending');

// Static pages
Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/cookies', function () {
    return view('pages.cookies');
})->name('cookies');

Route::get('/rules', function () {
    return view('pages.rules');
})->name('rules');

Route::get('/safety', function () {
    return view('pages.safety');
})->name('safety');

Route::get('/moderation', function () {
    return view('pages.moderation');
})->name('moderation');

Route::get('/report', function () {
    return view('pages.report');
})->name('report');

// SEO routes
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-pages.xml', [\App\Http\Controllers\SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-stories.xml', [\App\Http\Controllers\SitemapController::class, 'stories'])->name('sitemap.stories');
