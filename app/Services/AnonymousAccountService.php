<?php
namespace App\Services;

use App\Models\AnonymousUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AnonymousAccountService
{
    private const COOKIE_NAME     = 'bluntly_anonymous_id';
    private const COOKIE_LIFETIME = 525600; // 365 days

    private GeolocationService $geolocationService;
    private AnonymousUserService $legacyService;

    public function __construct(
        GeolocationService $geolocationService,
        AnonymousUserService $legacyService
    ) {
        $this->geolocationService = $geolocationService;
        $this->legacyService      = $legacyService;
    }

    /**
     * Get current user identifier (supports both cookie and account users)
     */
    public function getCurrentUserIdentifier(): string
    {
        $user = $this->getCurrentUser();
        if ($user) {
            return 'user_' . $user->id;
        }

        // Fallback to cookie-based system
        return $this->legacyService->getAnonymousId();
    }

    /**
     * Get current anonymous user (if logged in)
     */
    public function getCurrentUser(): ?AnonymousUser
    {
        return Auth::guard('anonymous')->user();
    }

    /**
     * Register a new anonymous user
     */
    public function register(string $username, string $password, ?string $email = null): AnonymousUser
    {
        $user = AnonymousUser::create([
            'username'                => $username,
            'password_hash'           => Hash::make($password),
            'email'                   => $email,
            'cookie_hash'             => $this->legacyService->getAnonymousId(), // For migration
            'is_migrated_from_cookie' => true,
        ]);

        $this->migrateUserData($user);

        return $user;
    }

    /**
     * Login an anonymous user
     */
    public function login(string $username, string $password): ?AnonymousUser
    {
        $user = AnonymousUser::byUsername($username)->first();

        if ($user && $user->checkPassword($password)) {
            Auth::guard('anonymous')->login($user);
            $user->updateLastLogin();
            return $user;
        }

        return null;
    }

    /**
     * Logout current user
     */
    public function logout(): void
    {
        Auth::guard('anonymous')->logout();
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return Auth::guard('anonymous')->check();
    }

    /**
     * Migrate existing cookie-based data to user account
     */
    public function migrateUserData(AnonymousUser $user): void
    {
        $cookieHash = $user->cookie_hash;
        if (! $cookieHash) {
            return;
        }

        // Migrate stories
        DB::table('stories')
            ->where('cookie_hash', $cookieHash)
            ->update(['anonymous_user_id' => $user->id]);

        // Migrate votes
        DB::table('votes')
            ->where('cookie_hash', $cookieHash)
            ->update(['anonymous_user_id' => $user->id]);

        // Migrate views
        DB::table('views')
            ->where('cookie_hash', $cookieHash)
            ->update(['anonymous_user_id' => $user->id]);

        // Migrate comments
        DB::table('comments')
            ->where('cookie_hash', $cookieHash)
            ->update(['anonymous_user_id' => $user->id]);

        // Migrate reports
        DB::table('reports')
            ->where('cookie_hash', $cookieHash)
            ->update(['anonymous_user_id' => $user->id]);

        // Migrate user sessions
        DB::table('user_sessions')
            ->where('cookie_hash', $cookieHash)
            ->update(['anonymous_user_id' => $user->id]);

        // Migrate tag affinities
        DB::table('user_tag_affinities')
            ->where('cookie_hash', $cookieHash)
            ->update(['anonymous_user_id' => $user->id]);

        // Migrate hidden posts
        DB::table('user_hidden_posts')
            ->where('cookie_hash', $cookieHash)
            ->update(['anonymous_user_id' => $user->id]);
    }

    /**
     * Get user location (supports both cookie and account users)
     */
    public function getUserLocation(): array
    {
        $user = $this->getCurrentUser();
        if ($user) {
            $session = $user->session;
            if ($session) {
                return [
                    'country_code' => $session->country_code,
                    'country_name' => $session->country_name,
                    'state_code'   => $session->state_code,
                    'state_name'   => $session->state_name,
                    'city'         => $session->city,
                    'region'       => $session->region ?? 'global',
                    'latitude'     => $session->latitude,
                    'longitude'    => $session->longitude,
                ];
            }
        }

        // Fallback to legacy service
        return $this->legacyService->getUserLocation($this->legacyService->getAnonymousId());
    }

    /**
     * Track interaction (supports both cookie and account users)
     */
    public function trackInteraction(int $storyId, string $interactionType): void
    {
        $user = $this->getCurrentUser();
        if ($user) {
            app(AffinityTrackingService::class)->trackInteractionForUser($user->id, $storyId, $interactionType);
        } else {
            $this->legacyService->trackInteraction($this->legacyService->getAnonymousId(), $storyId, $interactionType);
        }
    }

    /**
     * Hide post (supports both cookie and account users)
     */
    public function hidePost(int $storyId, string $reason = 'not_interested'): void
    {
        $user = $this->getCurrentUser();
        if ($user) {
            // Create hidden post record for user
            DB::table('user_hidden_posts')->updateOrInsert(
                ['anonymous_user_id' => $user->id, 'story_id' => $storyId],
                [
                    'reason' => $reason,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } else {
            $this->legacyService->hidePost($this->legacyService->getAnonymousId(), $storyId, $reason);
        }
    }

    /**
     * Record story view (supports both cookie and account users)
     */
    public function recordStoryView(int $storyId): void
    {
        $user = $this->getCurrentUser();
        if ($user) {
            $hasViewed = \App\Models\StoryView::where('story_id', $storyId)
                ->where('anonymous_user_id', $user->id)
                ->exists();

            if (! $hasViewed) {
                \App\Models\StoryView::create([
                    'story_id'          => $storyId,
                    'anonymous_user_id' => $user->id,
                    'created_at'        => now(),
                ]);

                // Increment view count and track interaction
                \App\Models\Story::where('id', $storyId)->increment('views');
                $this->trackInteraction($storyId, 'view');
            }
        } else {
            $this->legacyService->recordStoryView($storyId);
        }
    }

    /**
     * Generate available username
     */
    public function generateUsername(): string
    {
        return AnonymousUser::generateUsername();
    }

    /**
     * Check if username is available
     */
    public function isUsernameAvailable(string $username): bool
    {
        return ! AnonymousUser::where('username', $username)->exists();
    }

    /**
     * Get user's top tags by user ID
     */
    private function getUserTopTagsById(int $userId, int $limit = 10): array
    {
        return DB::table('user_tag_affinities')
            ->join('tags', 'user_tag_affinities.tag_id', '=', 'tags.id')
            ->where('user_tag_affinities.anonymous_user_id', $userId)
            ->where('user_tag_affinities.affinity_score', '>', 0)
            ->orderBy('user_tag_affinities.affinity_score', 'desc')
            ->limit($limit)
            ->pluck('tags.name')
            ->toArray();
    }

    /**
     * Get user's personalization data
     */
    public function getPersonalizationData(): array
    {
        $user = $this->getCurrentUser();
        if ($user) {
            // Get user stats from database
            $storyCount = DB::table('stories')->where('anonymous_user_id', $user->id)->count();
            $voteCount = DB::table('votes')->where('anonymous_user_id', $user->id)->count();
            $commentCount = DB::table('comments')->where('anonymous_user_id', $user->id)->count();

            return [
                'username'      => $user->username,
                'is_logged_in'  => true,
                'location'      => $this->getUserLocation(),
                'top_interests' => $this->getUserTopTagsById($user->id),
                'feed_stats'    => [
                    'stories_posted' => $storyCount,
                    'votes_cast' => $voteCount,
                    'comments_made' => $commentCount,
                    'total_activity' => $storyCount + $voteCount + $commentCount,
                ],
            ];
        }

        // Fallback to legacy service
        return array_merge(
            $this->legacyService->getUserPersonalizationData($this->legacyService->getAnonymousId()),
            ['is_logged_in' => false]
        );
    }

    /**
     * Prompt existing cookie users to create account
     */
    public function shouldPromptAccountCreation(): bool
    {
        if ($this->isLoggedIn()) {
            return false;
        }

        // Check if user has significant activity
        $cookieHash = $this->legacyService->getAnonymousId();

        $storyCount   = DB::table('stories')->where('cookie_hash', $cookieHash)->count();
        $voteCount    = DB::table('votes')->where('cookie_hash', $cookieHash)->count();
        $commentCount = DB::table('comments')->where('cookie_hash', $cookieHash)->count();

        $totalActivity = $storyCount + $voteCount + $commentCount;

        // Prompt if user has 5+ activities and no account
        return $totalActivity >= 5;
    }
}
