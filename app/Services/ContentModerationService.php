<?php

namespace App\Services;

use App\Models\Story;
use App\Models\Comment;
use App\Models\FlaggedItem;
use Illuminate\Support\Str;

class ContentModerationService
{
    private const SPAM_KEYWORDS = [
        'click here', 'buy now', 'free money', 'make money fast', 'lose weight fast',
        'viagra', 'casino', 'lottery', 'winner', 'congratulations you won'
    ];

    private const HARMFUL_PATTERNS = [
        '/suicide|kill myself|end it all/i',
        '/self harm|cutting|hurting myself/i',
        '/bomb|explosive|terrorist/i',
        '/nazi|hitler|genocide/i',
    ];

    public function sanitizeContent(string $content): string
    {
        // Remove potentially harmful HTML tags
        $content = strip_tags($content, '<p><br><strong><em><u><ol><ul><li><blockquote><h1><h2><h3>');
        
        // Convert special characters
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8', false);
        
        // Remove excessive whitespace
        $content = preg_replace('/\s+/', ' ', trim($content));
        
        return $content;
    }

    public function checkForSpam(string $content): bool
    {
        $content = strtolower($content);
        
        foreach (self::SPAM_KEYWORDS as $keyword) {
            if (strpos($content, $keyword) !== false) {
                return true;
            }
        }

        // Check for excessive links
        $linkCount = preg_match_all('/https?:\/\//', $content);
        if ($linkCount > 3) {
            return true;
        }

        // Check for excessive repetition
        if (preg_match('/(.{10,})\1{3,}/', $content)) {
            return true;
        }

        return false;
    }

    public function checkForHarmfulContent(string $content): bool
    {
        foreach (self::HARMFUL_PATTERNS as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    public function moderateStory(Story $story): void
    {
        $score = 0;
        $reasons = [];

        // Check for spam
        if ($this->checkForSpam($story->body) || $this->checkForSpam($story->title)) {
            $score += 50;
            $reasons[] = 'Potential spam content detected';
        }

        // Check for harmful content
        if ($this->checkForHarmfulContent($story->body) || $this->checkForHarmfulContent($story->title)) {
            $score += 80;
            $reasons[] = 'Potentially harmful content detected';
        }

        // Check downvote ratio
        $totalVotes = $story->upvotes + $story->downvotes;
        if ($totalVotes > 10) {
            $downvoteRatio = $story->downvotes / $totalVotes;
            if ($downvoteRatio > 0.7) {
                $score += 30;
                $reasons[] = 'High downvote ratio';
            }
        }

        // Flag if score exceeds threshold
        if ($score >= 70) {
            $this->flagItem('story', $story->id, 'auto_moderation', $score, implode(', ', $reasons));
            
            // Hide severely flagged content
            if ($score >= 100) {
                $story->update(['status' => 'hidden']);
            }
        }
    }

    public function moderateComment(Comment $comment): void
    {
        $score = 0;
        $reasons = [];

        if ($this->checkForSpam($comment->body)) {
            $score += 60;
            $reasons[] = 'Potential spam content detected';
        }

        if ($this->checkForHarmfulContent($comment->body)) {
            $score += 90;
            $reasons[] = 'Potentially harmful content detected';
        }

        if ($score >= 70) {
            $this->flagItem('comment', $comment->id, 'auto_moderation', $score, implode(', ', $reasons));
            
            if ($score >= 100) {
                $comment->update(['status' => 'hidden']);
            }
        }
    }

    private function flagItem(string $itemType, int $itemId, string $reason, int $score, string $details): void
    {
        FlaggedItem::updateOrCreate([
            'item_type' => $itemType,
            'item_id' => $itemId,
        ], [
            'flag_reason' => $reason,
            'score' => $score,
            'status' => 'pending',
        ]);
    }

    public function processReports(string $itemType, int $itemId): void
    {
        $reportCount = \App\Models\Report::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->count();

        if ($reportCount >= 3) {
            $this->flagItem($itemType, $itemId, 'reports', $reportCount * 10, "Received {$reportCount} reports");
            
            // Auto-hide heavily reported content
            if ($reportCount >= 10) {
                if ($itemType === 'story') {
                    Story::where('id', $itemId)->update(['status' => 'hidden']);
                } else {
                    Comment::where('id', $itemId)->update(['status' => 'hidden']);
                }
            }
        }
    }
}