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
        Schema::create('user_hidden_posts', function (Blueprint $table) {
            $table->id();
            $table->string('cookie_hash', 32)->index();
            $table->unsignedBigInteger('story_id');
            $table->enum('reason', ['not_interested', 'hide_category', 'hide_author', 'spam', 'other'])->nullable();
            $table->timestamp('hidden_at')->useCurrent();

            $table->foreign('story_id')->references('id')->on('stories')->onDelete('cascade');
            $table->unique(['cookie_hash', 'story_id']);
            $table->index('hidden_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_hidden_posts');
    }
};
