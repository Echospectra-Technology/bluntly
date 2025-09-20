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
        Schema::create('user_tag_affinities', function (Blueprint $table) {
            $table->id();
            $table->string('cookie_hash', 32)->index();
            $table->unsignedBigInteger('tag_id');
            $table->integer('interaction_count')->default(0);
            $table->decimal('affinity_score', 8, 4)->default(0.0); // Weighted affinity score
            $table->enum('last_interaction_type', ['view', 'upvote', 'downvote', 'comment'])->nullable();
            $table->timestamp('last_interaction')->nullable();
            $table->timestamps();

            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->unique(['cookie_hash', 'tag_id']);
            $table->index(['cookie_hash', 'affinity_score']);
            $table->index('last_interaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_tag_affinities');
    }
};
