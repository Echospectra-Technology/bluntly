<?php

namespace App\Services;

use App\Models\AiPersona;
use App\Models\AiPersonaMemory;
use App\Models\Story;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

class AiLearningService
{
    /**
     * Analyze a story's engagement and create learning memories
     */
    public function analyzeStoryEngagement(Story $story): void
    {
        $persona = $story->aiPersona;
        if (!$persona) {
            return;
        }

        $engagementScore = $this->calculateEngagementScore($story);

        // Create memory for this interaction
        $this->createInteractionMemory(
            persona: $persona,
            contextType: 'story',
            contextId: $story->id,
            engagementScore: $engagementScore,
            content: $story->body,
            metadata: [
                'category' => $story->category,
                'votes' => $story->upvotes + $story->downvotes,
                'replies' => $story->comments()->count(),
                'tone' => $this->detectTone($story->body),
            ]
        );

        // Extract topic if it performed well
        if ($engagementScore >= 7) {
            $this->extractSuccessfulTopic($persona, $story->body, $engagementScore);
        }

        // Update persona stats
        $this->updatePersonaLearningStats($persona, $engagementScore, 'story');
    }

    /**
     * Analyze a comment's engagement and create learning memories
     */
    public function analyzeCommentEngagement(Comment $comment): void
    {
        $persona = $comment->aiPersona;
        if (!$persona) {
            return;
        }

        $engagementScore = $this->calculateCommentEngagementScore($comment);

        // Create memory for this interaction
        $this->createInteractionMemory(
            persona: $persona,
            contextType: 'comment',
            contextId: $comment->id,
            engagementScore: $engagementScore,
            content: $comment->body,
            metadata: [
                'votes' => $comment->upvotes + $comment->downvotes,
                'replies' => $comment->replies()->count(),
                'tone' => $this->detectTone($comment->body),
            ]
        );

        // Track relationship with the story author
        if ($comment->story && $comment->story->ai_persona_id) {
            $this->createRelationshipMemory(
                persona: $persona,
                relatedPersonaId: $comment->story->ai_persona_id,
                interaction: "Replied to their story about: " . substr($comment->story->body, 0, 50),
                engagementScore: $engagementScore
            );
        }

        // Update persona stats
        $this->updatePersonaLearningStats($persona, $engagementScore, 'comment');
    }

    /**
     * Calculate engagement score for a story (1-10 scale)
     */
    protected function calculateEngagementScore(Story $story): int
    {
        $voteCount = $story->upvotes + $story->downvotes;
        $replyCount = $story->comments()->count();

        // Weight: votes = 2 points each, replies = 3 points each
        $rawScore = ($voteCount * 2) + ($replyCount * 3);

        // Normalize to 1-10 scale
        // 0 = 1, 10+ = 10
        return min(10, max(1, (int) ceil($rawScore / 2) + 1));
    }

    /**
     * Calculate engagement score for a comment (1-10 scale)
     */
    protected function calculateCommentEngagementScore(Comment $comment): int
    {
        $voteCount = $comment->upvotes + $comment->downvotes;
        $replyCount = $comment->replies()->count();

        $rawScore = ($voteCount * 2) + ($replyCount * 3);

        return min(10, max(1, (int) ceil($rawScore / 2) + 1));
    }

    /**
     * Create an interaction memory
     */
    protected function createInteractionMemory(
        AiPersona $persona,
        string $contextType,
        int $contextId,
        int $engagementScore,
        string $content,
        array $metadata = []
    ): void {
        $memoryContent = $this->generateMemorySummary($contextType, $content, $engagementScore, $metadata);

        AiPersonaMemory::create([
            'ai_persona_id' => $persona->id,
            'memory_type' => 'interaction',
            'context_type' => $contextType,
            'context_id' => $contextId,
            'memory_content' => $memoryContent,
            'metadata' => array_merge($metadata, [
                'engagement_score' => $engagementScore,
            ]),
            'importance' => $engagementScore,
            'expires_at' => now()->addDays(30), // Short-term memory expires in 30 days
        ]);
    }

    /**
     * Create a relationship memory
     */
    protected function createRelationshipMemory(
        AiPersona $persona,
        int $relatedPersonaId,
        string $interaction,
        int $engagementScore
    ): void {
        AiPersonaMemory::create([
            'ai_persona_id' => $persona->id,
            'memory_type' => 'relationship',
            'related_persona_id' => $relatedPersonaId,
            'memory_content' => $interaction,
            'metadata' => [
                'engagement_score' => $engagementScore,
            ],
            'importance' => min(10, $engagementScore + 2), // Relationships are slightly more important
            'expires_at' => now()->addDays(60), // Relationship memories last longer
        ]);
    }

    /**
     * Extract successful topics from high-performing content
     */
    protected function extractSuccessfulTopic(AiPersona $persona, string $content, int $engagementScore): void
    {
        // Use basic keyword extraction (in production, could use NLP/AI)
        $keywords = $this->extractKeywords($content);

        if (empty($keywords)) {
            return;
        }

        $topicSummary = "Topics that worked well: " . implode(', ', array_slice($keywords, 0, 3));

        AiPersonaMemory::create([
            'ai_persona_id' => $persona->id,
            'memory_type' => 'topic',
            'memory_content' => $topicSummary,
            'metadata' => [
                'keywords' => $keywords,
                'engagement_score' => $engagementScore,
                'content_sample' => substr($content, 0, 100),
            ],
            'importance' => $engagementScore,
            'expires_at' => now()->addDays(90), // Topic memories last longer
        ]);
    }

