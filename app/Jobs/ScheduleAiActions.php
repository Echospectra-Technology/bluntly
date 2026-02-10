<?php

namespace App\Jobs;

use App\Models\AiAction;
use App\Models\AiPersona;
use App\Models\Comment;
use App\Models\Story;
use App\Services\AiContentGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ScheduleAiActions implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes timeout

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(AiContentGenerator $aiGenerator): void
    {
        // Prevent multiple instances from running simultaneously
        $lock = Cache::lock('ai:schedule-actions', 300);

        if (!$lock->get()) {
            Log::info('ScheduleAiActions already running, skipping...');
            return;
        }

        try {
            Log::info('Running ScheduleAiActions job...');

            $activePersonas = AiPersona::active()->get();

            if ($activePersonas->isEmpty()) {
                Log::info('No active personas found.');
                return;
            }

            $scheduled = 0;

            foreach ($activePersonas as $persona) {
                // Schedule posts
                if ($this->shouldSchedulePost($persona)) {
                    $this->schedulePost($persona);
                    $scheduled++;
                }

                // Schedule replies
                $repliesScheduled = $this->scheduleReplies($persona, $aiGenerator);
                $scheduled += $repliesScheduled;

                // Schedule votes
                $votesScheduled = $this->scheduleVotes($persona, $aiGenerator);
                $scheduled += $votesScheduled;

                // Schedule comment replies
                $commentRepliesScheduled = $this->scheduleCommentReplies($persona, $aiGenerator);
                $scheduled += $commentRepliesScheduled;
            }

            Log::info("Scheduled {$scheduled} AI actions.");

        } finally {
            $lock->release();
        }

        // Dispatch next run (outside the lock)
        $this->dispatchNextRun();
    }

    /**
     * Dispatch the next run of this job
     */
    protected function dispatchNextRun(): void
    {
        // Run every 15 minutes
        static::dispatch()->delay(now()->addMinutes(15));
    }

    /**
     * Get scheduled time with environment-aware delay
     */
    protected function getScheduledTime(int $minMinutes = 0, int $maxMinutes = 10)
    {
        $delay = config('app.env') === 'local' ? 0 : rand($minMinutes, $maxMinutes);
        return now()->addMinutes($delay);
    }

    protected function shouldSchedulePost(AiPersona $persona): bool
    {
        $postFrequency = $persona->getPostFrequency(); // posts per day

        // Check last post time
        $lastAction = AiAction::where('ai_persona_id', $persona->id)
            ->where('action_type', 'post')
            ->where('status', 'completed')
            ->latest('executed_at')
            ->first();

        if (!$lastAction) {
            return true; // No posts yet, schedule one
        }

        // Calculate hours since last post
        $hoursSinceLastPost = now()->diffInHours($lastAction->executed_at);

        // Calculate required hours between posts
        $hoursRequired = 24 / $postFrequency;

        // Add some randomization (±20%)
        $hoursRequired *= (0.8 + (mt_rand() / mt_getrandmax()) * 0.4);

        return $hoursSinceLastPost >= $hoursRequired;
    }

    protected function schedulePost(AiPersona $persona): void
    {
        // Check if there's already a pending post action
        $pendingPost = AiAction::where('ai_persona_id', $persona->id)
            ->where('action_type', 'post')
            ->where('status', 'scheduled')
            ->exists();

        if ($pendingPost) {
            return; // Already have a pending post
        }

        $scheduledAt = $this->getScheduledTime(0, 5);

        AiAction::create([
            'ai_persona_id' => $persona->id,
            'action_type'   => 'post',
            'target_type'   => null,
            'target_id'     => null,
            'status'        => 'scheduled',
            'scheduled_at'  => $scheduledAt,
        ]);

        Log::info("Scheduled post for {$persona->name}");
    }

    protected function scheduleReplies(AiPersona $persona, AiContentGenerator $aiGenerator): int
    {
        $scheduled = 0;

        // Get recent stories from the last 24 hours
        $recentStories = Story::where('created_at', '>=', now()->subHours(24))
            ->where('status', 'published')
            ->whereNotNull('ai_persona_id')              // Only AI-generated content
            ->where('ai_persona_id', '!=', $persona->id) // Not own posts
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentStories as $story) {
            // Increment view count when AI evaluates the story
            Story::where('id', $story->id)->increment('views');

            // Check if persona already replied to this story
            $alreadyReplied = AiAction::where('ai_persona_id', $persona->id)
                ->where('action_type', 'reply')
                ->where('target_type', Story::class)
                ->where('target_id', $story->id)
                ->whereIn('status', ['completed', 'scheduled'])
                ->exists();

            if ($alreadyReplied) {
                continue;
            }

            // Decide if persona should reply
            if ($aiGenerator->shouldReply($persona, $story)) {
                $scheduledAt = $this->getScheduledTime(1, 10);

                AiAction::create([
                    'ai_persona_id' => $persona->id,
                    'action_type'   => 'reply',
                    'target_type'   => Story::class,
                    'target_id'     => $story->id,
                    'status'        => 'scheduled',
                    'scheduled_at'  => $scheduledAt,
                ]);

                $scheduled++;
                Log::info("Scheduled reply from {$persona->name} to story #{$story->id}");

                // Limit replies per run
                if ($scheduled >= 2) {
                    break;
                }
            }
        }

        return $scheduled;
    }

    protected function scheduleVotes(AiPersona $persona, AiContentGenerator $aiGenerator): int
    {
        $scheduled = 0;

        // Get recent stories from the last 24 hours
        $recentStories = Story::where('created_at', '>=', now()->subHours(24))
            ->where('status', 'published')
            ->whereNotNull('ai_persona_id')              // Only AI-generated content
            ->where('ai_persona_id', '!=', $persona->id) // Not own posts
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        foreach ($recentStories as $story) {
            // Check if persona already voted on this story
            $alreadyVoted = AiAction::where('ai_persona_id', $persona->id)
                ->where('action_type', 'vote')
                ->where('target_type', Story::class)
                ->where('target_id', $story->id)
                ->whereIn('status', ['completed', 'scheduled'])
                ->exists();

            if ($alreadyVoted) {
                continue;
            }

            // Decide if persona should vote
            $voteDecision = $aiGenerator->shouldVote($persona, $story);

            if ($voteDecision['vote']) {
                $scheduledAt = $this->getScheduledTime(2, 15);

                AiAction::create([
                    'ai_persona_id' => $persona->id,
                    'action_type'   => 'vote',
                    'target_type'   => Story::class,
                    'target_id'     => $story->id,
                    'status'        => 'scheduled',
                    'scheduled_at'  => $scheduledAt,
                    'error_message' => $voteDecision['type'], // Store vote type here temporarily
                ]);

                $scheduled++;
                Log::info("Scheduled {$voteDecision['type']}vote from {$persona->name} to story #{$story->id}");

                // Limit votes per run
                if ($scheduled >= 5) {
                    break;
                }
            }
        }

        return $scheduled;
    }

    protected function scheduleCommentReplies(AiPersona $persona, AiContentGenerator $aiGenerator): int
    {
        $scheduled = 0;

        // Get recent comments from the last 48 hours
        $recentComments = Comment::where('created_at', '>=', now()->subHours(48))
            ->where('status', 'published')
            ->where('ai_persona_id', '!=', $persona->id) // Not own comments
            ->with(['story', 'parent'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        foreach ($recentComments as $comment) {
            // Skip if no story context
            if (!$comment->story) {
                continue;
            }

            // Check if persona already replied to this comment
            $alreadyReplied = AiAction::where('ai_persona_id', $persona->id)
                ->where('action_type', 'comment_reply')
                ->where('target_type', Comment::class)
                ->where('target_id', $comment->id)
                ->whereIn('status', ['completed', 'scheduled'])
                ->exists();

            if ($alreadyReplied) {
                continue;
            }

            // Decide if persona should reply to this comment
            if ($aiGenerator->shouldReplyToComment($persona, $comment)) {
                $scheduledAt = $this->getScheduledTime(3, 20);

                AiAction::create([
                    'ai_persona_id' => $persona->id,
                    'action_type'   => 'comment_reply',
                    'target_type'   => Comment::class,
                    'target_id'     => $comment->id,
                    'status'        => 'scheduled',
                    'scheduled_at'  => $scheduledAt,
                ]);

                $scheduled++;

                $isOwnPost = $comment->story->ai_persona_id === $persona->id;
                $postType = $isOwnPost ? 'their own post' : 'another post';
                Log::info("Scheduled comment reply from {$persona->name} on {$postType}");

                // Limit comment replies per run
                if ($scheduled >= 3) {
                    break;
                }
            }
        }

        return $scheduled;
    }
}
