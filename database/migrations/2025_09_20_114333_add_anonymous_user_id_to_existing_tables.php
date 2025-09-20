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
        // Add anonymous_user_id to stories table
        Schema::table('stories', function (Blueprint $table) {
            $table->unsignedBigInteger('anonymous_user_id')->nullable()->after('cookie_hash');
            $table->index('anonymous_user_id');
            $table->foreign('anonymous_user_id')->references('id')->on('anonymous_users')->onDelete('set null');
        });

        // Add anonymous_user_id to votes table
        Schema::table('votes', function (Blueprint $table) {
            $table->unsignedBigInteger('anonymous_user_id')->nullable()->after('cookie_hash');
            $table->index('anonymous_user_id');
            $table->foreign('anonymous_user_id')->references('id')->on('anonymous_users')->onDelete('cascade');
        });

        // Add anonymous_user_id to views table
        Schema::table('views', function (Blueprint $table) {
            $table->unsignedBigInteger('anonymous_user_id')->nullable()->after('cookie_hash');
            $table->index('anonymous_user_id');
            $table->foreign('anonymous_user_id')->references('id')->on('anonymous_users')->onDelete('cascade');
        });

        // Add anonymous_user_id to comments table
        Schema::table('comments', function (Blueprint $table) {
            $table->unsignedBigInteger('anonymous_user_id')->nullable()->after('cookie_hash');
            $table->index('anonymous_user_id');
            $table->foreign('anonymous_user_id')->references('id')->on('anonymous_users')->onDelete('set null');
        });

        // Add anonymous_user_id to reports table
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedBigInteger('anonymous_user_id')->nullable()->after('cookie_hash');
            $table->index('anonymous_user_id');
            $table->foreign('anonymous_user_id')->references('id')->on('anonymous_users')->onDelete('set null');
        });

        // Update personalization tables
        Schema::table('user_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('anonymous_user_id')->nullable()->after('cookie_hash');
            $table->index('anonymous_user_id');
            $table->foreign('anonymous_user_id')->references('id')->on('anonymous_users')->onDelete('cascade');
        });

        Schema::table('user_tag_affinities', function (Blueprint $table) {
            $table->unsignedBigInteger('anonymous_user_id')->nullable()->after('cookie_hash');
            $table->index('anonymous_user_id');
            $table->foreign('anonymous_user_id')->references('id')->on('anonymous_users')->onDelete('cascade');
        });

        Schema::table('user_hidden_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('anonymous_user_id')->nullable()->after('cookie_hash');
            $table->index('anonymous_user_id');
            $table->foreign('anonymous_user_id')->references('id')->on('anonymous_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['stories', 'votes', 'views', 'comments', 'reports', 'user_sessions', 'user_tag_affinities', 'user_hidden_posts'];
        
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['anonymous_user_id']);
                $t->dropIndex(['anonymous_user_id']);
                $t->dropColumn('anonymous_user_id');
            });
        }
    }
};
