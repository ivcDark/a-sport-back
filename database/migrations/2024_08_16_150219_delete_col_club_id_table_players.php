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
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign('players_club_id_fk');
            $table->dropIndex('players_club_id_idx');
            $table->dropColumn('club_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->uuid('club_id')->nullable();

            $table->index('club_id', 'players_club_id_idx');

            $table->foreign('club_id', 'players_club_id_fk')
                ->on('clubs')
                ->references('id')
                ->cascadeOnDelete();
        });
    }
};
