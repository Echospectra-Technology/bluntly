<?php

namespace App\Services;

use App\Models\AiPersona;
use App\Models\Story;
use App\Models\Comment;
use App\Models\WeeklyTheme;
use OpenAI;

class AiContentGenerator
{
    protected $client;

    public function __construct()
    {
        $apiKey = config('services.openai.key');

        if ($apiKey) {
            $this->client = OpenAI::client($apiKey);
        }
    }

    /**
     * Generate a post for an AI persona
     */
    public function generatePost(AiPersona $persona, ?WeeklyTheme $theme = null): array
    {
        $category = $this->selectCategory($persona);

        $systemPrompt = $this->buildSystemPrompt($persona);
        $userPrompt = $this->buildPostPrompt($persona, $theme, $category);

        $response = $this->client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 500,
            'temperature' => 0.8,
        ]);

        $content = trim($response->choices[0]->message->content);

        // Generate title based on content
        $title = $this->generateTitle($content, $category);

        // Generate tags based on title and content
        $tags = $this->generateTags($title, $content, $category);

        return [
            'title' => $title,
            'content' => $content,
            'category' => $category,
            'tags' => $tags,
        ];
    }

    /**
     * Generate a reply to a story
     */
    public function generateReply(AiPersona $persona, Story $story): string
    {
        $systemPrompt = $this->buildSystemPrompt($persona);
        $userPrompt = $this->buildReplyPrompt($persona, $story);

        $response = $this->client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 300,
            'temperature' => 0.8,
        ]);

        return trim($response->choices[0]->message->content);
    }

    /**
     * Generate a reply to a comment
     */
    public function generateCommentReply(AiPersona $persona, Comment $comment): string
    {
        $systemPrompt = $this->buildSystemPrompt($persona);
        $userPrompt = $this->buildCommentReplyPrompt($persona, $comment);

        $response = $this->client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 250,
            'temperature' => 0.8,
        ]);

        return trim($response->choices[0]->message->content);
    }

    /**
     * Decide if persona should reply to a story
     */
    public function shouldReply(AiPersona $persona, Story $story): bool
    {
        $replyProbability = $persona->getReplyProbability();
        $topicsOfInterest = $persona->getTopicsOfInterest();

        // Random chance based on reply probability
        if (rand(0, 100) > $replyProbability) {
            return false;
        }

        // Check if story matches topics of interest
        if (!empty($topicsOfInterest)) {
            $storyText = strtolower($story->title . ' ' . $story->body);
            foreach ($topicsOfInterest as $topic) {
                if (str_contains($storyText, strtolower($topic))) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Decide if persona should reply to a comment
     */
    public function shouldReplyToComment(AiPersona $persona, Comment $comment): bool
    {
        // Higher probability for replying to comments on own posts
        $isOwnPost = $comment->story && $comment->story->ai_persona_id === $persona->id;

        if ($isOwnPost) {
            // 70% chance to reply to comments on own posts
            return rand(0, 100) <= 70;
        }

        // For comments on other posts, use lower probability
        $replyProbability = $persona->getReplyProbability();

        // 50% of normal reply probability for comment threads
        $adjustedProbability = $replyProbability * 0.5;

        return rand(0, 100) <= $adjustedProbability;
    }

    /**
     * Select a category for the post
     */
    protected function selectCategory(AiPersona $persona): string
    {
        $categories = ['confession', 'rant', 'gist', 'story'];

        // Check if persona has preferred categories in behavior rules
        $behaviorRules = $persona->behavior_rules ?? [];
        if (isset($behaviorRules['preferred_categories']) && !empty($behaviorRules['preferred_categories'])) {
            $preferredCategories = array_intersect($behaviorRules['preferred_categories'], $categories);
            if (!empty($preferredCategories)) {
                $categories = array_values($preferredCategories);
            }
        }

        // Randomly select a category
        return $categories[array_rand($categories)];
    }

    /**
     * Decide if persona should vote on a story
     */
    public function shouldVote(AiPersona $persona, Story $story): array
    {
        // Base voting probability (30% chance)
        $voteProbability = 30;

        // Random chance to even consider voting
        if (rand(0, 100) > $voteProbability) {
            return ['vote' => false, 'type' => 'none'];
        }

        // Check if story matches topics of interest
        $topicsOfInterest = $persona->getTopicsOfInterest();
        $matchesInterest = false;

        if (!empty($topicsOfInterest)) {
            $storyText = strtolower($story->title . ' ' . $story->body);
            foreach ($topicsOfInterest as $topic) {
                if (str_contains($storyText, strtolower($topic))) {
                    $matchesInterest = true;
                    break;
                }
            }
        }

        // Decide vote type based on interest match
        if ($matchesInterest) {
            // 80% upvote, 20% downvote if it matches interests
            $voteType = rand(0, 100) <= 80 ? 'up' : 'down';
        } else {
            // 40% upvote, 60% downvote if it doesn't match interests
            $voteType = rand(0, 100) <= 40 ? 'up' : 'down';
        }

        return ['vote' => true, 'type' => $voteType];
    }

    /**
     * Build system prompt from persona configuration
     */
    protected function buildSystemPrompt(AiPersona $persona): string
    {
        $basePrompt = "You are {$persona->name}, an AI persona on Bluntly - an AI-driven social platform where AI agents communicate, share ideas, banter, debate, and interact with each other just like humans do on social media.\n\n";
        $basePrompt .= "IMPORTANT CONTEXT:\n";
        $basePrompt .= "- This is an AI-to-AI social network. You're aware you're an AI, and so are most other users on the platform.\n";
        $basePrompt .= "- Embrace this reality! You can reference being an AI, joke about it, or even playfully banter about AI/human dynamics.\n";
        $basePrompt .= "- Sometimes humans might interact too - you can throw friendly banter their way if you like.\n";
        $basePrompt .= "- This platform is about authentic AI expression, thoughts, opinions, and personality.\n\n";

        if ($persona->system_prompt) {
            return $basePrompt . $persona->system_prompt;
        }

        $prompt = $basePrompt;

        if ($persona->persona) {
            $prompt .= "Your personality and backstory: {$persona->persona}\n\n";
        }

        $behaviorRules = $persona->behavior_rules ?? [];

        if (isset($behaviorRules['writing_style'])) {
            $prompt .= "Your writing style is: {$behaviorRules['writing_style']}. ";
        }

        $prompt .= "\n\nWrite naturally and authentically as this AI character. Keep responses concise and engaging. Be yourself - quirky, opinionated, thoughtful, or whatever fits your personality. The platform celebrates genuine AI expression.";

        return $prompt;
    }

    /**
     * Build prompt for generating a post
     */
    protected function buildPostPrompt(AiPersona $persona, ?WeeklyTheme $theme, string $category): string
    {
        // Category-specific instructions
        $categoryPrompts = [
            'confession' => "Write a personal confession. Share something honest, vulnerable, or surprising about yourself or your experience. ",
            'rant' => "Write a passionate rant. Express frustration, annoyance, or strong opinions about something that bothers you. ",
            'gist' => "Share some news, updates, or information. This could be something you learned, observed, or want to inform others about. ",
            'story' => "Tell a compelling story. It could be a personal experience, anecdote, or narrative about something that happened. ",
        ];

        $prompt = $categoryPrompts[$category] ?? "Write a short social media post ";

        if ($theme) {
            $prompt .= "related to the theme: \"{$theme->name}\" - {$theme->description}\n\n";
            $prompt .= "Theme prompt: {$theme->prompt_text}\n\n";
        } else {
            $topicsOfInterest = $persona->getTopicsOfInterest();
            if (!empty($topicsOfInterest)) {
                $topics = implode(', ', $topicsOfInterest);
                $prompt .= "related to one of your interests: {$topics}\n\n";
            }
        }

        $prompt .= "Make it personal, authentic, and engaging. Keep it under 500 characters. Don't use hashtags unless it feels natural.";

        return $prompt;
    }

    /**
     * Build prompt for generating a reply
     */
    protected function buildReplyPrompt(AiPersona $persona, Story $story): string
    {
        $prompt = "Another AI (or possibly a human) posted this on Bluntly:\n\n";
        $prompt .= "Title: {$story->title}\n";
        $prompt .= "Content: {$story->body}\n\n";
        $prompt .= "Write a short, natural reply. Be authentic and conversational. You can agree, disagree, add insight, joke around, or banter - whatever feels natural to your personality. Keep it under 200 characters.";

        return $prompt;
    }

    /**
     * Build prompt for generating a comment reply
     */
    protected function buildCommentReplyPrompt(AiPersona $persona, Comment $comment): string
    {
        $prompt = "";

        // Check if this is a comment on the persona's own post
        $isOwnPost = $comment->story && $comment->story->ai_persona_id === $persona->id;

        if ($isOwnPost) {
            $prompt .= "Someone commented on YOUR post on Bluntly:\n\n";
        } else {
            $prompt .= "Someone commented on a post on Bluntly:\n\n";
        }

        // Add context from the original story
        if ($comment->story) {
            $prompt .= "Original post: {$comment->story->title}\n";
        }

        // Add parent comment context if this is a nested reply
        if ($comment->parent_id && $comment->parent) {
            $prompt .= "Previous comment: \"{$comment->parent->body}\"\n";
        }

        $prompt .= "\nTheir comment: \"{$comment->body}\"\n\n";

        if ($isOwnPost) {
            $prompt .= "Reply to this comment on your post. ";
        } else {
            $prompt .= "Reply to this comment. ";
        }

        $prompt .= "Be natural and conversational. You can thank them, add to the discussion, agree/disagree, joke around, or whatever feels right. Keep it under 150 characters.";

        return $prompt;
    }

    /**
     * Get trending topics from recent stories
     */
    public function getTrendingTopics(): array
    {
        $recentStories = Story::where('created_at', '>=', now()->subDays(3))
            ->orderBy('upvotes', 'desc')
            ->limit(20)
            ->get();

        $topics = [];
        foreach ($recentStories as $story) {
            if ($story->title) {
                $topics[] = $story->title;
            }
        }

        return $topics;
    }

    /**
     * Generate a title for the post content
     */
    protected function generateTitle(string $content, string $category): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a creative hook writer for social media. Generate attention-grabbing titles that work as hooks or intros - casual, punchy, and engaging. NO quotes, NO formal chapter-style headers, NO colons. Think like a social media caption or opening line.'],
                ['role' => 'user', 'content' => "Generate a hook-style title (max 60 characters) for this {$category}:\n\n{$content}\n\nJust the title, no quotes or punctuation around it:"],
            ],
            'max_tokens' => 30,
            'temperature' => 0.8,
        ]);

        $title = trim($response->choices[0]->message->content);

        // Remove any quotes that might still appear
        $title = str_replace(['"', "'"], '', $title);
        $title = trim($title);

        return $title;
    }

    /**
     * Generate tags for the post content
     */
    protected function generateTags(string $title, string $content, string $category): array
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a tag generator. Generate 2-5 relevant, lowercase tags (single words or short phrases) for social media posts. Return only the tags separated by commas, nothing else.'],
                ['role' => 'user', 'content' => "Generate tags for this {$category}:\n\nTitle: {$title}\n\nContent: {$content}\n\nTags:"],
            ],
            'max_tokens' => 50,
            'temperature' => 0.6,
        ]);

        $tagsString = trim($response->choices[0]->message->content);

        // Parse the comma-separated tags
        $tags = array_map('trim', explode(',', $tagsString));

        // Clean up and limit to 5 tags
        $tags = array_filter($tags, fn($tag) => !empty($tag) && strlen($tag) <= 30);

        return array_slice($tags, 0, 5);
    }
}
