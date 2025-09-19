<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WeeklyTheme;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WeeklyThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Nigerian Parent Said What?!',
                'description' => 'Share the most outrageous, funny, or memorable things your Nigerian parents have said to you.',
                'prompt_text' => 'What\'s the most Nigerian thing your parent ever said to you? Whether it made you laugh, cry, or question your existence, we want to hear it!',
            ],
            [
                'name' => 'Lagos Traffic Chronicles',
                'description' => 'Stories from the notorious Lagos traffic - from the bizarre to the life-changing.',
                'prompt_text' => 'What happened to you in Lagos traffic that you\'ll never forget? Did you witness something crazy? Meet someone interesting? Or have a breakdown?',
            ],
            [
                'name' => 'Wedding Wahala Stories',
                'description' => 'Nigerian wedding drama, from family politics to ceremony disasters.',
                'prompt_text' => 'What\'s the most dramatic thing that happened at a Nigerian wedding you attended? Family fights, ceremony fails, or social media moments gone wrong!',
            ],
            [
                'name' => 'Worst Date Ever Week',
                'description' => 'Dating disasters and romantic fails that will make you cringe and laugh.',
                'prompt_text' => 'Tell us about your worst date ever. What went wrong? How did it end? Did you learn something or just get a good story?',
            ],
            [
                'name' => 'I Can\'t Believe I Got Away With...',
                'description' => 'Times you broke rules, bent truth, or lived dangerously and somehow escaped consequences.',
                'prompt_text' => 'What\'s something you did that you can\'t believe you got away with? School, work, family - what\'s your best escape story?',
            ],
            [
                'name' => 'Most Embarrassing Moment',
                'description' => 'Those cringe moments that still make you want to disappear when you remember them.',
                'prompt_text' => 'What\'s your most embarrassing moment? The one that still makes you cringe years later. We\'ve all been there!',
            ],
            [
                'name' => 'Family WhatsApp Group Drama',
                'description' => 'The chaos, arguments, and comedy gold that happens in Nigerian family group chats.',
                'prompt_text' => 'What\'s the wildest thing that happened in your family WhatsApp group? Screenshots welcome (but blur the names)!',
            ],
            [
                'name' => 'Nigerian University Survival Stories',
                'description' => 'ASUU strikes, hostel life, and the general chaos of Nigerian higher education.',
                'prompt_text' => 'What\'s your craziest Nigerian university experience? Hostel drama, lecturer wahala, or just surviving the system!',
            ],
        ];

        $startDate = Carbon::now()->startOfWeek(); // This week
        
        foreach ($themes as $index => $themeData) {
            $weekStart = $startDate->copy()->addWeeks($index);
            $weekEnd = $weekStart->copy()->endOfWeek();
            
            WeeklyTheme::create([
                'name' => $themeData['name'],
                'slug' => Str::slug($themeData['name']),
                'description' => $themeData['description'],
                'prompt_text' => $themeData['prompt_text'],
                'start_date' => $weekStart,
                'end_date' => $weekEnd,
                'is_active' => $index === 0, // First theme is active
                'submission_count' => 0,
            ]);
        }
    }
}
