<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Move alias to slug and generate new aliases
        $stories = \App\Models\Story::all();
        
        foreach ($stories as $story) {
            // Move current alias (URL slug) to slug column
            $story->slug = $story->alias;
            
            // Generate new anonymous alias for display
            $story->alias = $this->generateRandomAlias();
            
            $story->save();
        }

        // Add unique constraint to slug
        Schema::table('stories', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    private function generateRandomAlias(): string
    {
        $adjectives = [
            'quiet', 'midnight', 'silver', 'deep', 'honest', 'working', 'night', 
            'urban', 'compassionate', 'truthful', 'gentle', 'brave', 'hopeful', 
            'wise', 'caring', 'peaceful', 'thoughtful', 'kind', 'resilient', 'curious'
        ];
        
        $nouns = [
            'voice', 'owl', 'storm', 'thoughts', 'soul', 'person', 'wanderer', 
            'heart', 'spirit', 'seeker', 'friend', 'dreamer', 'warrior', 'listener', 
            'helper', 'traveler', 'writer', 'observer', 'thinker', 'storyteller'
        ];

        return $adjectives[array_rand($adjectives)] . $nouns[array_rand($nouns)];
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove unique constraint
        Schema::table('stories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
    }
};
