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
            $table->string('country_code', 2)->nullable()->index()->after('theme_id');
            $table->string('state_code')->nullable()->after('country_code');
            $table->string('city')->nullable()->after('state_code');
            $table->string('region')->nullable()->index()->after('city'); // Combined region identifier like "NG-Lagos"
            $table->integer('spotlight_score')->default(0)->index()->after('region');
            $table->integer('shares_count')->default(0)->after('spotlight_score');
            $table->decimal('computed_feed_score', 10, 4)->nullable()->index()->after('shares_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropColumn([
                'country_code', 
                'state_code', 
                'city', 
                'region', 
                'spotlight_score', 
                'shares_count', 
                'computed_feed_score'
            ]);
        });
    }
};
