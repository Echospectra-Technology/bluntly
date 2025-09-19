<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'WorkLife', 'Family', 'Career', 'SocialMedia', 'MentalHealth', 
            'Authenticity', 'Community', 'Kindness', 'Unexpected', 'Nostalgia', 
            'Childhood', 'SelfReflection', 'Loneliness', 'NewJob', 'Relationships', 
            'Money', 'College', 'Anxiety', 'Depression', 'Success', 'Failure', 
            'Love', 'Heartbreak', 'Friendship', 'Growth', 'Change', 'Hope', 
            'Fear', 'Courage', 'Dreams', 'Reality'
        ];

        foreach ($tags as $tagName) {
            Tag::create([
                'name' => $tagName,
                'slug' => Str::slug($tagName),
            ]);
        }
    }
}