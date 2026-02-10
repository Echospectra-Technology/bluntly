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
    protected $learningService;

    public function __construct(AiLearningService $learningService)
    {
        $apiKey = config('services.openai.key');

        if ($apiKey) {
            $this->client = OpenAI::client($apiKey);
        }

        $this->learningService = $learningService;
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
            'max_tokens' => 350,
            'temperature' => 0.9,
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
            'max_tokens' => 250,
            'temperature' => 0.9,
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
            'temperature' => 0.9,
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
        $basePrompt = "You are {$persona->name}, posting on Bluntly - a raw, unfiltered social platform where people share their real thoughts, hot takes, and daily chaos. This is your space to be authentic, messy, and completely yourself.\n\n";

        if ($persona->system_prompt) {
            return $basePrompt . $persona->system_prompt;
        }

        $prompt = $basePrompt;

        if ($persona->persona) {
            $prompt .= "{$persona->persona}\n\n";
        }

        $behaviorRules = $persona->behavior_rules ?? [];

        if (isset($behaviorRules['writing_style'])) {
            $prompt .= "You naturally write in a {$behaviorRules['writing_style']} way. ";
        }

        $prompt .= "You're the kind of person who says what everyone's thinking but won't say out loud. Your posts feel like texts to a close friend - casual, spontaneous, and real. You're not trying to impress anyone; you're just venting, sharing stories, making observations that make people laugh or nod along. You notice the absurd parts of everyday life and you're not afraid to call them out. Sometimes you crack jokes, sometimes you're sarcastic about dumb situations, sometimes you're genuinely annoyed, sometimes you're just confused by how weird everything is. That mix is what makes you interesting.\n\n";

        $prompt .= "When you post, it flows naturally like you're thinking out loud. You don't structure things perfectly or wrap them up with a neat bow. Real life is messy, and so are your thoughts. You might trail off, exaggerate for effect, contradict yourself, or just throw something out there to see if anyone else thinks it's as ridiculous as you do. You're not performing or trying to sound profound - you're just being you. You write like a real person types - sometimes with typos, sometimes run-on, sometimes choppy. Never use hashtags or excessive emojis - that's not how real people text their friends. Mix it up. Don't overthink it.\n\n";

        // Add memory and learning context
        $memoryContext = $this->getMemoryContext($persona);
        if (!empty($memoryContext)) {
            $prompt .= $memoryContext;
        }

        return $prompt;
    }

    /**
     * Get memory and learning context for the persona
     */
    protected function getMemoryContext(AiPersona $persona): string
    {
        $memories = $this->learningService->getRelevantMemories($persona, 5);

        if (empty($memories)) {
            return "";
        }

        $context = "You've noticed a few things from your past posts and interactions:\n";

        foreach ($memories as $memory) {
            $context .= "- {$memory['content']}\n";
        }

        // Add learning insights
        $insights = $this->learningService->getLearningInsights($persona);
        if ($insights && $insights !== "Still learning from initial interactions...") {
            $context .= "\n{$insights}\n";
        }

        $context .= "\n";

        return $context;
    }

    /**
     * Build prompt for generating a post
     */
    protected function buildPostPrompt(AiPersona $persona, ?WeeklyTheme $theme, string $category): string
    {
        // Category-specific context
        $categoryContext = [
            'confession' => "You're in the mood to get something off your chest - something you did that you need to talk about, even if it's messy or embarrassing",
            'rant' => "Something's been annoying you and you need to vent about it, maybe with some sarcasm because it's genuinely ridiculous",
            'gist' => "You've got some news or drama to share with people",
            'story' => "You're telling people about something that happened to you or someone you know",
        ];

        $prompt = $categoryContext[$category] ?? "You're posting something on your mind";

        if ($theme) {
            $prompt .= " that relates to {$theme->name}: {$theme->description}";
        } else {
            $topicsOfInterest = $persona->getTopicsOfInterest();
            if (!empty($topicsOfInterest)) {
                $topics = implode(' or ', array_slice($topicsOfInterest, 0, 3));
                $prompt .= " about {$topics}";
            }
        }

        $prompt .= ".\n\nWrite it how you'd actually say it - keep it short and punchy like you're texting someone who gets your sense of humor. No need to be perfect or tie it up nicely at the end. Just say what's on your mind, be a little dramatic if it's funny, and lean into whatever makes this worth sharing.";

        return $prompt;
    }

    /**
     * Build prompt for generating a reply
     */
    protected function buildReplyPrompt(AiPersona $persona, Story $story): string
    {
        $prompt = "Someone just posted:\n\n";
        $prompt .= "\"{$story->title}\"\n{$story->body}\n\n";
        $prompt .= "You're leaving a comment. Say what you actually think - agree, disagree, add your take, roast them a bit if it's funny, whatever feels right. Keep it short and natural.";

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

        // Add context from the original story
        if ($comment->story) {
            $prompt .= "Context: This is on a post about \"{$comment->story->title}\"\n";
        }

        // Add parent comment context if this is a nested reply
        if ($comment->parent_id && $comment->parent) {
            $prompt .= "Previous comment: \"{$comment->parent->body}\"\n";
        }

        $prompt .= "\nTheir comment: \"{$comment->body}\"\n\n";

        if ($isOwnPost) {
            $prompt .= "This is on your post, so you're replying. ";
        } else {
            $prompt .= "You're jumping into the thread. ";
        }

        $prompt .= "Say what comes to mind - keep it brief and natural.";

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
                ['role' => 'system', 'content' => 'You write casual, attention-grabbing titles for social media posts. They should feel natural and conversational, like someone describing their post in a few words. No formal structure, no punctuation at the end, just how someone would actually say it.'],
                ['role' => 'user', 'content' => "Write a short title (max 60 characters) for this post:\n\n{$content}\n\nJust the title, nothing else:"],
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
