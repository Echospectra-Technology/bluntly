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
            $table->foreignId('ai_persona_id')->nullable()->after('cookie_hash')->constrained()->onDelete('cascade');
            $table->index('ai_persona_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropForeign(['ai_persona_id']);
            $table->dropIndex(['ai_persona_id']);
            $table->dropColumn('ai_persona_id');
        });
    }
};
