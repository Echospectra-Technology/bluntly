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
        Schema::create('anonymous_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->string('email')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('cookie_hash', 32)->nullable()->unique(); // For migration from cookie system
            $table->boolean('is_migrated_from_cookie')->default(false);
            $table->json('preferences')->nullable(); // Store user preferences
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->index('username');
            $table->index('cookie_hash');
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anonymous_users');
    }
};
