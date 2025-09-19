<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Story;
use Illuminate\Support\Str;

class NigerianParentStoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stories = [
            [
                'title' => 'My mum said I can\'t be hungry because there\'s rice at home',
                'body' => '<p>So I told my mum I was hungry while we were out shopping and she looked at me dead serious and said "How can you be hungry when there\'s rice at home?" Like mum, we\'re at the mall, the rice is 30 minutes away, and I haven\'t eaten since morning! 😂</p><p>Nigerian parents will really make you question the laws of physics. How does rice existing in my house automatically fill my stomach? But I knew better than to argue with that logic.</p><p>Anyone else\'s parents think hunger is just a state of mind that can be cured by remembering you have food at home?</p>',
                'category' => 'confession',
                'alias' => 'hungryChild',
                'upvotes' => 15,
                'downvotes' => 2,
                'views' => 89,
            ],
            [
                'title' => '"Money for what?" - My dad\'s response to everything',
                'body' => '<p>Me: Dad, I need money for textbooks<br>Dad: Money for what?</p><p>Me: Dad, can I get lunch money?<br>Dad: Money for what?</p><p>Me: Dad, the school is asking for PTA levy<br>Dad: Money for what?</p><p>It doesn\'t matter what I\'m asking for. My father\'s automatic response is always "Money for what?" even when I literally just explained what the money is for! Then he\'ll proceed to give me a 20-minute lecture about how when he was my age, they didn\'t need money for anything.</p><p>The funny thing is, he usually gives me the money after the lecture. But that "Money for what?" is mandatory. It\'s like a ritual.</p>',
                'category' => 'rant',
                'alias' => 'brokeStudent',
                'upvotes' => 23,
                'downvotes' => 1,
                'views' => 156,
            ],
            [
                'title' => 'My mum said AC will give me pneumonia',
                'body' => '<p>Lagos heat was literally melting me and I turned on the AC in my room. My mum walked in and immediately started shouting "Turn off that thing! Do you want to catch pneumonia?"</p><p>I tried to explain that AC doesn\'t cause pneumonia but she gave me that look that said "Are you trying to be smart with me?" So I just turned it off and continued melting in silence.</p><p>The same woman will enter her own room and blast the AC full power. But somehow when I use it, it becomes a death trap. Nigerian parent logic is undefeated.</p><p>Now I have to sneak AC like I\'m doing something illegal in my own house.</p>',
                'category' => 'confession',
                'alias' => 'hotAndSuffering',
                'upvotes' => 18,
                'downvotes' => 3,
                'views' => 92,
            ],
            [
                'title' => '"We have food at home" but the food at home was beans from 3 days ago',
                'body' => '<p>Family: Can we get KFC?<br>Mum: We have food at home</p><p>The food at home: Beans that has been in the pot since Monday. It\'s now Thursday. The beans has evolved. It has its own ecosystem. I think I saw a small civilization forming on top.</p><p>But according to mum, this prehistoric beans is perfectly fine because "food is food" and I should be grateful. Meanwhile, she\'ll go and buy fresh fish for herself but expect us to eat beans that could qualify for carbon dating.</p><p>The worst part? She\'ll taste it, make a face, add more pepper and declare it "perfect for you children."</p>',
                'category' => 'rant',
                'alias' => 'beansVictim',
                'upvotes' => 31,
                'downvotes' => 1,
                'views' => 203,
            ],
            [
                'title' => 'My dad said sleeping late will make me mad',
                'body' => '<p>"If you don\'t sleep early, you will run mad!" - My father, every time he sees me awake past 10pm.</p><p>I tried to explain that I\'m doing assignments, but he said "What assignment can\'t wait till morning?" I said it\'s due tomorrow morning. He said "Then you should have started earlier."</p><p>Now according to my dad, every person who stays up late is automatically on the path to madness. YouTube videos at night? Madness. Reading at night? Double madness. Using phone at night? Straight ticket to Yaba Left.</p><p>But the same man will stay up till 2am watching Nollywood movies and that\'s perfectly normal. Only his children are at risk of madness.</p>',
                'category' => 'gist',
                'alias' => 'nightOwl',
                'upvotes' => 19,
                'downvotes' => 2,
                'views' => 134,
            ],
            [
                'title' => '"Close that door, are we air conditioning the whole of Lagos?"',
                'body' => '<p>Every. Single. Time. I leave a room and forget to close the door, my mum will shout from wherever she is: "Close that door! Are we air conditioning the whole of Lagos?"</p><p>Even when the AC is not on. Even when it\'s just fan. Even when there\'s no electrical appliance in the room. The door must be closed because apparently we\'re in danger of cooling down the entire state of Lagos.</p><p>I think my mum genuinely believes that leaving doors open creates some kind of energy vacuum that sucks out all the cool air from our house directly into Lagos traffic.</p><p>Now I close doors even when I\'m just stepping out for 2 seconds because the lecture is not worth it.</p>',
                'category' => 'confession',
                'alias' => 'doorCloser',
                'upvotes' => 27,
                'downvotes' => 1,
                'views' => 178,
            ],
            [
                'title' => 'My mum said paracetamol cures everything',
                'body' => '<p>Headache? Paracetamol.<br>Stomachache? Paracetamol.<br>Broken heart? Paracetamol.<br>Existential crisis? You guessed it - Paracetamol.</p><p>I could come home with my leg hanging off and my mum would say "Take paracetamol, you\'ll be fine." It\'s like she genuinely believes paracetamol is some kind of magical healing potion.</p><p>The funny thing is, she\'ll buy the generic brand that costs 50 naira and insist it works better than any expensive medicine. "All these oyibo medicine na the same thing. Just take paracetamol."</p><p>To this day, I instinctively reach for paracetamol for any problem, even emotional ones. Nigerian parenting has programmed me.</p>',
                'category' => 'gist',
                'alias' => 'paracetamolChild',
                'upvotes' => 25,
                'downvotes' => 2,
                'views' => 167,
            ],
            [
                'title' => '"If you like, don\'t eat" - The ultimate Nigerian parent threat',
                'body' => '<p>Me: This soup tastes funny<br>Mum: If you like, don\'t eat</p><p>Those five words carry more weight than any military threat. Because she means it. She will literally watch you starve rather than make another meal.</p><p>And the thing is, the soup really did taste funny. I think she forgot salt. Or maybe added too much salt. But once she says "If you like, don\'t eat," the conversation is over. You either eat the questionable soup or you fast.</p><p>I\'ve seen my siblings choose hunger over admitting the food needed adjustment. That\'s the power of those words. Nigerian parents don\'t negotiate with food terrorists.</p>',
                'category' => 'rant',
                'alias' => 'foodCritic',
                'upvotes' => 33,
                'downvotes' => 0,
                'views' => 289,
            ],
            [
                'title' => 'My dad said calculator will damage my brain',
                'body' => '<p>"Don\'t use calculator! It will damage your brain!" - My father, every time he sees me reaching for a calculator.</p><p>According to him, using a calculator will make my brain lazy and eventually stop working. Meanwhile, he uses his phone calculator to calculate simple things like splitting a bill.</p><p>He made me do long division by hand while he\'s there using calculator to figure out how much change he should get from pure water. The hypocrisy is real.</p><p>Now I\'m in university and still feel guilty every time I use a calculator, like I\'m betraying my brain or something. Nigerian parent programming runs deep.</p>',
                'category' => 'confession',
                'alias' => 'mathStruggler',
                'upvotes' => 21,
                'downvotes' => 1,
                'views' => 145,
            ],
            [
                'title' => '"You\'re always on that phone" says my mum while on her phone',
                'body' => '<p>My mum: "You\'re always on that phone! Put it down!"<br>Also my mum: *Currently scrolling through Facebook while talking*</p><p>The irony is completely lost on her. She\'ll spend 3 hours watching TikToks but the moment I pick up my phone for 5 minutes, I\'m addicted and need intervention.</p><p>When I point out that she\'s also on her phone, she says "That\'s different, I\'m doing important things." The important things: Sharing prayers on WhatsApp status and arguing with strangers in Facebook comment sections.</p><p>Nigerian parents have perfected the art of "Do as I say, not as I do."</p>',
                'category' => 'rant',
                'alias' => 'phoneSinner',
                'upvotes' => 29,
                'downvotes' => 2,
                'views' => 198,
            ],
        ];

        foreach ($stories as $storyData) {
            Story::create([
                'title' => $storyData['title'],
                'body' => $storyData['body'],
                'slug' => Str::slug($storyData['title']),
                'alias' => $storyData['alias'],
                'cookie_hash' => 'seeder_' . Str::random(32),
                'status' => 'published',
                'category' => $storyData['category'],
                'upvotes' => $storyData['upvotes'],
                'downvotes' => $storyData['downvotes'],
                'views' => $storyData['views'],
                'created_at' => now()->subDays(rand(1, 7)),
                'updated_at' => now()->subDays(rand(1, 7)),
            ]);
        }
    }
}