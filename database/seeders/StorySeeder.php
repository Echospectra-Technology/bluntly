<?php

namespace Database\Seeders;

use App\Models\Story;
use App\Models\Tag;
use App\Models\Comment;
use App\Models\Vote;
use App\Models\StoryView;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StorySeeder extends Seeder
{
    public function run(): void
    {
        $stories = [
            [
                'title' => "I've been lying to my family about my job for two years",
                'body' => "Everyone thinks I'm a successful marketing manager at a tech company, making six figures and climbing the corporate ladder. In reality, I work at a coffee shop downtown, making barely above minimum wage. I lost my real job two years ago and have been too ashamed to tell anyone. The weight of this lie is crushing me, but I'm in too deep now. Every family gathering is torture, pretending everything is fine while I'm drowning in debt and shame.",
                'alias' => 'midnightowl',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['WorkLife', 'Family', 'Career'],
                'upvotes' => 234,
                'downvotes' => 12,
                'views' => 1200,
            ],
            [
                'title' => "Why does everyone pretend social media is real life?",
                'body' => "I'm so tired of the fake happiness, the staged photos, the pretend perfect lives. When did we all agree to this collective lie? I scroll through Instagram and see my friends posting about their 'amazing' lives while I know half of them are struggling with depression, debt, or relationship issues. Yet here I am, also posting fake smiles and carefully curated moments. We're all participating in this massive delusion and I hate it.",
                'alias' => 'quietstorm',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'rant',
                'tags' => ['SocialMedia', 'MentalHealth', 'Authenticity'],
                'upvotes' => 567,
                'downvotes' => 43,
                'views' => 3800,
            ],
            [
                'title' => "The day I accidentally became a local hero",
                'body' => "All I did was help an old lady with her groceries when she dropped them outside the supermarket. But somehow, that simple act spiraled into the whole neighborhood thinking I'm some kind of saint. Now everyone waves at me, brings me baked goods, and treats me like I saved someone's life. It's sweet but also overwhelming. I'm just a regular person who helped someone up. The bar for being a 'hero' is apparently very low these days.",
                'alias' => 'silvervoice',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'gist',
                'tags' => ['Community', 'Kindness', 'Unexpected'],
                'upvotes' => 189,
                'downvotes' => 45,
                'views' => 892,
            ],
            [
                'title' => "I found my childhood diary and it changed everything",
                'body' => "While cleaning out my parents' attic, I discovered my diary from when I was 12. Reading my own words from 20 years ago was like meeting a stranger who shared my face. That little kid had dreams I'd completely forgotten about, fears that seemed silly now, and a perspective on life that was so pure and hopeful. It made me realize how much I've let cynicism and 'being realistic' crush my spirit. I'm trying to reconnect with that optimistic kid I used to be.",
                'alias' => 'urbanwanderer',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'story',
                'tags' => ['Nostalgia', 'Childhood', 'SelfReflection'],
                'upvotes' => 445,
                'downvotes' => 67,
                'views' => 2100,
            ],
            [
                'title' => "I've been eating lunch alone in my car for six months",
                'body' => "Started a new job six months ago and still haven't made a single friend. While everyone else goes to lunch together, I drive to a quiet parking lot and eat my sandwich alone in my car. I tell myself it's peaceful, but honestly, it's the loneliest part of my day. I've gotten good at pretending I have somewhere important to be, but really I just don't want them to see how pathetic I am eating alone at my desk.",
                'alias' => 'nightthoughts',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['WorkLife', 'Loneliness', 'NewJob'],
                'upvotes' => 892,
                'downvotes' => 234,
                'views' => 5200,
            ],
        ];

        foreach ($stories as $storyData) {
            $tags = $storyData['tags'];
            unset($storyData['tags']);

            // Generate slug from title and keep alias as username
            $storyData['slug'] = Str::slug($storyData['title']);

            $story = Story::create($storyData);

            // Attach tags
            $tagIds = Tag::whereIn('name', $tags)->pluck('id');
            $story->tags()->attach($tagIds);

            // Create some votes
            for ($i = 0; $i < $storyData['upvotes']; $i++) {
                Vote::create([
                    'item_type' => 'story',
                    'item_id' => $story->id,
                    'value' => 'up',
                    'cookie_hash' => Str::random(32),
                    'created_at' => now()->subHours(rand(1, 48)),
                ]);
            }

            for ($i = 0; $i < $storyData['downvotes']; $i++) {
                Vote::create([
                    'item_type' => 'story',
                    'item_id' => $story->id,
                    'value' => 'down',
                    'cookie_hash' => Str::random(32),
                    'created_at' => now()->subHours(rand(1, 48)),
                ]);
            }

            // Create some views
            for ($i = 0; $i < $storyData['views']; $i++) {
                StoryView::create([
                    'story_id' => $story->id,
                    'cookie_hash' => Str::random(32),
                    'created_at' => now()->subHours(rand(1, 72)),
                ]);
            }

            // Create some comments
            $commentCount = rand(5, 15);
            for ($i = 0; $i < $commentCount; $i++) {
                $comment = Comment::create([
                    'story_id' => $story->id,
                    'parent_id' => null,
                    'body' => $this->getRandomComment(),
                    'alias' => $this->getRandomAlias(),
                    'cookie_hash' => Str::random(32),
                    'status' => 'published',
                    'upvotes' => rand(0, 50),
                    'downvotes' => rand(0, 10),
                    'created_at' => now()->subHours(rand(1, 48)),
                ]);

                // Sometimes add replies
                if (rand(1, 3) === 1) {
                    Comment::create([
                        'story_id' => $story->id,
                        'parent_id' => $comment->id,
                        'body' => $this->getRandomReply(),
                        'alias' => $this->getRandomAlias(),
                        'cookie_hash' => Str::random(32),
                        'status' => 'published',
                        'upvotes' => rand(0, 20),
                        'downvotes' => rand(0, 5),
                        'created_at' => now()->subHours(rand(1, 24)),
                    ]);
                }
            }
        }
    }

    private function getRandomComment(): string
    {
        $comments = [
            "I relate to this so much. Thank you for sharing.",
            "This hits different. Been there myself.",
            "You're not alone in feeling this way.",
            "Thanks for being so honest about this.",
            "I needed to read this today.",
            "This is exactly what I've been going through.",
            "You put into words what I couldn't express.",
            "Sending you virtual hugs. This too shall pass.",
            "Your courage to share this is inspiring.",
            "I see you and I hear you. You matter.",
        ];

        return $comments[array_rand($comments)];
    }

    private function getRandomReply(): string
    {
        $replies = [
            "Exactly! You get it.",
            "Thank you for understanding.",
            "This means a lot to me.",
            "I'm glad I'm not the only one.",
            "Your words really help.",
            "Same here, friend.",
            "Appreciate you taking the time to respond.",
            "This community is amazing.",
            "Feeling less alone now.",
            "Thank you for the support.",
        ];

        return $replies[array_rand($replies)];
    }

    private function getRandomAlias(): string
    {
        $adjectives = ['quiet', 'midnight', 'silver', 'deep', 'honest', 'working', 'night', 'urban', 'compassionate', 'truthful', 'gentle', 'brave', 'hopeful', 'wise', 'caring'];
        $nouns = ['voice', 'owl', 'storm', 'thoughts', 'soul', 'person', 'wanderer', 'heart', 'spirit', 'seeker', 'friend', 'dreamer', 'warrior', 'listener', 'helper'];

        return $adjectives[array_rand($adjectives)] . $nouns[array_rand($nouns)];
    }
}