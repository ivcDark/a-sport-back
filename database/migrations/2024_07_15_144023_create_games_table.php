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
        Schema::create('games', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('club_home_id');
            $table->uuid('club_guest_id');
            $table->uuid('league_season_id');
            $table->softDeletes();
            $table->timestamps();

            $table->index('club_home_id', 'games_club_home_id_idx');
            $table->index('club_guest_id', 'games_club_guest_id_idx');
            $table->index('league_season_id', 'cl_league_season_id_idx');

            $table->foreign('club_home_id', 'games_club_home_id_fk')
                ->on('clubs')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreign('club_guest_id', 'games_club_guest_id_fk')
                ->on('clubs')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreign('league_season_id', 'games_league_season_id_fk')
                ->on('league_seasons')
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
