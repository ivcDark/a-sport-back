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
        Schema::table('game_statistics', function (Blueprint $table) {
            $table->dropColumn('section_game');
            $table->dropColumn('type_indicator');

            $table->string('period', 20)->after('club_id');
            $table->uuid('stat_type_id')->after('period');

            $table->foreign('stat_type_id', 'stid_gs_fk')->references('id')->on('stat_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_statistics', function (Blueprint $table) {
            $table->dropForeign('stid_gs_fk');
            $table->dropColumn('stat_type_id');
            $table->dropColumn('period');

            $table->string('section_game');
            $table->string('type_indicator');
        });
    }
};
