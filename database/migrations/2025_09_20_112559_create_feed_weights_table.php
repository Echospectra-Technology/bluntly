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
        Schema::create('feed_weights', function (Blueprint $table) {
            $table->id();
            $table->string('experiment_name')->default('default')->index();
            $table->string('weight_type'); // 'region', 'affinity', 'engagement', 'recency', 'spotlight', 'diversity'
            $table->decimal('weight_value', 5, 3)->default(1.0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['experiment_name', 'weight_type']);
            $table->index(['experiment_name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_weights');
    }
};
