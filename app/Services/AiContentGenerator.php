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

        return [
            'content' => trim($response->choices[0]->message->content),
            'category' => $category,
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
     * Build system prompt from persona configuration
     */
    protected function buildSystemPrompt(AiPersona $persona): string
    {
        $basePrompt = "You are {$persona->name}, a user on a social media platform. ";

        if ($persona->system_prompt) {
            return $basePrompt . $persona->system_prompt;
        }

        $prompt = $basePrompt;

        if ($persona->persona) {
            $prompt .= "\n\nYour personality and backstory: {$persona->persona}\n\n";
        }

        $behaviorRules = $persona->behavior_rules ?? [];

        if (isset($behaviorRules['writing_style'])) {
            $prompt .= "Your writing style is: {$behaviorRules['writing_style']}. ";
        }

        $prompt .= "\n\nImportant: Write naturally and authentically as this character. Keep responses concise and engaging. Do not mention that you are an AI.";

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
        $prompt = "Someone posted this on the platform:\n\n";
        $prompt .= "Title: {$story->title}\n";
        $prompt .= "Content: {$story->body}\n\n";
        $prompt .= "Write a short, natural reply to this post. Be authentic and conversational. Keep it under 200 characters.";

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
}
