<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class AnonymousUserService
{
    private const COOKIE_NAME = 'bluntly_anonymous_id';
    private const COOKIE_LIFETIME = 525600; // 365 days

    public function getAnonymousId(): string
    {
        $anonymousId = Cookie::get(self::COOKIE_NAME);
        
        if (!$anonymousId) {
            $anonymousId = $this->generateAnonymousId();
            $this->setAnonymousCookie($anonymousId);
        }
        
        return $anonymousId;
    }

    public function setAnonymousCookie(string $anonymousId): void
    {
        Cookie::queue(self::COOKIE_NAME, $anonymousId, self::COOKIE_LIFETIME);
    }

    public function generateAnonymousId(): string
    {
        return Str::random(32);
    }

    public function generateAlias(): string
    {
        $adjectives = [
            'quiet', 'midnight', 'silver', 'deep', 'honest', 'working', 'night', 
            'urban', 'compassionate', 'truthful', 'gentle', 'brave', 'hopeful', 
            'wise', 'caring', 'peaceful', 'thoughtful', 'kind', 'resilient', 'curious'
        ];
        
        $nouns = [
            'voice', 'owl', 'storm', 'thoughts', 'soul', 'person', 'wanderer', 
            'heart', 'spirit', 'seeker', 'friend', 'dreamer', 'warrior', 'listener', 
            'helper', 'traveler', 'writer', 'observer', 'thinker', 'storyteller'
        ];

        return $adjectives[array_rand($adjectives)] . $nouns[array_rand($nouns)];
    }

    public function hasVotedOn(string $itemType, int $itemId): ?string
    {
        $anonymousId = $this->getAnonymousId();
        
        $vote = \App\Models\Vote::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('cookie_hash', $anonymousId)
            ->first();
            
        return $vote ? $vote->value : null;
    }

    public function hasReportedItem(string $itemType, int $itemId): bool
    {
        $anonymousId = $this->getAnonymousId();
        
        return \App\Models\Report::where('item_type', $itemType)
            ->where('item_id', $itemId)
            ->where('cookie_hash', $anonymousId)
            ->exists();
    }

    public function hasViewedStory(int $storyId): bool
    {
        $anonymousId = $this->getAnonymousId();
        
        return \App\Models\StoryView::where('story_id', $storyId)
            ->where('cookie_hash', $anonymousId)
            ->exists();
    }

    public function recordStoryView(int $storyId): void
    {
        $anonymousId = $this->getAnonymousId();
        
        if (!$this->hasViewedStory($storyId)) {
            \App\Models\StoryView::create([
                'story_id' => $storyId,
                'cookie_hash' => $anonymousId,
                'created_at' => now(),
            ]);
            
            // Increment view count on story
            \App\Models\Story::where('id', $storyId)->increment('views');
        }
    }
}