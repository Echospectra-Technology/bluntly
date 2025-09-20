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
        // Only run if slug column doesn't exist
        if (!Schema::hasColumn('stories', 'slug')) {
            Schema::table('stories', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('body');
            });

            // Populate slug column with incremental ID approach for existing stories
            $stories = \App\Models\Story::whereNull('slug')->orderBy('id')->get();
            foreach ($stories as $index => $story) {
                // Use the incremental approach: slug-based-on-title + story-id
                $baseSlug = \Illuminate\Support\Str::slug($story->title);
                $slug = $baseSlug . '-' . $story->id;
                $story->update(['slug' => $slug]);
            }

            // Make slug not nullable and add unique constraint
            Schema::table('stories', function (Blueprint $table) {
                $table->string('slug')->nullable(false)->change();
                $table->unique('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            if (Schema::hasColumn('stories', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });
    }
};
