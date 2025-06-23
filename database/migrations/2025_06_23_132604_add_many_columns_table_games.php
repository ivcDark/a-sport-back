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
        Schema::table('games', function (Blueprint $table) {
            $table->uuid('stadium_id')->after('league_season_id')->nullable();
            $table->uuid('group_tournament_id')->after('stadium_id')->nullable();
            $table->uuid('playoff_round_id')->after('group_tournament_id')->nullable();
            $table->unsignedSmallInteger('leg_number')->after('playoff_round_id')->nullable();
            $table->string('game_type')->after('leg_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('stadium_id');
            $table->dropColumn('group_tournament_id');
            $table->dropColumn('playoff_round_id');
            $table->dropColumn('leg_number');
            $table->dropColumn('game_type');
        });
    }
};
