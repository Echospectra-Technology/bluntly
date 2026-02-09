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
        Schema::create('ai_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_persona_id')->constrained()->onDelete('cascade');
            $table->enum('action_type', ['post', 'reply', 'vote'])->default('post');
            $table->nullableMorphs('target'); // target_type, target_id (story or comment to reply to)
            $table->enum('status', ['scheduled', 'processing', 'completed', 'failed'])->default('scheduled');
            $table->text('error_message')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['ai_persona_id', 'status']);
            $table->index('scheduled_at');
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_actions');
    }
};
