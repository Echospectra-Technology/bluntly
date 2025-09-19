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
        // Assign categories to existing stories based on their content
        $stories = [
            1 => 'confession', // "I've been lying to my family about my job for two years"
            2 => 'rant',       // "Why does everyone pretend social media is real life?"
            3 => 'gist',       // "The day I accidentally became a local hero"
            4 => 'story',      // "I found my childhood diary and it changed everything"
            5 => 'confession', // "I've been eating lunch alone in my car for six months"
        ];

        foreach ($stories as $storyId => $category) {
            \DB::table('stories')
                ->where('id', $storyId)
                ->update(['category' => $category]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
