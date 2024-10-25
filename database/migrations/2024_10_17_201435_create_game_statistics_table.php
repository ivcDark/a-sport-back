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
        Schema::create('game_statistics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id');
            $table->uuid('club_id');
            $table->string('section_game');
            $table->string('type_indicator');
            $table->double('value')->nullable();
            $table->timestamps();

            $table->index('game_id', 'gs_game_id_idx');
            $table->index('club_id', 'gs_club_id_idx');

            $table->foreign('game_id', 'gs_game_id_fk')
                ->on('games')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreign('club_id', 'gs_club_id_fk')
                ->on('clubs')
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_statistics');
    }
};
