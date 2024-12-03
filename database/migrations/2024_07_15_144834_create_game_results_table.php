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
        Schema::create('game_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id');
            $table->smallInteger('home_goals');
            $table->smallInteger('guest_goals');
            $table->softDeletes();
            $table->timestamps();

            $table->index('game_id', 'gr_game_id_idx');

            $table->foreign('game_id', 'gr_game_id_fk')
                ->on('games')
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_results');
    }
};
