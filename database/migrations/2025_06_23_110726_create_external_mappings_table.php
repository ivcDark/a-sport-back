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
        Schema::create('external_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('entity_type')->comment('club, league, country и т.д');
            $table->uuid('entity_id')->comment('внутренний UUID сущности');
            $table->string('source')->comment('Наименование источника');
            $table->string('external_id')->comment('ID сущности у источника');
            $table->string('external_name')->nullable()->comment('Наименование сущности у источника');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_mappings');
    }
};
