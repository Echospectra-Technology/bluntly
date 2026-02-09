<?php

namespace App\Jobs;

use App\Models\AiAction;
use App\Models\Comment;
use App\Models\Story;
use App\Models\WeeklyTheme;
use App\Services\AiContentGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessAiAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AiAction $action;

    public function __construct(AiAction $action)
    {
        $this->action = $action;
    }

    public function handle(AiContentGenerator $aiGenerator): void
    {
        // Mark as processing
        $this->action->markAsProcessing();

        try {
            if ($this->action->action_type === 'post') {
                $this->handlePost($aiGenerator);
            } elseif ($this->action->action_type === 'reply') {
                $this->handleReply($aiGenerator);
            }

            $this->action->markAsCompleted();
        } catch (\Exception $e) {
            $this->action->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    protected function handlePost(AiContentGenerator $aiGenerator): void
    {
        $persona = $this->action->aiPersona;

        // Get current weekly theme (optional)
        $theme = WeeklyTheme::where('is_active', true)->first();

        // Generate post content and category
        $postData = $aiGenerator->generatePost($persona, $theme);

        // Create story
        $story = Story::create([
            'ai_persona_id' => $persona->id,
            'title' => null,
            'body' => $postData['content'],
            'slug' => Str::slug(Str::limit($postData['content'], 50)) . '-' . Str::random(8),
            'alias' => $persona->username,
            'cookie_hash' => null,
            'status' => 'published',
            'category' => $postData['category'],
            'theme_id' => $theme?->id,
        ]);

        // Update persona stats
        $persona->incrementPostCount();
    }

    protected function handleReply(AiContentGenerator $aiGenerator): void
    {
        $persona = $this->action->aiPersona;
        $target = $this->action->target;

        if (!$target) {
            throw new \Exception('Reply target not found');
        }

        // Generate reply content
        $content = $aiGenerator->generateReply($persona, $target);

        // Create comment
        Comment::create([
            'story_id' => $target->id,
            'ai_persona_id' => $persona->id,
            'parent_id' => null,
            'body' => $content,
            'alias' => $persona->username,
            'cookie_hash' => null,
            'status' => 'published',
        ]);

        // Update persona stats
        $persona->incrementReplyCount();
    }
}
