<?php

namespace App\Services;

use App\Models\Story;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AffinityTrackingService
{
    // Weight different interactions
    private const INTERACTION_WEIGHTS = [
        'view' => 1.0,
        'upvote' => 3.0,
        'downvote' => -1.0,
        'comment' => 5.0,
        'share' => 4.0,
    ];

    // Decay factor for older interactions (per day)
    private const DECAY_FACTOR = 0.98;

    public function trackInteraction(string $cookieHash, int $storyId, string $interactionType): void
    {
        try {
            $story = Story::with('tags')->find($storyId);
            if (!$story || $story->tags->isEmpty()) {
                return;
            }

            foreach ($story->tags as $tag) {
                $this->updateTagAffinity($cookieHash, null, $tag->id, $interactionType);
            }
        } catch (\Exception $e) {
            Log::error('Failed to track affinity interaction', [
                'cookie_hash' => $cookieHash,
                'story_id' => $storyId,
                'interaction_type' => $interactionType,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function trackInteractionForUser(int $userId, int $storyId, string $interactionType): void
    {
        try {
            $story = Story::with('tags')->find($storyId);
            if (!$story || $story->tags->isEmpty()) {
                return;
            }

            foreach ($story->tags as $tag) {
                $this->updateTagAffinity(null, $userId, $tag->id, $interactionType);
            }
        } catch (\Exception $e) {
            Log::error('Failed to track affinity interaction for user', [
                'user_id' => $userId,
                'story_id' => $storyId,
                'interaction_type' => $interactionType,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function updateTagAffinity(?string $cookieHash, ?int $anonymousUserId, int $tagId, string $interactionType): void
    {
        $weight = self::INTERACTION_WEIGHTS[$interactionType] ?? 1.0;

        $whereConditions = ['tag_id' => $tagId];
        $updateData = [
            'interaction_count' => DB::raw('interaction_count + 1'),
            'affinity_score' => DB::raw("affinity_score + {$weight}"),
            'last_interaction_type' => $interactionType,
            'last_interaction' => now(),
            'updated_at' => now(),
            'created_at' => DB::raw('COALESCE(created_at, NOW())'),
        ];

        if ($anonymousUserId) {
            $whereConditions['anonymous_user_id'] = $anonymousUserId;
            $updateData['anonymous_user_id'] = $anonymousUserId;
        } else {
            $whereConditions['cookie_hash'] = $cookieHash;
            $updateData['cookie_hash'] = $cookieHash;
        }

        DB::table('user_tag_affinities')
            ->updateOrInsert($whereConditions, $updateData);
    }

    public function getUserTagAffinities(string $cookieHash, int $limit = 50): array
    {
        $affinities = DB::table('user_tag_affinities')
            ->join('tags', 'user_tag_affinities.tag_id', '=', 'tags.id')
            ->where('user_tag_affinities.cookie_hash', $cookieHash)
            ->where('user_tag_affinities.affinity_score', '>', 0)
            ->orderBy('user_tag_affinities.affinity_score', 'desc')
            ->limit($limit)
            ->select([
                'tags.id',
                'tags.name',
                'user_tag_affinities.affinity_score',
                'user_tag_affinities.interaction_count',
                'user_tag_affinities.last_interaction'
            ])
            ->get();

        return $affinities->mapWithKeys(function ($affinity) {
            return [$affinity->id => [
                'name' => $affinity->name,
                'score' => (float) $affinity->affinity_score,
                'interactions' => $affinity->interaction_count,
                'last_interaction' => $affinity->last_interaction,
            ]];
        })->toArray();
    }

    public function getUserTagAffinitiesById(int $userId, int $limit = 50): array
    {
        $affinities = DB::table('user_tag_affinities')
            ->join('tags', 'user_tag_affinities.tag_id', '=', 'tags.id')
            ->where('user_tag_affinities.anonymous_user_id', $userId)
            ->where('user_tag_affinities.affinity_score', '>', 0)
            ->orderBy('user_tag_affinities.affinity_score', 'desc')
            ->limit($limit)
            ->select([
                'tags.id',
                'tags.name',
                'user_tag_affinities.affinity_score',
                'user_tag_affinities.interaction_count',
                'user_tag_affinities.last_interaction'
            ])
            ->get();

        return $affinities->mapWithKeys(function ($affinity) {
            return [$affinity->id => [
                'name' => $affinity->name,
                'score' => (float) $affinity->affinity_score,
                'interactions' => $affinity->interaction_count,
                'last_interaction' => $affinity->last_interaction,
            ]];
        })->toArray();
    }

    public function calculateStoryAffinityScore(string $cookieHash, Story $story): float
    {
        if ($story->tags->isEmpty()) {
            return 0.0;
        }

        $userAffinities = $this->getUserTagAffinities($cookieHash);
        
        if (empty($userAffinities)) {
            return 0.0;
        }

        $totalScore = 0.0;
        $matchedTags = 0;

        foreach ($story->tags as $tag) {
            if (isset($userAffinities[$tag->id])) {
                $totalScore += $userAffinities[$tag->id]['score'];
                $matchedTags++;
            }
        }

        if ($matchedTags === 0) {
            return 0.0;
        }

        // Normalize by number of matched tags and apply diminishing returns
        $avgScore = $totalScore / $matchedTags;
        
        // Apply logarithmic scaling to prevent one highly-weighted tag from dominating
        return min(log($avgScore + 1) / log(10), 2.0); // Cap at 2.0
    }

    public function calculateStoryAffinityScoreById(int $userId, Story $story): float
    {
        if ($story->tags->isEmpty()) {
            return 0.0;
        }

        $userAffinities = $this->getUserTagAffinitiesById($userId);
        
        if (empty($userAffinities)) {
            return 0.0;
        }

        $totalScore = 0.0;
        $matchedTags = 0;

        foreach ($story->tags as $tag) {
            if (isset($userAffinities[$tag->id])) {
                $totalScore += $userAffinities[$tag->id]['score'];
                $matchedTags++;
            }
        }

        if ($matchedTags === 0) {
            return 0.0;
        }

        // Normalize by number of matched tags and apply diminishing returns
        $avgScore = $totalScore / $matchedTags;
        
        // Apply logarithmic scaling to prevent one highly-weighted tag from dominating
        return min(log($avgScore + 1) / log(10), 2.0); // Cap at 2.0
    }

    public function decayAffinities(int $daysOld = 1): int
    {
        $cutoffDate = now()->subDays($daysOld);
        $decayMultiplier = pow(self::DECAY_FACTOR, $daysOld);

        $affectedRows = DB::table('user_tag_affinities')
            ->where('last_interaction', '<', $cutoffDate)
            ->where('affinity_score', '>', 0.1) // Don't decay scores below 0.1
            ->update([
                'affinity_score' => DB::raw("affinity_score * {$decayMultiplier}"),
                'updated_at' => now(),
            ]);

        // Clean up very low scores
        DB::table('user_tag_affinities')
            ->where('affinity_score', '<', 0.01)
            ->delete();

        return $affectedRows;
    }

    public function getUserTopTags(string $cookieHash, int $limit = 10): array
    {
        return DB::table('user_tag_affinities')
            ->join('tags', 'user_tag_affinities.tag_id', '=', 'tags.id')
            ->where('user_tag_affinities.cookie_hash', $cookieHash)
            ->where('user_tag_affinities.affinity_score', '>', 0)
            ->orderBy('user_tag_affinities.affinity_score', 'desc')
            ->limit($limit)
            ->pluck('tags.name')
            ->toArray();
    }

    public function clearUserAffinities(string $cookieHash): void
    {
        DB::table('user_tag_affinities')
            ->where('cookie_hash', $cookieHash)
            ->delete();
    }

    public function getAffinityStats(): array
    {
        $stats = DB::table('user_tag_affinities')
            ->selectRaw('
                COUNT(DISTINCT cookie_hash) as total_users,
                COUNT(*) as total_affinities,
                AVG(affinity_score) as avg_score,
                MAX(affinity_score) as max_score,
                COUNT(CASE WHEN last_interaction >= ? THEN 1 END) as recent_interactions
            ', [now()->subDays(7)])
            ->first();

        $topTags = DB::table('user_tag_affinities')
            ->join('tags', 'user_tag_affinities.tag_id', '=', 'tags.id')
            ->selectRaw('tags.name, COUNT(*) as user_count, AVG(affinity_score) as avg_score')
            ->groupBy('tags.id', 'tags.name')
            ->orderBy('user_count', 'desc')
            ->limit(10)
            ->get();

        return [
            'overview' => $stats,
            'top_tags' => $topTags,
        ];
    }
}