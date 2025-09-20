<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestFeedAlgorithm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feed:test 
                           {cookie_hash? : Test with specific cookie hash}
                           {--limit=10 : Number of stories to return}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the personalized feed algorithm';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cookieHash = $this->argument('cookie_hash') ?: 'test_user_' . time();
        $limit = (int) $this->option('limit');

        $this->info("Testing feed algorithm for user: {$cookieHash}");

        // Initialize services
        $geolocationService = app(\App\Services\GeolocationService::class);
        $affinityService = app(\App\Services\AffinityTrackingService::class);
        $feedService = app(\App\Services\PersonalizedFeedService::class);
        $anonymousService = app(\App\Services\AnonymousUserService::class);

        // Get/create user location
        $userLocation = $anonymousService->getUserLocation($cookieHash);
        
        $this->info("User location: {$userLocation['region']} ({$userLocation['country_name']})");

        // Get user affinities
        $affinities = $affinityService->getUserTagAffinities($cookieHash, 10);
        $this->info("User has " . count($affinities) . " tag affinities");
        
        if (!empty($affinities)) {
            $this->table(
                ['Tag', 'Score', 'Interactions'],
                array_map(function($tagId, $data) {
                    return [$data['name'], round($data['score'], 2), $data['interactions']];
                }, array_keys($affinities), $affinities)
            );
        }

        // Get personalized feed
        $this->info("Generating personalized feed...");
        $stories = $feedService->getPersonalizedFeed($cookieHash, $userLocation, $limit);

        $this->info("Generated {$stories->count()} stories");

        // Display results
        $tableData = [];
        foreach ($stories as $story) {
            $tableData[] = [
                $story->id,
                substr($story->title, 0, 50) . '...',
                $story->category ?? 'none',
                $story->region ?? 'global',
                $story->upvotes - $story->downvotes,
                $story->views,
                $story->computed_feed_score ?? 'N/A'
            ];
        }

        $this->table(
            ['ID', 'Title', 'Category', 'Region', 'Net Votes', 'Views', 'Feed Score'],
            $tableData
        );

        return Command::SUCCESS;
    }
}
