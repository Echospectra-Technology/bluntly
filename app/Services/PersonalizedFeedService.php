<?php

namespace App\Services;

use App\Models\Story;
use App\Models\WeeklyTheme;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class PersonalizedFeedService
{
    private FeedScoringService $scoringService;
    private GeolocationService $geolocationService;
    private AffinityTrackingService $affinityService;
    
    // Feed composition percentages
    private const FEED_COMPOSITION = [
        'local' => 0.65,        // 65% local/regional content
        'interest' => 0.15,     // 15% interest-matched content
        'trending' => 0.10,     // 10% globally trending content
        'theme' => 0.05,        // 5% current theme spotlight
        'exploration' => 0.05,  // 5% random exploration
    ];

    public function __construct(
        FeedScoringService $scoringService,
        GeolocationService $geolocationService,
        AffinityTrackingService $affinityService
    ) {
        $this->scoringService = $scoringService;
        $this->geolocationService = $geolocationService;
        $this->affinityService = $affinityService;
    }

    public function getPersonalizedFeed(
        string $cookieHash,
        array $userLocation,
        int $totalItems = 50,
        array $excludeIds = [],
        string $category = 'all',
        ?int $themeId = null,
        string $experimentName = 'default'
    ): Collection {
        
        // If filtering by theme, return theme-specific feed
        if ($themeId) {
            return $this->getThemeFeed($themeId, $cookieHash, $userLocation, $totalItems, $excludeIds, $experimentName);
        }

        // If filtering by category, adjust feed composition
        if ($category !== 'all') {
            return $this->getCategoryFeed($category, $cookieHash, $userLocation, $totalItems, $excludeIds, $experimentName);
        }

        // Get hidden posts for this user
        $hiddenIds = $this->getHiddenPostIds($cookieHash);
        $allExcludeIds = array_merge($excludeIds, $hiddenIds);

        // Calculate how many items from each bucket
        $bucketSizes = $this->calculateBucketSizes($totalItems);

        // Collect stories from each bucket
        $feedStories = collect();

        // 1. Local Priority Bucket (65%)
        $localStories = $this->getLocalStories(
            $userLocation,
            $bucketSizes['local'],
            $allExcludeIds
        );
        $feedStories = $feedStories->concat($localStories);
        $allExcludeIds = array_merge($allExcludeIds, $localStories->pluck('id')->toArray());

        // 2. Interest-Based Bucket (15%)
        $interestStories = $this->getInterestBasedStories(
            $cookieHash,
            $bucketSizes['interest'],
            $allExcludeIds
        );
        $feedStories = $feedStories->concat($interestStories);
        $allExcludeIds = array_merge($allExcludeIds, $interestStories->pluck('id')->toArray());

        // 3. Trending Bucket (10%)
        $trendingStories = $this->getTrendingStories(
            $bucketSizes['trending'],
            $allExcludeIds
        );
        $feedStories = $feedStories->concat($trendingStories);
        $allExcludeIds = array_merge($allExcludeIds, $trendingStories->pluck('id')->toArray());

        // 4. Theme Spotlight Bucket (5%)
        $themeStories = $this->getCurrentThemeStories(
            $bucketSizes['theme'],
            $allExcludeIds
        );
        $feedStories = $feedStories->concat($themeStories);
        $allExcludeIds = array_merge($allExcludeIds, $themeStories->pluck('id')->toArray());

        // 5. Exploration Bucket (5%)
        $explorationStories = $this->getExplorationStories(
            $cookieHash,
            $bucketSizes['exploration'],
            $allExcludeIds
        );
        $feedStories = $feedStories->concat($explorationStories);
        $allExcludeIds = array_merge($allExcludeIds, $explorationStories->pluck('id')->toArray());

        // 6. Fallback: Fill remaining slots with general stories if we don't have enough
        if ($feedStories->count() < $totalItems) {
            $remainingSlots = $totalItems - $feedStories->count();
            $fallbackStories = $this->getFallbackStories($remainingSlots, $allExcludeIds, $category);
            $feedStories = $feedStories->concat($fallbackStories);
        }

        // Final scoring and ranking
        return $this->finalizeAndRankFeed($feedStories, $cookieHash, $userLocation, $experimentName);
    }

    private function calculateBucketSizes(int $totalItems): array
    {
        return [
            'local' => (int) ceil($totalItems * self::FEED_COMPOSITION['local']),
            'interest' => (int) ceil($totalItems * self::FEED_COMPOSITION['interest']),
            'trending' => (int) ceil($totalItems * self::FEED_COMPOSITION['trending']),
            'theme' => (int) ceil($totalItems * self::FEED_COMPOSITION['theme']),
            'exploration' => (int) ceil($totalItems * self::FEED_COMPOSITION['exploration']),
        ];
    }

    private function getLocalStories(array $userLocation, int $limit, array $excludeIds): Collection
    {
        $query = Story::with(['tags', 'comments', 'theme'])
            ->where('status', 'published')
            ->whereNotIn('id', $excludeIds);

        // Prioritize by region hierarchy
        if ($userLocation['region'] && $userLocation['region'] !== 'global') {
            $regionHierarchy = $this->geolocationService->getLocationHierarchy($userLocation);
            
            // Build case statement for region priority
            $cases = [];
            $priority = count($regionHierarchy);
            foreach ($regionHierarchy as $region) {
                $cases[] = "WHEN region = '{$region}' THEN {$priority}";
                $priority--;
            }
            
            if (!empty($cases)) {
                $caseStatement = 'CASE ' . implode(' ', $cases) . ' ELSE 0 END';
                $query->orderByRaw("{$caseStatement} DESC");
            }
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit * 2) // Get extra to allow for scoring
            ->get()
            ->take($limit);
    }

    private function getInterestBasedStories(string $cookieHash, int $limit, array $excludeIds): Collection
    {
        $userAffinities = $this->affinityService->getUserTagAffinities($cookieHash, 20);
        
        if (empty($userAffinities)) {
            return collect();
        }

        $tagIds = array_keys($userAffinities);
        
        return Story::with(['tags', 'comments', 'theme'])
            ->where('status', 'published')
            ->whereNotIn('id', $excludeIds)
            ->whereHas('tags', function ($query) use ($tagIds) {
                $query->whereIn('tags.id', $tagIds);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getTrendingStories(int $limit, array $excludeIds): Collection
    {
        return Story::with(['tags', 'comments', 'theme'])
            ->withCount('comments')
            ->where('status', 'published')
            ->whereNotIn('id', $excludeIds)
            ->where('created_at', '>=', now()->subDays(7)) // Last week only
            ->orderByRaw('(upvotes - downvotes) DESC')
            ->orderBy('comments_count', 'desc')
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getCurrentThemeStories(int $limit, array $excludeIds): Collection
    {
        $currentTheme = WeeklyTheme::current()->first();
        
        if (!$currentTheme) {
            return collect();
        }

        return Story::with(['tags', 'comments', 'theme'])
            ->where('status', 'published')
            ->whereNotIn('id', $excludeIds)
            ->where('theme_id', $currentTheme->id)
            ->orderBy('spotlight_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getExplorationStories(string $cookieHash, int $limit, array $excludeIds): Collection
    {
        // Get categories user hasn't seen recently
        $recentCategories = DB::table('views')
            ->join('stories', 'views.story_id', '=', 'stories.id')
            ->where('views.cookie_hash', $cookieHash)
            ->where('views.created_at', '>=', now()->subDays(7))
            ->distinct()
            ->pluck('stories.category')
            ->filter() // Remove null values
            ->toArray();

        $query = Story::with(['tags', 'comments', 'theme'])
            ->where('status', 'published')
            ->whereNotIn('id', $excludeIds);

        if (!empty($recentCategories)) {
            $query->whereNotIn('category', $recentCategories);
        }

        return $query->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    private function getThemeFeed(int $themeId, string $cookieHash, array $userLocation, int $limit, array $excludeIds, string $experimentName): Collection
    {
        $hiddenIds = $this->getHiddenPostIds($cookieHash);
        $allExcludeIds = array_merge($excludeIds, $hiddenIds);

        $stories = Story::with(['tags', 'comments', 'theme'])
            ->where('status', 'published')
            ->where('theme_id', $themeId)
            ->whereNotIn('id', $allExcludeIds)
            ->orderBy('spotlight_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return $this->finalizeAndRankFeed($stories, $cookieHash, $userLocation, $experimentName);
    }

    private function getCategoryFeed(string $category, string $cookieHash, array $userLocation, int $limit, array $excludeIds, string $experimentName): Collection
    {
        $hiddenIds = $this->getHiddenPostIds($cookieHash);
        $allExcludeIds = array_merge($excludeIds, $hiddenIds);

        $stories = Story::with(['tags', 'comments', 'theme'])
            ->where('status', 'published')
            ->where('category', $category)
            ->whereNotIn('id', $allExcludeIds)
            ->orderBy('created_at', 'desc')
            ->limit($limit * 2) // Get extra for scoring
            ->get();

        return $this->finalizeAndRankFeed($stories, $cookieHash, $userLocation, $experimentName)
            ->take($limit);
    }

    private function finalizeAndRankFeed(Collection $stories, string $cookieHash, array $userLocation, string $experimentName): Collection
    {
        if ($stories->isEmpty()) {
            return collect();
        }

        // Calculate final scores for all stories
        $recentCategories = $this->getRecentViewingHistory($cookieHash);
        $scores = $this->scoringService->batchCalculateScores(
            $stories->all(),
            $cookieHash,
            $userLocation,
            $recentCategories,
            $experimentName
        );

        // Sort by calculated scores
        return $stories->sortByDesc(function ($story) use ($scores) {
            return $scores[$story->id] ?? 0;
        })->values();
    }

    private function getHiddenPostIds(string $cookieHash): array
    {
        return DB::table('user_hidden_posts')
            ->where('cookie_hash', $cookieHash)
            ->pluck('story_id')
            ->toArray();
    }

    private function getRecentViewingHistory(string $cookieHash): array
    {
        return DB::table('views')
            ->join('stories', 'views.story_id', '=', 'stories.id')
            ->where('views.cookie_hash', $cookieHash)
            ->where('views.created_at', '>=', now()->subHours(24))
            ->whereNotNull('stories.category')
            ->groupBy('stories.category')
            ->selectRaw('stories.category, COUNT(*) as count')
            ->pluck('count', 'category')
            ->toArray();
    }

    public function hidePost(string $cookieHash, int $storyId, string $reason = 'not_interested'): void
    {
        DB::table('user_hidden_posts')->updateOrInsert(
            ['cookie_hash' => $cookieHash, 'story_id' => $storyId],
            ['reason' => $reason, 'hidden_at' => now()]
        );
    }

    public function getFeedStats(string $cookieHash): array
    {
        $userAffinities = $this->affinityService->getUserTagAffinities($cookieHash, 10);
        $hiddenCount = DB::table('user_hidden_posts')
            ->where('cookie_hash', $cookieHash)
            ->count();

        return [
            'top_interests' => array_keys($userAffinities),
            'hidden_posts_count' => $hiddenCount,
            'personalization_score' => min(count($userAffinities) / 10, 1.0), // 0-1 scale
        ];
    }

    private function getFallbackStories(int $limit, array $excludeIds, string $category): Collection
    {
        $query = Story::with(['tags', 'comments', 'theme'])
            ->where('status', 'published')
            ->whereNotIn('id', $excludeIds);

        // Apply category filter if specified
        if ($category !== 'all') {
            $query->where('category', $category);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}