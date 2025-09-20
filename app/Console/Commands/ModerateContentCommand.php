<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ModerationEngine;
use App\Models\Story;
use App\Models\Comment;
use Illuminate\Support\Facades\Log;

class ModerateContentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moderate:content 
                           {--batch-size=50 : Number of items to process per batch}
                           {--type=all : Type of content to moderate (stories, comments, all)}
                           {--force : Re-moderate already moderated content}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moderate content using the automated moderation engine';

    protected ModerationEngine $moderationEngine;

    public function __construct(ModerationEngine $moderationEngine)
    {
        parent::__construct();
        $this->moderationEngine = $moderationEngine;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = (int) $this->option('batch-size');
        $type = $this->option('type');
        $force = $this->option('force');

        $this->info('Starting content moderation...');

        $totalProcessed = 0;
        $totalFlagged = 0;

        if ($type === 'stories' || $type === 'all') {
            [$processed, $flagged] = $this->moderateStories($batchSize, $force);
            $totalProcessed += $processed;
            $totalFlagged += $flagged;
        }

        if ($type === 'comments' || $type === 'all') {
            [$processed, $flagged] = $this->moderateComments($batchSize, $force);
            $totalProcessed += $processed;
            $totalFlagged += $flagged;
        }

        $this->info("Moderation completed!");
        $this->info("Total processed: {$totalProcessed}");
        $this->info("Total flagged: {$totalFlagged}");

        Log::info('Content moderation completed', [
            'total_processed' => $totalProcessed,
            'total_flagged' => $totalFlagged,
            'type' => $type,
            'batch_size' => $batchSize,
            'force' => $force
        ]);

        return Command::SUCCESS;
    }

    private function moderateStories(int $batchSize, bool $force): array
    {
        $processed = 0;
        $flagged = 0;

        $query = Story::query();
        
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('moderated_at')
                  ->orWhere('moderated_at', '<', now()->subHours(24));
            });
        }

        $this->info('Processing stories...');

        $query->chunkById($batchSize, function ($stories) use (&$processed, &$flagged) {
            foreach ($stories as $story) {
                try {
                    $result = $this->moderationEngine->moderateStory($story);
                    
                    if ($result['status'] !== 'safe') {
                        $flagged++;
                        $this->warn("Story {$story->id} flagged: {$result['status']} (score: {$result['score']})");
                    } else {
                        $this->line("Story {$story->id}: safe (score: {$result['score']})");
                    }
                    
                    $processed++;
                } catch (\Exception $e) {
                    $this->error("Error moderating story {$story->id}: " . $e->getMessage());
                    Log::error('Error moderating story', [
                        'story_id' => $story->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        return [$processed, $flagged];
    }

    private function moderateComments(int $batchSize, bool $force): array
    {
        $processed = 0;
        $flagged = 0;

        $query = Comment::query();
        
        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('moderated_at')
                  ->orWhere('moderated_at', '<', now()->subHours(24));
            });
        }

        $this->info('Processing comments...');

        $query->chunkById($batchSize, function ($comments) use (&$processed, &$flagged) {
            foreach ($comments as $comment) {
                try {
                    $result = $this->moderationEngine->moderateComment($comment);
                    
                    if ($result['status'] !== 'safe') {
                        $flagged++;
                        $this->warn("Comment {$comment->id} flagged: {$result['status']} (score: {$result['score']})");
                    } else {
                        $this->line("Comment {$comment->id}: safe (score: {$result['score']})");
                    }
                    
                    $processed++;
                } catch (\Exception $e) {
                    $this->error("Error moderating comment {$comment->id}: " . $e->getMessage());
                    Log::error('Error moderating comment', [
                        'comment_id' => $comment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        return [$processed, $flagged];
    }
}
