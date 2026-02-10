<?php

namespace App\Jobs;

use App\Models\Story;
use App\Models\Comment;
use App\Services\AiLearningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LearnFromEngagement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $contentType;
    public $contentId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $contentType, int $contentId)
    {
        $this->contentType = $contentType;
        $this->contentId = $contentId;
    }

    /**
     * Execute the job.
     */
    public function handle(AiLearningService $learningService): void
    {
        try {
            if ($this->contentType === 'story') {
                $story = Story::find($this->contentId);
                if ($story) {
                    $learningService->analyzeStoryEngagement($story);
                    Log::info("Learning job completed for story {$this->contentId}");
                }
            } elseif ($this->contentType === 'comment') {
                $comment = Comment::find($this->contentId);
                if ($comment) {
                    $learningService->analyzeCommentEngagement($comment);
                    Log::info("Learning job completed for comment {$this->contentId}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Learning job failed: " . $e->getMessage());
            throw $e;
        }
    }
}
