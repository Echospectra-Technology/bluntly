<?php

namespace App\Services;

use App\Models\UserSession;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Request;

class AnonymousUserService
{
    private const COOKIE_NAME = 'bluntly_anonymous_id';
    private const COOKIE_LIFETIME = 525600; // 365 days
    
    private GeolocationService $geolocationService;

    public function __construct(GeolocationService $geolocationService)
    {
        $this->geolocationService = $geolocationService;
    }

    public function getAnonymousId(): string
    {
        $anonymousId = Cookie::get(self::COOKIE_NAME);
        
        if (!$anonymousId) {
            $anonymousId = $this->generateAnonymousId();
            $this->setAnonymousCookie($anonymousId);
            $this->initializeUserSession($anonymousId);
        } else {
            $this->updateUserActivity($anonymousId);
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
            
            // Track view interaction for personalization
            $this->trackInteraction($anonymousId, $storyId, 'view');
        }
    }

    private function initializeUserSession(string $anonymousId): void
    {
        $ipAddress = Request::ip();
        $location = $this->geolocationService->getLocationFromIP($ipAddress);

        UserSession::create([
            'cookie_hash' => $anonymousId,
            'country_code' => $location['country_code'],
            'country_name' => $location['country_name'],
            'state_code' => $location['state_code'],
            'state_name' => $location['state_name'],
            'city' => $location['city'],
            'region' => $location['region'],
            'ip_hash' => $location['ip_hash'],
            'latitude' => $location['latitude'],
            'longitude' => $location['longitude'],
            'last_activity' => now(),
        ]);
    }

    private function updateUserActivity(string $anonymousId): void
    {
        UserSession::where('cookie_hash', $anonymousId)
            ->update(['last_activity' => now()]);
    }

    public function getUserLocation(string $anonymousId): array
    {
        $session = UserSession::where('cookie_hash', $anonymousId)->first();
        
        if (!$session) {
            // Fallback: create session from current IP
            $this->initializeUserSession($anonymousId);
            $session = UserSession::where('cookie_hash', $anonymousId)->first();
        }

        return [
            'country_code' => $session->country_code,
            'country_name' => $session->country_name,
            'state_code' => $session->state_code,
            'state_name' => $session->state_name,
            'city' => $session->city,
            'region' => $session->region ?? 'global',
            'latitude' => $session->latitude,
            'longitude' => $session->longitude,
        ];
    }

    public function updateUserLocation(string $anonymousId, ?string $ipAddress = null): array
    {
        $ipAddress = $ipAddress ?: Request::ip();
        $location = $this->geolocationService->getLocationFromIP($ipAddress);

        UserSession::updateOrCreate(
            ['cookie_hash' => $anonymousId],
            [
                'country_code' => $location['country_code'],
                'country_name' => $location['country_name'],
                'state_code' => $location['state_code'],
                'state_name' => $location['state_name'],
                'city' => $location['city'],
                'region' => $location['region'],
                'ip_hash' => $location['ip_hash'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'last_activity' => now(),
            ]
        );

        return $location;
    }

    public function trackInteraction(string $anonymousId, int $storyId, string $interactionType): void
    {
        // Track the interaction for affinity learning
        app(AffinityTrackingService::class)->trackInteraction($anonymousId, $storyId, $interactionType);
    }

    public function hidePost(string $anonymousId, int $storyId, string $reason = 'not_interested'): void
    {
        app(PersonalizedFeedService::class)->hidePost($anonymousId, $storyId, $reason);
    }

    public function getUserPersonalizationData(string $anonymousId): array
    {
        $location = $this->getUserLocation($anonymousId);
        $affinityService = app(AffinityTrackingService::class);
        $feedService = app(PersonalizedFeedService::class);
        
        return [
            'location' => $location,
            'top_interests' => $affinityService->getUserTopTags($anonymousId),
            'feed_stats' => $feedService->getFeedStats($anonymousId),
        ];
    }
}