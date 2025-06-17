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
        Schema::create('players', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('club_id');
            $table->string('fio');
            $table->unsignedSmallInteger('number')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('club_id', 'players_club_id_idx');

            $table->foreign('club_id', 'players_club_id_fk')
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
        Schema::dropIfExists('players');
    }
};
