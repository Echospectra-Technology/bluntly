<?php

namespace App\Services;

use App\Models\Story;
use App\Models\Comment;
use App\Models\FlaggedItem;
use Illuminate\Support\Str;

class ModerationEngine
{
    private const PROFANITY_KEYWORDS = [
        'mild' => ['damn', 'hell', 'crap', 'piss'],
        'moderate' => ['shit', 'bitch', 'asshole', 'bastard'],
        'severe' => ['fuck', 'cunt', 'nigger', 'faggot', 'retard']
    ];

    private const HATE_KEYWORDS = [
        'racial' => ['nigger', 'chink', 'spic', 'kike', 'towelhead'],
        'homophobic' => ['faggot', 'dyke', 'homo', 'queer'],
        'religious' => ['terrorist', 'jihad', 'infidel'],
        'general' => ['nazi', 'hitler', 'genocide', 'ethnic cleansing']
    ];

    private const VIOLENCE_KEYWORDS = [
        'self_harm' => ['suicide', 'kill myself', 'end it all', 'self harm', 'cutting', 'hurting myself'],
        'threats' => ['kill you', 'murder', 'die', 'shoot you', 'stab', 'bomb'],
        'violence' => ['rape', 'assault', 'beat up', 'torture', 'mutilate', 'gore']
    ];

    private const DOXXING_PATTERNS = [
        'phone' => '/\b(?:\+?1[-.\s]?)?\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})\b/',
        'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        'address' => '/\b\d+\s+[A-Za-z\s]+(?:street|st|avenue|ave|road|rd|drive|dr|lane|ln|court|ct|circle|cir|way|blvd|boulevard)\b/i',
        'ssn' => '/\b\d{3}-?\d{2}-?\d{4}\b/',
        'credit_card' => '/\b(?:\d{4}[-\s]?){3}\d{4}\b/'
    ];

    private const SPAM_PATTERNS = [
        'excessive_links' => '/https?:\/\/[^\s]+/i',
        'duplicate_text' => '/(.{10,})\1{2,}/',
        'excessive_caps' => '/[A-Z]{10,}/',
        'excessive_punctuation' => '/[!?]{5,}/'
    ];

    private const SPAM_KEYWORDS = [
        'click here', 'buy now', 'free money', 'make money fast', 'lose weight fast',
        'viagra', 'casino', 'lottery', 'winner', 'congratulations you won',
        'limited time', 'act now', 'special offer', 'discount', 'promo code'
    ];

    public function evaluateContent(string $text, string $type = 'story'): array
    {
        $score = 0;
        $matchedRules = [];
        $details = [];

        // Check profanity and hate speech
        $profanityResult = $this->checkProfanity($text);
        if ($profanityResult['score'] > 0) {
            $score += $profanityResult['score'];
            $matchedRules[] = 'profanity';
            $details[] = $profanityResult['details'];
        }

        // Check violence and self-harm
        $violenceResult = $this->checkViolence($text);
        if ($violenceResult['score'] > 0) {
            $score += $violenceResult['score'];
            $matchedRules[] = 'violence';
            $details[] = $violenceResult['details'];
        }

        // Check for doxxing
        $doxxingResult = $this->checkDoxxing($text);
        if ($doxxingResult['score'] > 0) {
            $score += $doxxingResult['score'];
            $matchedRules[] = 'doxxing';
            $details[] = $doxxingResult['details'];
        }

        // Check for spam
        $spamResult = $this->checkSpam($text);
        if ($spamResult['score'] > 0) {
            $score += $spamResult['score'];
            $matchedRules[] = 'spam';
            $details[] = $spamResult['details'];
        }

        // Check for low quality content
        $qualityResult = $this->checkQuality($text);
        if ($qualityResult['score'] > 0) {
            $score += $qualityResult['score'];
            $matchedRules[] = 'low_quality';
            $details[] = $qualityResult['details'];
        }

        // Cap score at 100
        $score = min($score, 100);

        // Determine status based on score
        $status = $this->determineStatus($score);

        return [
            'score' => $score,
            'status' => $status,
            'matched_rules' => $matchedRules,
            'details' => $details
        ];
    }

    private function checkProfanity(string $text): array
    {
        $text = strtolower($text);
        $score = 0;
        $found = [];

        // Check mild profanity
        foreach (self::PROFANITY_KEYWORDS['mild'] as $word) {
            if (strpos($text, $word) !== false) {
                $score += 20;
                $found[] = "mild: {$word}";
            }
        }

        // Check moderate profanity
        foreach (self::PROFANITY_KEYWORDS['moderate'] as $word) {
            if (strpos($text, $word) !== false) {
                $score += 35;
                $found[] = "moderate: {$word}";
            }
        }

        // Check severe profanity
        foreach (self::PROFANITY_KEYWORDS['severe'] as $word) {
            if (strpos($text, $word) !== false) {
                $score += 50;
                $found[] = "severe: {$word}";
            }
        }

        // Check hate speech keywords
        foreach (self::HATE_KEYWORDS as $category => $words) {
            foreach ($words as $word) {
                if (strpos($text, $word) !== false) {
                    $score += 50;
                    $found[] = "hate speech ({$category}): {$word}";
                }
            }
        }

        return [
            'score' => $score,
            'details' => implode(', ', $found)
        ];
    }

