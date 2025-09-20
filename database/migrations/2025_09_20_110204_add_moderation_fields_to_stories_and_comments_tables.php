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
        Schema::table('stories', function (Blueprint $table) {
            $table->integer('moderation_score')->default(0)->after('status');
            $table->timestamp('moderated_at')->nullable()->after('moderation_score');
            $table->json('matched_rules')->nullable()->after('moderated_at');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->integer('moderation_score')->default(0)->after('status');
            $table->timestamp('moderated_at')->nullable()->after('moderation_score');
            $table->json('matched_rules')->nullable()->after('moderated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn(['moderation_score', 'moderated_at', 'matched_rules']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn(['moderation_score', 'moderated_at', 'matched_rules']);
        });
    }
};
