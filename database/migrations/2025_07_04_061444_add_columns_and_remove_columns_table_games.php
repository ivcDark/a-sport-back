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
            $table->uuid('game_stage_id')->after('stadium_id')->index('games_gsid_idx')->nullable();
            $table->uuid('game_round_id')->after('game_stage_id')->index('games_grid_idx')->nullable();

            $table->dropColumn('group_tournament_id');
            $table->dropColumn('playoff_round_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('game_stage_id');
            $table->dropColumn('game_round_id');

            $table->uuid('group_tournament_id')->after('stadium_id')->nullable();
            $table->uuid('playoff_round_id')->after('group_tournament_id')->nullable();
        });
    }
};
