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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->enum('reason', ['harassment', 'spam', 'hate', 'self_harm', 'other']);
            $table->string('cookie_hash');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['item_type', 'item_id']);
            $table->index(['cookie_hash']);
            $table->index(['reason']);
            $table->unique(['item_type', 'item_id', 'cookie_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
