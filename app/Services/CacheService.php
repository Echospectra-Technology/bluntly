<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Story;
use App\Models\Tag;

class CacheService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const TRENDING_TTL = 1800; // 30 minutes
    private const POPULAR_TTL = 7200; // 2 hours

    public function getTrendingStories(int $limit = 5): array
    {
        return Cache::remember('trending_stories_' . $limit, self::TRENDING_TTL, function () use ($limit) {
            return Story::with(['tags'])
                ->where('status', 'published')
                ->where('created_at', '>=', now()->subDay())
                ->orderByRaw('(upvotes - downvotes) DESC')
                ->orderBy('views', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    public function getPopularStories(int $limit = 5): array
    {
        return Cache::remember('popular_stories_' . $limit, self::POPULAR_TTL, function () use ($limit) {
            return Story::with(['tags'])
                ->where('status', 'published')
                ->orderByRaw('(upvotes - downvotes) DESC')
                ->orderBy('views', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    public function getTrendingTags(int $limit = 8): array
    {
        return Cache::remember('trending_tags_' . $limit, self::TRENDING_TTL, function () use ($limit) {
            return Tag::withCount(['stories' => function ($query) {
                $query->where('status', 'published')
                    ->where('created_at', '>=', now()->subWeek());
            }])
            ->orderBy('stories_count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
        });
    }

    public function getStoryDetails(string $slug): ?array
    {
        return Cache::remember('story_details_' . $slug, self::CACHE_TTL, function () use ($slug) {
            $story = Story::with(['tags'])
                ->where('alias', $slug)
                ->where('status', 'published')
                ->first();

            return $story ? $story->toArray() : null;
        });
    }

    public function clearStoryCache(string $slug): void
    {
        Cache::forget('story_details_' . $slug);
        $this->clearListCaches();
    }

    public function clearListCaches(): void
    {
        Cache::forget('trending_stories_5');
        Cache::forget('popular_stories_5');
        Cache::forget('trending_tags_8');
        
        // Clear different limit variations
        for ($i = 1; $i <= 20; $i++) {
            Cache::forget('trending_stories_' . $i);
            Cache::forget('popular_stories_' . $i);
            Cache::forget('trending_tags_' . $i);
        }
    }

    public function warmCache(): void
    {
        // Pre-warm commonly accessed caches
        $this->getTrendingStories(5);
        $this->getPopularStories(5);
        $this->getTrendingTags(8);
    }

    public function getStoryViewCount(int $storyId): int
    {
        return Cache::remember('story_views_' . $storyId, 300, function () use ($storyId) {
            return \App\Models\StoryView::where('story_id', $storyId)->count();
        });
    }

    public function incrementStoryViews(int $storyId): void
    {
        Cache::forget('story_views_' . $storyId);
    }
}