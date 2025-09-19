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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            $table->text('body');
            $table->string('alias');
            $table->string('cookie_hash');
            $table->enum('status', ['published', 'review', 'hidden'])->default('published');
            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);
            $table->timestamps();

            $table->index(['story_id']);
            $table->index(['parent_id']);
            $table->index(['status']);
            $table->index(['cookie_hash']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
