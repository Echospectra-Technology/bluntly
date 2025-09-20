<?php

namespace App\Services;

use App\Models\Story;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FeedScoringService
{
    private GeolocationService $geolocationService;
    private AffinityTrackingService $affinityService;
    
    // Default weights (can be overridden by database config)
    private const DEFAULT_WEIGHTS = [
        'region' => 2.0,
        'affinity' => 1.5,
        'engagement' => 1.0,
        'recency' => 0.8,
        'spotlight' => 3.0,
        'diversity' => -0.5,
    ];

    public function __construct(
        GeolocationService $geolocationService,
        AffinityTrackingService $affinityService
    ) {
        $this->geolocationService = $geolocationService;
        $this->affinityService = $affinityService;
    }

    public function calculateFeedScore(
        Story $story,
        string $cookieHash,
        array $userLocation,
        array $recentlyViewedCategories = [],
        string $experimentName = 'default'
    ): float {
        $weights = $this->getExperimentWeights($experimentName);
        
        $regionScore = $this->calculateRegionScore($story, $userLocation);
        $affinityScore = $this->calculateAffinityScore($story, $cookieHash);
        $engagementScore = $this->calculateEngagementScore($story);
        $recencyScore = $this->calculateRecencyScore($story);
        $spotlightScore = $this->calculateSpotlightScore($story);
        $diversityScore = $this->calculateDiversityScore($story, $recentlyViewedCategories);

        $totalScore = 
            ($regionScore * $weights['region']) +
            ($affinityScore * $weights['affinity']) +
            ($engagementScore * $weights['engagement']) +
            ($recencyScore * $weights['recency']) +
            ($spotlightScore * $weights['spotlight']) +
            ($diversityScore * $weights['diversity']);

        return max(0, $totalScore); // Ensure non-negative
    }

    private function calculateRegionScore(Story $story, array $userLocation): float
    {
        if (!$story->region || !$userLocation['region']) {
            return 0.1; // Small base score for content without region
        }

        return $this->geolocationService->calculateRegionScore(
            $userLocation['region'],
            $story->region
        );
    }

    private function calculateAffinityScore(Story $story, string $cookieHash): float
    {
        return $this->affinityService->calculateStoryAffinityScore($cookieHash, $story);
    }

    private function calculateEngagementScore(Story $story): float
    {
        // Normalize engagement metrics
        $upvoteRatio = $story->upvotes > 0 ? 
            $story->upvotes / max(1, $story->upvotes + $story->downvotes) : 0;
        
        $commentScore = min(log($story->comments_count + 1) / log(10), 2.0); // Cap at 2.0
        $viewScore = min(log($story->views + 1) / log(100), 2.0); // Cap at 2.0
        
        // Weighted combination
        return ($upvoteRatio * 0.4) + ($commentScore * 0.4) + ($viewScore * 0.2);
    }

    private function calculateRecencyScore(Story $story): float
    {
        $hoursOld = $story->created_at->diffInHours(now());
        
        // Exponential decay: fresher content gets higher scores
        // Score drops to 0.5 after 24 hours, 0.25 after 48 hours, etc.
        return exp(-$hoursOld / 24);
    }

    private function calculateSpotlightScore(Story $story): float
    {
        if ($story->spotlight_score <= 0) {
            return 0.0;
        }

        // Normalize spotlight score (assuming max is 100)
        return min($story->spotlight_score / 100, 1.0);
    }

    private function calculateDiversityScore(Story $story, array $recentlyViewedCategories): float
    {
        if (empty($recentlyViewedCategories) || !$story->category) {
            return 0.0; // No penalty if no recent history
        }

        $categoryCount = $recentlyViewedCategories[$story->category] ?? 0;
        
        // Penalize over-representation of categories
        // Linear penalty: -0.1 for each additional story in same category
        return -min($categoryCount * 0.1, 1.0);
    }

    private function getExperimentWeights(string $experimentName): array
    {
        $cacheKey = "feed_weights_{$experimentName}";
        
        return Cache::remember($cacheKey, 3600, function () use ($experimentName) {
            $weights = self::DEFAULT_WEIGHTS;
            
            $dbWeights = DB::table('feed_weights')
                ->where('experiment_name', $experimentName)
                ->where('is_active', true)
                ->pluck('weight_value', 'weight_type')
                ->toArray();
            
            return array_merge($weights, $dbWeights);
        });
    }

    public function batchCalculateScores(
        array $stories,
        string $cookieHash,
        array $userLocation,
        array $recentlyViewedCategories = [],
        string $experimentName = 'default'
    ): array {
        $scores = [];
        
        foreach ($stories as $story) {
            $scores[$story->id] = $this->calculateFeedScore(
                $story,
                $cookieHash,
                $userLocation,
                $recentlyViewedCategories,
                $experimentName
            );
        }
        
        return $scores;
    }

    public function updateStoredScores(array $storyScores): void
    {
        $cases = [];
        $ids = [];
        
        foreach ($storyScores as $storyId => $score) {
            $cases[] = "WHEN {$storyId} THEN {$score}";
            $ids[] = $storyId;
        }
        
        if (!empty($cases)) {
            $casesString = implode(' ', $cases);
            $idsString = implode(',', $ids);
            
            DB::statement("
                UPDATE stories 
                SET computed_feed_score = CASE id {$casesString} END,
                    updated_at = NOW()
                WHERE id IN ({$idsString})
            ");
        }
    }

    public function getTopScoringStories(
        string $cookieHash,
        array $userLocation,
        int $limit = 50,
        array $excludeIds = [],
        string $experimentName = 'default'
    ): array {
        $query = Story::with(['tags', 'comments', 'theme'])
            ->where('status', 'published');
            
        if (!empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }
        
        $stories = $query->get();
        
        // Get recent viewing history for diversity calculation
        $recentCategories = $this->getRecentViewingHistory($cookieHash);
        
        // Calculate scores for all stories
        $scores = $this->batchCalculateScores(
            $stories,
            $cookieHash,
            $userLocation,
            $recentCategories,
            $experimentName
        );
        
        // Sort by score and limit
        arsort($scores);
        $topScores = array_slice($scores, 0, $limit, true);
        
        // Return stories in score order
        return $stories->whereIn('id', array_keys($topScores))
            ->sortBy(function ($story) use ($topScores) {
                return array_search($story->id, array_keys($topScores));
            })
            ->values()
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

    public function clearScoreCache(): void
    {
        Cache::forget('feed_weights_default');
        // Clear other experiment caches if needed
    }
}