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
        Schema::table('view_table_best_clubs_seasons', function (Blueprint $table) {
            $table->uuid('type_game_id')->after('section_game_id')->nullable();
            $table->uuid('section_game_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('view_table_best_clubs_seasons', function (Blueprint $table) {
            $table->dropColumn('type_game_id');
            $table->uuid('section_game_id')->change();
        });
    }
};
