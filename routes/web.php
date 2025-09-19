<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Volt::route('/', 'index')->name('index');
Route::get('/', function () {
    // Get trending stories for homepage
    $trendingStories = \App\Models\Story::with(['tags'])
        ->where('status', 'published')
        ->where('created_at', '>=', now()->subWeek())
        ->orderByRaw('(upvotes - downvotes) DESC')
        ->orderBy('views', 'desc')
        ->take(3)
        ->get();

    return view('index', compact('trendingStories'));
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
Volt::route('/post/{slug}', 'pages.story-details')->name('post');

// Trending route - redirect to stories with trending filter
Route::get('/trending', function () {
    return redirect()->route('feed', ['filter' => 'trending']);
})->name('trending');
