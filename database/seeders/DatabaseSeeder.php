<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        
        $this->call([
            // First, seed basic data
            TagSeeder::class,
            
            // Create weekly themes (needed for theme-based stories)
            WeeklyThemeSeeder::class,
            
            // Seed initial stories
            StorySeeder::class,
            
            // Add Nigerian parent themed stories
            NigerianParentStoriesSeeder::class,
            MoreNigerianParentStoriesSeeder::class,
        ]);
        
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Tags: ' . \App\Models\Tag::count());
        $this->command->info('   - Weekly Themes: ' . \App\Models\WeeklyTheme::count());
        $this->command->info('   - Stories: ' . \App\Models\Story::count());
        $this->command->info('   - Nigerian Parent Stories: ' . \App\Models\Story::whereHas('theme', function($q) { 
            $q->where('name', 'like', '%Nigerian Parent%'); 
        })->count());
    }
}
