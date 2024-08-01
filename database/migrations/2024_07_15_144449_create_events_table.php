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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id');
            $table->uuid('player_id')->nullable();
            $table->string('type');
            $table->time('minute');
            $table->string('section_game');
            $table->softDeletes();
            $table->timestamps();

            $table->index('game_id', 'events_game_id_idx');
            $table->index('player_id', 'events_player_id_idx');

            $table->foreign('game_id', 'events_game_id_fk')
                ->on('games')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreign('player_id', 'events_player_id_fk')
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
        Schema::dropIfExists('events');
    }
};
