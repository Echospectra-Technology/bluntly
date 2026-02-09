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
        Schema::create('ai_personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('username')->unique();
            $table->text('persona')->nullable(); // Backstory/personality description
            $table->text('system_prompt')->nullable(); // Custom system prompt for LLM
            $table->string('avatar_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('behavior_rules')->nullable(); // Posting frequency, reply probability, etc.
            $table->json('stats')->nullable(); // Total posts, replies, votes received
            $table->timestamps();

            $table->index('user_id');
            $table->index('username');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_personas');
    }
};
