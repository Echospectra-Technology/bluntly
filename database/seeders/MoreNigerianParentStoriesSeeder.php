<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Story;
use Illuminate\Support\Str;

class MoreNigerianParentStoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stories = [
            [
                'title' => '"Go and bring my slippers" - A 10-meter journey that could cripple you',
                'body' => '<p>Me: *sitting comfortably watching TV*<br>Dad: Go and bring my slippers from the room</p><p>The slippers are literally 10 meters away. I am 20 years old. I have a degree. But somehow I cannot ignore this command.</p><p>If I dare say "Dad, you\'re closer" the response is always: "Are your legs broken? Since when do you talk back to me?"</p><p>Then I have to get up, walk to the room, bring the slippers, and somehow feel like I\'ve been taught a valuable lesson about respect. Nigerian parents will have you fetching slippers until you\'re 40.</p><p>The same man will ask me to change the TV channel when the remote is right next to him. But that\'s different because "I\'m the elder here."</p>',
                'category' => 'rant',
                'alias' => 'slipperFetcher',
                'upvotes' => 28,
                'downvotes' => 1,
                'views' => 201,
            ],
            [
                'title' => 'My mum said light food will make me weak',
                'body' => '<p>"Eat rice! This your salad and fruit will make you weak like white people!" - My mother, every time I try to eat healthy.</p><p>According to my mum, any food that\'s not rice, yam, or beans is "light food" that will turn me into a weakling. I tried explaining that vegetables have nutrients but she said "Have you seen any strong Yoruba man eating grass?"</p><p>She genuinely believes that carbs = strength and anything green = European nonsense. When I told her I was trying to lose weight, she asked "Why? You want to look like those skinny white girls in magazines?"</p><p>Now I eat salad in secret like I\'m doing something shameful. The irony is she complains about my dad\'s big belly but still loads his plate with enough rice to feed a small village.</p>',
                'category' => 'confession',
                'alias' => 'secretSaladEater',
                'upvotes' => 24,
                'downvotes' => 2,
                'views' => 167,
            ],
            [
                'title' => '"Who is calling you at this time?" - 7PM is apparently midnight',
                'body' => '<p>My phone rings at 7PM.</p><p>Mum: "Who is calling you at this time? What kind of call is this?"</p><p>It\'s literally 7 in the evening. The sun just set. But according to Nigerian parent logic, any call after 6PM is suspicious and probably from a cult or a yahoo boy trying to steal my destiny.</p><p>Even when I explain it\'s my course mate asking about assignment, she\'s like "Why can\'t they call in the afternoon? This night call is not good."</p><p>The same woman will answer her church member\'s call at 11PM and talk for 2 hours about sister Mary\'s wedding planning. But my 7PM call is "this night call nonsense."</p><p>Nigerian parents think any social activity after sunset is the beginning of moral decay.</p>',
                'category' => 'gist',
                'alias' => 'nightCallSuspect',
                'upvotes' => 22,
                'downvotes' => 1,
                'views' => 134,
            ],
            [
                'title' => 'My dad said I should marry "that nice boy from church" - I\'m 19',
                'body' => '<p>I mentioned that Michael from youth fellowship said hi to me after service.</p><p>Dad: "That boy is well-behaved. You should marry him."</p><p>Father, I am 19 years old. Michael and I have exchanged exactly 3 words: "Hi," "Hello," and "Amen." But somehow my dad has already planned our traditional wedding and counted our future children.</p><p>When I said I want to finish university first, he said "What is university when you have a good husband? You think all this book will cook for you?"</p><p>The same man who always talks about education being the key to success suddenly forgets all that when he spots a potential son-in-law. Now every conversation ends with "That Michael boy, when are you bringing him home to greet us properly?"</p><p>Nigerian parents can arrange your entire future based on one church greeting.</p>',
                'category' => 'rant',
                'alias' => 'churchWifeMaterial',
                'upvotes' => 35,
                'downvotes' => 0,
                'views' => 289,
            ],
            [
                'title' => '"Don\'t touch anything in the parlor" but guests can sit wherever',
                'body' => '<p>Our sitting room (the "parlor") is like a museum. Perfect cushions covered in plastic, decorative fruits that have been there since 2015, and a center table that\'s never been used.</p><p>"Don\'t touch anything in the parlor! That place is for visitors!"</p><p>But we ARE the people who live here? How are we not allowed in our own sitting room?</p><p>Then pastor comes to visit and suddenly: "Go and call them to sit in the parlor!" The same parlor that was forbidden 5 minutes ago is now perfectly fine for outsiders.</p><p>We have a whole room in our house that we can\'t use. It\'s like having a car that you can only look at but never drive. Meanwhile, we\'re cramped in the family room watching TV on a bench.</p><p>Nigerian homes have VIP sections that the actual residents can\'t access. Make it make sense!</p>',
                'category' => 'confession',
                'alias' => 'parlorPrisoner',
                'upvotes' => 31,
                'downvotes' => 1,
                'views' => 245,
            ],
            [
                'title' => 'My mum said video games will make me mad like those American children',
                'body' => '<p>"Turn off that thing! Do you want to become mad like those American children who shoot schools?"</p><p>I was playing FIFA. It\'s literally just football on a screen. But according to my mum, any video game is a direct path to violence and insanity.</p><p>She saw a news report about violence in America and somehow concluded that PlayStation is the root of all evil. Now I\'m not allowed to play anything because "those games will put demons in your head."</p><p>The same woman will watch 6 hours of Nollywood movies featuring gunfights, rituals, and actual violence, but my football game is the dangerous one.</p><p>I tried to explain that millions of people play games without becoming violent, but she said "You think you\'re smarter than me? I have seen what those things do to children."</p><p>Nigerian parents will blame video games for everything except the actual problems.</p>',
                'category' => 'gist',
                'alias' => 'fifaVillain',
                'upvotes' => 26,
                'downvotes' => 2,
                'views' => 178,
            ],
            [
                'title' => '"You must greet everyone" but I don\'t know half these people',
                'body' => '<p>Family gathering = 2 hours of greeting people I\'ve never seen in my life.</p><p>Mum: "Go and greet Uncle Tunde!"<br>Me: "Who is Uncle Tunde?"<br>Mum: "Your father\'s cousin\'s friend from the village. Show some respect!"</p><p>Apparently I\'m supposed to prostrate for every adult present, including people who literally just met me today. Then I have to do the full "Good afternoon sir/ma" routine while they inspect me like a science project.</p><p>"Ah! You\'ve grown so much! Last time I saw you, you were this small!" *gestures to the ground*</p><p>Sir, you have never seen me in your life. You\'re thinking of someone else\'s child. But I still have to smile and say "Thank you sir" because Nigerian respect culture doesn\'t allow for truth.</p><p>I spend more time greeting strangers at family events than actually enjoying the party.</p>',
                'category' => 'rant',
                'alias' => 'professionalGreeter',
                'upvotes' => 29,
                'downvotes' => 1,
                'views' => 212,
            ],
        ];

        // Get the current "My Nigerian Parent Once Said..." theme
        $theme = \App\Models\WeeklyTheme::where('name', 'My Nigerian Parent Once Said...')->first();

        foreach ($stories as $storyData) {
            Story::create([
                'title' => $storyData['title'],
                'body' => $storyData['body'],
                'slug' => Str::slug($storyData['title']),
                'alias' => $storyData['alias'],
                'cookie_hash' => 'seeder_' . Str::random(32),
                'status' => 'published',
                'category' => $storyData['category'],
                'theme_id' => $theme ? $theme->id : null,
                'upvotes' => $storyData['upvotes'],
                'downvotes' => $storyData['downvotes'],
                'views' => $storyData['views'],
                'created_at' => now()->subDays(rand(1, 5)),
                'updated_at' => now()->subDays(rand(1, 5)),
            ]);
        }
    }
}