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
        Schema::table('game_players', function (Blueprint $table) {
            $table->boolean('is_missing_game')->after('is_injured_group')->default(false);
            $table->string('missing_reason')->after('is_missing_game')->nullable();
            $table->unsignedInteger('number_player')->after('player_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_players', function (Blueprint $table) {
            $table->dropColumn('is_missing_game');
            $table->dropColumn('missing_reason');
            $table->dropColumn('number_player');
        });
    }
};
