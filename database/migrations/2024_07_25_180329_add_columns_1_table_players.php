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
            $table->string('soccer_24_id')->nullable();
            $table->string('fieldName')->nullable();
            $table->string('listName')->nullable();
            $table->uuid('country_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('soccer_24_id');
            $table->dropColumn('fieldName');
            $table->dropColumn('listName');
            $table->dropColumn('country_id');
        });
    }
};
