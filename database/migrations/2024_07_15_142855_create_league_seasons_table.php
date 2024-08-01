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
        Schema::create('league_seasons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('league_id');
            $table->uuid('season_id');
            $table->timestamps();

            $table->index('league_id', 'ls_league_id_idx');
            $table->index('season_id', 'ls_season_id_idx');

            $table->foreign('league_id', 'ls_league_id_fk')
                ->on('leagues')
                ->references('id')
                ->cascadeOnDelete();
            $table->foreign('season_id', 'ls_season_id_fk')
                ->on('seasons')
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_seasons');
    }
};