    private function checkViolence(string $text): array
    {
        $text = strtolower($text);
        $score = 0;
        $found = [];

        foreach (self::VIOLENCE_KEYWORDS as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    $points = match($category) {
                        'self_harm' => 60,
                        'threats' => 50,
                        'violence' => 40,
                        default => 40
                    };
                    $score += $points;
                    $found[] = "{$category}: {$keyword}";
                }
            }
        }

        return [
            'score' => $score,
            'details' => implode(', ', $found)
        ];
    }

    private function checkDoxxing(string $text): array
    {
        $score = 0;
        $found = [];

        foreach (self::DOXXING_PATTERNS as $type => $pattern) {
            if (preg_match($pattern, $text)) {
                $score += 40;
                $found[] = "personal info: {$type}";
            }
        }

        return [
            'score' => $score,
            'details' => implode(', ', $found)
        ];
    }

    private function checkSpam(string $text): array
    {
        $text = strtolower($text);
        $score = 0;
        $found = [];

        // Check spam keywords
        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $score += 25;
                $found[] = "spam keyword: {$keyword}";
            }
        }

        // Check for excessive links
        $linkCount = preg_match_all(self::SPAM_PATTERNS['excessive_links'], $text);
        if ($linkCount > 2) {
            $score += 30;
            $found[] = "excessive links: {$linkCount}";
        }

        // Check for duplicate content patterns
        if (preg_match(self::SPAM_PATTERNS['duplicate_text'], $text)) {
            $score += 25;
            $found[] = "duplicate content pattern";
        }

        // Check for excessive caps
        if (preg_match(self::SPAM_PATTERNS['excessive_caps'], $text)) {
            $score += 15;
            $found[] = "excessive caps";
        }

        // Check for excessive punctuation
        if (preg_match(self::SPAM_PATTERNS['excessive_punctuation'], $text)) {
            $score += 10;
            $found[] = "excessive punctuation";
        }

        return [
            'score' => $score,
            'details' => implode(', ', $found)
        ];
    }

    private function checkQuality(string $text): array
    {
        $wordCount = str_word_count($text);
        $score = 0;
        $found = [];

        // Check for very short content
        if ($wordCount < 5) {
            $score += 10;
            $found[] = "too short: {$wordCount} words";
        }

        // Check for all caps (if most of content is caps)
        $capsPercentage = 0;
        if (strlen($text) > 0) {
            $capsCount = strlen(preg_replace('/[^A-Z]/', '', $text));
            $capsPercentage = ($capsCount / strlen($text)) * 100;
        }

        if ($capsPercentage > 70 && strlen($text) > 20) {
            $score += 15;
            $found[] = "mostly caps: {$capsPercentage}%";
        }

        return [
            'score' => $score,
            'details' => implode(', ', $found)
        ];
    }

    private function determineStatus(int $score): string
    {
        if ($score <= 30) {
            return 'safe';
        } elseif ($score <= 70) {
            return 'review';
        } else {
            return 'auto_blocked';
        }
    }

    public function moderateStory(Story $story): array
    {
        // Combine title and body for evaluation
        $content = $story->title . ' ' . $story->body;
        $result = $this->evaluateContent($content, 'story');

        // Update story based on moderation result
        $this->applyModerationResult($story, $result);

        return $result;
    }

    public function moderateComment(Comment $comment): array
    {
        $result = $this->evaluateContent($comment->body, 'comment');

        // Update comment based on moderation result
        $this->applyModerationResult($comment, $result);

        return $result;
    }

    private function applyModerationResult($model, array $result): void
    {
        $status = match($result['status']) {
            'safe' => 'published',
            'review' => 'published', // Published but flagged
            'auto_blocked' => 'hidden',
            default => 'published'
        };

        // Update the model
        $model->update([
            'status' => $status,
            'moderation_score' => $result['score'],
            'moderated_at' => now(),
            'matched_rules' => json_encode($result['matched_rules'])
        ]);

        // Create flagged item if needed
        if ($result['status'] !== 'safe') {
            $this->createFlaggedItem($model, $result);
        }
    }

    private function createFlaggedItem($model, array $result): void
    {
        $itemType = $model instanceof Story ? 'story' : 'comment';
        
        $flagStatus = match($result['status']) {
            'review' => 'pending',
            'auto_blocked' => 'review',
            default => 'pending'
        };

        FlaggedItem::updateOrCreate([
            'item_type' => $itemType,
            'item_id' => $model->id,
        ], [
            'flag_reason' => 'auto_moderation',
            'score' => $result['score'],
            'status' => $flagStatus,
        ]);
    }

    public function checkDuplicateContent(string $content, string $type = 'story'): bool
    {
        $contentHash = md5(trim(strtolower($content)));
        
        if ($type === 'story') {
            return Story::whereRaw('MD5(LOWER(TRIM(CONCAT(title, " ", body)))) = ?', [$contentHash])
                ->where('created_at', '>', now()->subDays(7))
                ->exists();
        } else {
            return Comment::whereRaw('MD5(LOWER(TRIM(body))) = ?', [$contentHash])
                ->where('created_at', '>', now()->subDays(7))
                ->exists();
        }
    }
}