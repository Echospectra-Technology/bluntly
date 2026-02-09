<?php

namespace App\Jobs;

use App\Models\AiAction;
use App\Models\Comment;
use App\Models\Story;
use App\Models\Tag;
use App\Models\Vote;
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
            } elseif ($this->action->action_type === 'vote') {
                $this->handleVote($aiGenerator);
            } elseif ($this->action->action_type === 'comment_reply') {
                $this->handleCommentReply($aiGenerator);
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
            'title' => $postData['title'],
            'body' => $postData['content'],
            'slug' => Str::slug(Str::limit($postData['title'] ?? $postData['content'], 50)) . '-' . Str::random(8),
            'alias' => $persona->username,
            'cookie_hash' => null,
            'status' => 'published',
            'category' => $postData['category'],
            'theme_id' => $theme?->id,
        ]);

        // Attach tags to story
        if (!empty($postData['tags'])) {
            $tagIds = [];
            foreach ($postData['tags'] as $tagName) {
                // Find or create tag
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($tagName)],
                    ['name' => $tagName]
                );
                $tagIds[] = $tag->id;
            }
            // Attach tags to story
            $story->tags()->attach($tagIds);
        }

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

    protected function handleVote(AiContentGenerator $aiGenerator): void
    {
        $persona = $this->action->aiPersona;
        $target = $this->action->target;

        if (!$target) {
            throw new \Exception('Vote target not found');
        }

        // Get vote type from error_message field (temporary storage)
        $voteType = $this->action->error_message; // 'up' or 'down'

        if (!in_array($voteType, ['up', 'down'])) {
            throw new \Exception('Invalid vote type');
        }

        // Create vote record with AI persona identifier
        Vote::create([
            'item_type' => Story::class,
            'item_id' => $target->id,
            'value' => $voteType,
            'cookie_hash' => 'ai-persona-' . $persona->id, // Unique identifier for AI votes
            'created_at' => now(),
        ]);

        // Update story vote counts
        $upvotes = Vote::where('item_type', Story::class)
            ->where('item_id', $target->id)
            ->where('value', 'up')
            ->count();

        $downvotes = Vote::where('item_type', Story::class)
            ->where('item_id', $target->id)
            ->where('value', 'down')
            ->count();

        Story::where('id', $target->id)->update([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
        ]);

        // Update persona stats
        $persona->incrementVotesGiven();
    }

    protected function handleCommentReply(AiContentGenerator $aiGenerator): void
    {
        $persona = $this->action->aiPersona;
        $targetComment = $this->action->target;

        if (!$targetComment) {
            throw new \Exception('Comment reply target not found');
        }

        // Generate reply content
        $content = $aiGenerator->generateCommentReply($persona, $targetComment);

        // Create nested comment
        Comment::create([
            'story_id' => $targetComment->story_id,
            'ai_persona_id' => $persona->id,
            'parent_id' => $targetComment->id, // This makes it a nested reply
            'body' => $content,
            'alias' => $persona->username,
            'cookie_hash' => null,
            'status' => 'published',
        ]);

        // Update persona stats
        $persona->incrementReplyCount();
    }
}
