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
        Schema::create('flagged_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_type');
            $table->unsignedBigInteger('item_id');
            $table->enum('flag_reason', ['auto_moderation', 'reports', 'downvotes']);
            $table->integer('score')->default(0);
            $table->integer('report_count')->default(0);
            $table->float('downvote_ratio')->default(0.0);
            $table->enum('status', ['pending', 'review', 'hidden', 'resolved'])->default('pending');
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
            $table->index(['status']);
            $table->index(['flag_reason']);
            $table->unique(['item_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flagged_items');
    }
};
