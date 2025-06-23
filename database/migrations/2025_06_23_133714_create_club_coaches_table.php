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
        Schema::create('club_coaches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('club_id')->index();
            $table->uuid('coach_id')->index();
            $table->uuid('season_id')->index();
            $table->boolean('is_active')->index();
            $table->date('start_date')->index();
            $table->date('end_date')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_coaches');
    }
};
