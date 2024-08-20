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
        Schema::create('player_clubs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_id');
            $table->uuid('club_id');
            $table->timestamps();

            $table->index('player_id', 'pc_player_id_idx');
            $table->index('club_id', 'pc_club_id_idx');

            $table->foreign('player_id', 'pc_player_id_fk')
                ->on('players')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreign('club_id', 'pc_club_id_fk')
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
        Schema::dropIfExists('player_clubs');
    }
};