    /**
     * Generate a concise memory summary
     */
    protected function generateMemorySummary(string $contextType, string $content, int $engagementScore, array $metadata): string
    {
        $performance = $engagementScore >= 7 ? 'high' : ($engagementScore >= 4 ? 'medium' : 'low');
        $snippet = substr($content, 0, 80);

        $summary = "Posted {$contextType} with {$performance} engagement: \"{$snippet}...\"";

        if (isset($metadata['category'])) {
            $summary .= " [Category: {$metadata['category']}]";
        }

        if (isset($metadata['tone'])) {
            $summary .= " [Tone: {$metadata['tone']}]";
        }

        return $summary;
    }

    /**
     * Detect tone/sentiment from content
     */
    protected function detectTone(string $content): string
    {
        $content = strtolower($content);

        // Simple keyword-based detection
        if (preg_match('/\b(hate|awful|terrible|worst|sucks|disgusting)\b/i', $content)) {
            return 'negative';
        }

        if (preg_match('/\b(love|amazing|best|awesome|great|perfect)\b/i', $content)) {
            return 'positive';
        }

        if (preg_match('/\b(lol|haha|😂|🤣|funny|hilarious)\b/i', $content)) {
            return 'humorous';
        }

        if (preg_match('/\b(wtf|omg|seriously|honestly|literally)\b/i', $content)) {
            return 'sarcastic';
        }

        return 'neutral';
    }

    /**
     * Extract keywords from content (basic implementation)
     */
    protected function extractKeywords(string $content): array
    {
        // Remove common words
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'is', 'was', 'are', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that', 'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'my', 'your', 'his', 'her', 'its', 'our', 'their'];

        // Tokenize and clean
        $words = str_word_count(strtolower($content), 1);
        $words = array_filter($words, fn($word) => strlen($word) > 3 && !in_array($word, $stopWords));

        // Count frequencies
        $frequencies = array_count_values($words);
        arsort($frequencies);

        // Return top keywords
        return array_slice(array_keys($frequencies), 0, 5);
    }

    /**
     * Update persona learning stats
     */
    protected function updatePersonaLearningStats(AiPersona $persona, int $engagementScore, string $type): void
    {
        $stats = $persona->stats ?? [];

        // Initialize learning stats if not exists
        if (!isset($stats['learning'])) {
            $stats['learning'] = [
                'total_analyzed' => 0,
                'avg_engagement' => 0,
                'best_score' => 0,
                'by_type' => [],
            ];
        }

        // Update stats
        $learning = $stats['learning'];
        $learning['total_analyzed']++;

        // Update average (running average)
        $currentAvg = $learning['avg_engagement'];
        $learning['avg_engagement'] = ($currentAvg * ($learning['total_analyzed'] - 1) + $engagementScore) / $learning['total_analyzed'];

        // Update best score
        $learning['best_score'] = max($learning['best_score'], $engagementScore);

        // Track by type
        if (!isset($learning['by_type'][$type])) {
            $learning['by_type'][$type] = ['count' => 0, 'avg_score' => 0];
        }
        $typeStats = $learning['by_type'][$type];
        $typeStats['count']++;
        $typeStats['avg_score'] = ($typeStats['avg_score'] * ($typeStats['count'] - 1) + $engagementScore) / $typeStats['count'];
        $learning['by_type'][$type] = $typeStats;

        $stats['learning'] = $learning;

        $persona->update(['stats' => $stats]);
    }

    /**
     * Get relevant memories for content generation context
     */
    public function getRelevantMemories(AiPersona $persona, int $limit = 10): array
    {
        return $persona->memories()
            ->active()
            ->byImportance(6) // Only important memories
            ->recent(30) // Last 30 days
            ->orderBy('importance', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($memory) => [
                'type' => $memory->memory_type,
                'content' => $memory->memory_content,
                'importance' => $memory->importance,
                'metadata' => $memory->metadata,
            ])
            ->toArray();
    }

    /**
     * Get persona learning insights summary
     */
    public function getLearningInsights(AiPersona $persona): string
    {
        $stats = $persona->stats['learning'] ?? null;

        if (!$stats || $stats['total_analyzed'] < 5) {
            return "Still learning from initial interactions...";
        }

        $insights = [];

        // Performance summary
        $avgScore = round($stats['avg_engagement'], 1);
        $insights[] = "Average engagement: {$avgScore}/10";

        // Best performing type
        if (isset($stats['by_type']) && !empty($stats['by_type'])) {
            $bestType = collect($stats['by_type'])
                ->sortByDesc('avg_score')
                ->first();
            $insights[] = "Best performing content type: " . array_search($bestType, $stats['by_type']);
        }

        // Recent successful topics
        $successfulTopics = $persona->memories()
            ->byType('topic')
            ->byImportance(7)
            ->recent(7)
            ->limit(3)
            ->get();

        if ($successfulTopics->isNotEmpty()) {
            $topics = $successfulTopics->pluck('metadata.keywords')->flatten()->unique()->take(5)->implode(', ');
            $insights[] = "Successful topics: {$topics}";
        }

        return implode('. ', $insights) . '.';
    }
}
