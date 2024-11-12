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
        Schema::create('view_table_best_clubs_seasons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('club_id');
            $table->uuid('league_season_id');
            $table->uuid('league_id');
            $table->uuid('season_id');
            $table->uuid('section_game_id');
            $table->unsignedInteger('games_played')->default(0);
            $table->unsignedInteger('points')->default(0);
            $table->unsignedInteger('goals_scored')->default(0);
            $table->unsignedInteger('goals_conceded')->default(0);
            $table->integer('goals_diff')->default(0);
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('yellow_cards')->default(0);
            $table->unsignedInteger('red_cards')->default(0);
            $table->double('avg_ball_possession')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_table_best_clubs_seasons');
    }
};
