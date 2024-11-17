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
        Schema::create('view_table_best_player_on_positions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player_id');
            $table->unsignedSmallInteger('position');
            $table->unsignedDouble('rating');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('view_table_best_player_on_positions');
    }
};
