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
        Schema::create('views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->onDelete('cascade');
            $table->string('cookie_hash');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['story_id']);
            $table->index(['cookie_hash']);
            $table->unique(['story_id', 'cookie_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};
