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
        Schema::table('player_clubs', function (Blueprint $table) {
            $table->boolean('in_club')->after('club_id')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_clubs', function (Blueprint $table) {
            $table->dropColumn('in_club');
        });
    }
};
