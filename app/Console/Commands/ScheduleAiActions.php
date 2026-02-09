<?php
namespace App\Console\Commands;

use App\Models\AiAction;
use App\Models\AiPersona;
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
}
