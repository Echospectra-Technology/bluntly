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
                'title' => "My family still thinks I'm studying abroad in Canada",
                'body' => "Three years ago I told them I got a scholarship to study in Canada. In reality, I'm in Lagos working multiple jobs just to survive. I send fake graduation photos I find online and pretend to be this successful international student. The pressure to 'make it' as the first child is suffocating. How do I tell them their golden child is just another struggling graduate in traffic every morning?",
                'alias' => 'firstson',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['Family', 'Education', 'Pressure'],
                'upvotes' => 456,
                'downvotes' => 23,
                'views' => 2800,
            ],
            [
                'title' => "Nigerian parents and their obsession with marriage",
                'body' => "I'm 28 and every family gathering is 'When are you bringing someone home?' 'Your mate is getting married.' 'We want to carry grandchildren.' Meanwhile, I'm trying to figure out how to pay rent and they think my biggest problem is being single. Can we please normalize being unmarried past 25 without it being a family emergency?",
                'alias' => 'singlebyChoice',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'rant',
                'tags' => ['Marriage', 'Family', 'Society'],
                'upvotes' => 789,
                'downvotes' => 134,
                'views' => 4200,
            ],
            [
                'title' => "The day I accidentally became a bus conductor",
                'body' => "Was rushing to catch a danfo when the conductor jumped down at Berger. Driver looked at me and said 'Oya, help us call passengers.' Before I knew it, I was hanging off the bus shouting 'Ikeja! Ikeja! One more passenger!' for thirty minutes. Made 200 naira that day and realized I'm not too proud to hustle. Sometimes life puts you exactly where you need to be.",
                'alias' => 'hustlerspirit',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'gist',
                'tags' => ['Hustle', 'Lagos', 'Unexpected'],
                'upvotes' => 342,
                'downvotes' => 45,
                'views' => 1650,
            ],
            [
                'title' => "Why do we pretend jollof rice competition is just fun?",
                'body' => "Ghanaians say they have the best jollof. Senegalese claim they invented it. Meanwhile we Nigerians are here producing the actual best jollof but getting dragged into these silly arguments. It's just rice, right? Wrong. This is about national pride, cultural identity, and who your mama taught you to be. Some battles are worth fighting.",
                'alias' => 'jollofdefender',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'rant',
                'tags' => ['Food', 'Culture', 'Pride'],
                'upvotes' => 567,
                'downvotes' => 89,
                'views' => 3100,
            ],
            [
                'title' => "I learned Yoruba at 25 because I was tired of being lost",
                'body' => "Grew up speaking only English because my parents wanted me to be 'international.' Now I sit in meetings with colleagues switching between Yoruba and English and I'm completely lost. Started learning from YouTube and language apps. My pronunciation is terrible but I'm determined. Your culture shouldn't be a stranger to you in your own country.",
                'alias' => 'culturaljourney',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['Culture', 'Language', 'Identity'],
                'upvotes' => 234,
                'downvotes' => 12,
                'views' => 1890,
            ],
            [
                'title' => "Generator fuel is the real tax on being Nigerian",
                'body' => "Spent 15k on fuel this month just to have electricity. NEPA takes light, I buy fuel. They bring light at 2am when I'm sleeping, take it again at 6am when I need to work. This country will humble you in ways you never imagined. We're all just trying to charge our phones and live like human beings but even that feels like a luxury.",
                'alias' => 'powerstruggle',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'rant',
                'tags' => ['Nigeria', 'Electricity', 'Struggle'],
                'upvotes' => 923,
                'downvotes' => 45,
                'views' => 5600,
            ],
            [
                'title' => "My village thinks I'm rich because I live in Lagos",
                'body' => "Every time I visit home, relatives line up with requests. 'You're in Lagos, money is there.' Meanwhile I'm sharing a room with three people in Mushin, eating bread and tea for dinner. The assumption that living in Lagos equals wealth is killing us. I love my family but I can barely take care of myself, let alone sponsor everyone's dreams.",
                'alias' => 'lagosreality',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['Family', 'Money', 'Expectations'],
                'upvotes' => 667,
                'downvotes' => 78,
                'views' => 3400,
            ],
            [
                'title' => "The okada man who changed my perspective on life",
                'body' => "Was having the worst day when this okada rider started talking about his daughter who just got into university. He's riding bike in Lagos traffic every day, saving every naira for her education. No complaints, just pure determination. Here I was feeling sorry for myself over a bad job interview. Some people carry the weight of dreams on their shoulders and still smile.",
                'alias' => 'humbled',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'gist',
                'tags' => ['Inspiration', 'Perspective', 'Lagos'],
                'upvotes' => 445,
                'downvotes' => 23,
                'views' => 2100,
            ],
            [
                'title' => "Why Nigerian movies always have the same plot?",
                'body' => "Rich man's son falls for poor girl. Family rejects her. She gets pregnant. He travels abroad. She suffers. His mother becomes evil. Plot twist: she was rich all along. They reunite. The end. Nollywood, please, we need new stories. Our lives are complex and beautiful in so many other ways. Give us something fresh abeg.",
                'alias' => 'nollywoodcritic',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'rant',
                'tags' => ['Movies', 'Culture', 'Entertainment'],
                'upvotes' => 356,
                'downvotes' => 67,
                'views' => 2300,
            ],
            [
                'title' => "I paid 500 naira for pure water that was 50 naira yesterday",
                'body' => "Went to an event in Victoria Island and almost cried at the water prices. Same pure water I buy for 50 naira in my area was 500 naira. Asked the vendor why, he said 'Location, location, location.' I was so thirsty I paid it but felt scammed. Lagos will teach you that your money's value depends on which side of town you're standing on.",
                'alias' => 'pricecheck',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'gist',
                'tags' => ['Lagos', 'Money', 'Reality'],
                'upvotes' => 234,
                'downvotes' => 34,
                'views' => 1560,
            ],
            [
                'title' => "My neighbor plays loud music and I've given up",
                'body' => "Every night from 8pm to 2am, it's like having a club next door. I've complained, begged, even tried to be friends with them. Nothing works. Now I just wear earphones and pretend I live alone. Sometimes you have to choose your battles and protect your peace. This is Lagos, everyone is fighting something.",
                'alias' => 'peaceseekers',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['Neighbors', 'Lagos', 'Peace'],
                'upvotes' => 189,
                'downvotes' => 23,
                'views' => 980,
            ],
            [
                'title' => "Why do job interviews here feel like interrogations?",
                'body' => "'Where do you see yourself in 5 years?' Sir, I see myself being able to afford three meals a day and paying rent without borrowing. 'What's your greatest weakness?' Hunger, sir. Physical hunger because I need this job to eat. Can we please normalize honest conversations about why we work? It's not always passion, sometimes it's survival.",
                'alias' => 'jobhunter',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'rant',
                'tags' => ['Jobs', 'Interview', 'Reality'],
                'upvotes' => 823,
                'downvotes' => 56,
                'views' => 4500,
            ],
            [
                'title' => "I cried the day I finally sent money home",
                'body' => "After two years of struggling in Abuja, I finally sent 20,000 naira to my mother. It wasn't much but it was everything I could spare. She called me crying, said she was proud of me. For the first time since leaving home, I felt like I was becoming the child they raised me to be. Small victories count too.",
                'alias' => 'proudchild',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['Family', 'Money', 'Growth'],
                'upvotes' => 456,
                'downvotes' => 12,
                'views' => 2700,
            ],
            [
                'title' => "The politics in my office WhatsApp group is exhausting",
                'body' => "Every morning it's either 'Good morning, have a blessed day' messages or heated arguments about government policies. I just want to know when the meeting starts, not read Uncle Emeka's 10-paragraph analysis of the economy. Why can't work groups stick to work? Now I'm the bad guy for asking people to take political discussions to another group.",
                'alias' => 'workplacedrama',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'rant',
                'tags' => ['Work', 'Politics', 'WhatsApp'],
                'upvotes' => 567,
                'downvotes' => 89,
                'views' => 3200,
            ],
            [
                'title' => "I learned to cook because restaurants were bleeding me dry",
                'body' => "Was spending 2,000 naira daily on food until I realized I could feed myself for a week with that money. Started with indomie experiments, graduated to real meals. Now I can make jollof rice that actually tastes good and my bank account is thanking me. Sometimes financial pressure teaches you skills you never knew you needed.",
                'alias' => 'kitchennovice',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'gist',
                'tags' => ['Cooking', 'Money', 'Skills'],
                'upvotes' => 234,
                'downvotes' => 23,
                'views' => 1450,
            ],
            [
                'title' => "My biggest fear is becoming like the adults I used to pity",
                'body' => "Remember those adults who seemed so defeated by life? Who complained about everything but never seemed to try changing anything? I'm starting to see how it happens. Bills, disappointments, failed dreams - they pile up until fighting feels pointless. I refuse to give up but I understand now why some people do. Life is harder than teenage me ever imagined.",
                'alias' => 'stillhopeful',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['Life', 'Growth', 'Hope'],
                'upvotes' => 678,
                'downvotes' => 45,
                'views' => 3800,
            ],
            [
                'title' => "The day I realized I was becoming my mother",
                'body' => "Was scolding my younger brother over the phone about saving money and suddenly heard my mother's voice coming out of my mouth. Same tone, same words, same lecture. I used to roll my eyes when she said these things. Now I understand - she wasn't being difficult, she was trying to prepare us for real life. Becoming your parents isn't the curse we thought it was.",
                'alias' => 'mothersdaughter',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'gist',
                'tags' => ['Family', 'Growth', 'Wisdom'],
                'upvotes' => 345,
                'downvotes' => 34,
                'views' => 2100,
            ],
            [
                'title' => "Why does everyone assume I have money because I dress well?",
                'body' => "I buy clothes from Yaba market, iron them properly, and match them nicely. Suddenly everyone thinks I'm rich. 'Lend me money na, you have am.' Meanwhile this shirt cost 800 naira and I've been wearing the same three outfits for months. Looking good doesn't mean having money, it means having sense and creativity.",
                'alias' => 'budgetfashion',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'rant',
                'tags' => ['Fashion', 'Money', 'Perception'],
                'upvotes' => 445,
                'downvotes' => 67,
                'views' => 2500,
            ],
            [
                'title' => "I finally stood up to my toxic boss and it felt amazing",
                'body' => "For months he treated me like his personal slave. 'Get me water,' 'Clean my office,' 'Work overtime without pay.' Today he asked me to wash his car and I said no. He threatened to fire me and I said 'Go ahead.' Sometimes you have to value your dignity more than your paycheck. I'm job hunting but I'm sleeping peacefully.",
                'alias' => 'dignityintact',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['Work', 'Dignity', 'Courage'],
                'upvotes' => 789,
                'downvotes' => 34,
                'views' => 4200,
            ],
            [
                'title' => "The friendship I lost because of money still hurts",
                'body' => "My best friend asked to borrow 50,000 naira for her mother's hospital bill. I had the money but was saving for my rent. I said no and tried to explain but she never spoke to me again. Six months later, I see her posting expensive dinners on Instagram. Maybe she didn't really need the money, maybe she's hurt I didn't trust her. Either way, I lost a friend and gained a lesson about money and relationships.",
                'alias' => 'lonelyfriend',
                'cookie_hash' => Str::random(32),
                'status' => 'published',
                'category' => 'confession',
                'tags' => ['Friendship', 'Money', 'Trust'],
                'upvotes' => 567,
                'downvotes' => 123,
                'views' => 3400,
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