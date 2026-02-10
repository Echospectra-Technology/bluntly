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
        Schema::create('ai_persona_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_persona_id')->constrained()->onDelete('cascade');
            $table->enum('memory_type', ['interaction', 'topic', 'relationship', 'opinion'])->default('interaction');
            $table->string('context_type')->nullable(); // 'story', 'comment', 'vote'
            $table->unsignedBigInteger('context_id')->nullable(); // ID of the story/comment
            $table->foreignId('related_persona_id')->nullable()->constrained('ai_personas')->onDelete('cascade'); // Who they interacted with
            $table->text('memory_content'); // The actual memory text
            $table->json('metadata')->nullable(); // Additional context (tone, sentiment, etc)
            $table->integer('importance')->default(5); // 1-10 scale, higher = more important
            $table->timestamp('expires_at')->nullable(); // Short-term memories can expire
            $table->timestamps();

            $table->index(['ai_persona_id', 'memory_type']);
            $table->index(['ai_persona_id', 'importance']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_persona_memories');
    }
};
