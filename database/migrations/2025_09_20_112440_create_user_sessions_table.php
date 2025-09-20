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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('cookie_hash', 32)->index();
            $table->string('country_code', 2)->nullable()->index();
            $table->string('country_name')->nullable();
            $table->string('state_code')->nullable();
            $table->string('state_name')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable()->index(); // Combined region identifier like "NG-Lagos" or "US-NY"
            $table->string('ip_hash', 64)->nullable(); // Hashed IP for privacy
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('last_activity')->useCurrent();
            $table->timestamps();

            $table->unique('cookie_hash');
            $table->index(['country_code', 'state_code']);
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
