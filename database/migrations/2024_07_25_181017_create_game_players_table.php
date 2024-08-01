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
        Schema::create('game_players', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id');
            $table->uuid('player_id');
            $table->boolean('is_start_group')->default(false);
            $table->boolean('is_reserve_group')->default(false);
            $table->boolean('is_injured_group')->default(false);
            $table->boolean('is_best')->default(false);
            $table->double('rating')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('game_id', 'gp_game_id_idx');
            $table->index('player_id', 'gp_player_id_idx');

            $table->foreign('game_id', 'gp_game_id_fk')
                ->on('games')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreign('player_id', 'gp_player_id_fk')
                ->on('players')
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_players');
    }
};
