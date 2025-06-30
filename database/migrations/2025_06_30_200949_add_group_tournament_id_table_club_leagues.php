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
        Schema::table('club_leagues', function (Blueprint $table) {
            $table->uuid('group_tournament_id')->after('league_season_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_leagues', function (Blueprint $table) {
            $table->dropColumn('group_tournament_id');
        });
    }
};
