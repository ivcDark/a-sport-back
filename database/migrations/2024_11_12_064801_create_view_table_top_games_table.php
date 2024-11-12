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
        Schema::create('view_table_top_games', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('game_id');
            $table->unsignedInteger('win_home')->default(0)->comment('Победят хозяева');
            $table->unsignedInteger('win_guest')->default(0)->comment('Победят гости');
            $table->unsignedInteger('draw')->default(0)->comment('Будет ничья');
            $table->boolean('actual')->default(true)->comment('Матч актуален, выводить на странице');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_table_top_games');
    }
};
