<?php
namespace App\Console\Commands;

use App\Models\AiAction;
use App\Models\AiPersona;
use App\Models\Comment;
use App\Models\Story;
use App\Services\AiContentGenerator;
use Illuminate\Console\Command;

class ScheduleAiActions extends Command
{
    protected $signature   = 'ai:schedule-actions';
    protected $description = 'Schedule AI persona actions (posts and replies)';

    protected AiContentGenerator $aiGenerator;

    public function __construct(AiContentGenerator $aiGenerator)
    {
        parent::__construct();
        $this->aiGenerator = $aiGenerator;
    }

    public function handle()
    {
        $this->info('Scheduling AI persona actions...');

        $activePersonas = AiPersona::active()->get();

        if ($activePersonas->isEmpty()) {
            $this->info('No active personas found.');
            return 0;
        }

        $scheduled = 0;

        foreach ($activePersonas as $persona) {
            // Schedule posts
            if ($this->shouldSchedulePost($persona)) {
                $this->schedulePost($persona);
                $scheduled++;
            }

            // Schedule replies
            $repliesScheduled  = $this->scheduleReplies($persona);
            $scheduled        += $repliesScheduled;

            // Schedule votes
            $votesScheduled = $this->scheduleVotes($persona);
            $scheduled     += $votesScheduled;

            // Schedule comment replies
            $commentRepliesScheduled = $this->scheduleCommentReplies($persona);
            $scheduled              += $commentRepliesScheduled;
        }

        $this->info("Scheduled {$scheduled} actions.");
        return 0;
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

        if (! $lastAction) {
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

        // Schedule within next 30 minutes
        $scheduledAt = now()->addMinutes(rand(1, 30));

        AiAction::create([
            'ai_persona_id' => $persona->id,
            'action_type'   => 'post',
            'target_type'   => null,
            'target_id'     => null,
            'status'        => 'scheduled',
            // 'scheduled_at' => $scheduledAt,
            'scheduled_at'  => now(),
        ]);

        $this->info("Scheduled post for {$persona->name} at {$scheduledAt->format('H:i')}");
    }

    protected function scheduleReplies(AiPersona $persona): int
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
            if ($this->aiGenerator->shouldReply($persona, $story)) {
                // Schedule reply within next hour
                $scheduledAt = now()->addMinutes(rand(5, 60));

                AiAction::create([
                    'ai_persona_id' => $persona->id,
                    'action_type'   => 'reply',
                    'target_type'   => Story::class,
                    'target_id'     => $story->id,
                    'status'        => 'scheduled',
                    'scheduled_at'  => $scheduledAt,
                ]);

                $scheduled++;
                $this->info("Scheduled reply from {$persona->name} to story #{$story->id}");

                // Limit replies per run
                if ($scheduled >= 2) {
                    break;
                }
            }
        }

        return $scheduled;
    }

    protected function scheduleVotes(AiPersona $persona): int
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
            $voteDecision = $this->aiGenerator->shouldVote($persona, $story);

            if ($voteDecision['vote']) {
                // Schedule vote within next 2 hours
                $scheduledAt = now()->addMinutes(rand(10, 120));

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
                $this->info("Scheduled {$voteDecision['type']}vote from {$persona->name} to story #{$story->id}");

                // Limit votes per run
                if ($scheduled >= 5) {
                    break;
                }
            }
        }

        return $scheduled;
    }

    protected function scheduleCommentReplies(AiPersona $persona): int
    {
        $scheduled = 0;

        // Get recent comments from the last 48 hours
        // Include comments on own posts AND comments on other posts
        $recentComments = Comment::where('created_at', '>=', now()->subHours(48))
            ->where('status', 'published')
            ->where('ai_persona_id', '!=', $persona->id) // Not own comments
            ->with(['story', 'parent']) // Load relationships for context
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
            if ($this->aiGenerator->shouldReplyToComment($persona, $comment)) {
                // Schedule reply within next 3 hours
                $scheduledAt = now()->addMinutes(rand(15, 180));

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
                $this->info("Scheduled comment reply from {$persona->name} on {$postType}");

                // Limit comment replies per run
                if ($scheduled >= 3) {
                    break;
                }
            }
        }

        return $scheduled;
    }
}
