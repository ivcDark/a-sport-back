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
        Schema::create('stat_type_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source')->comment('Источник'); // Например: flashscore, opta
            $table->string('original_name')->comment('Наименование у источника');
            $table->unsignedInteger('original_id')->nullable(); // ID у источника
            $table->uuid('stat_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['source', 'original_name', 'stat_type_id']);   // Чтобы не было дублей
            $table->foreign('stat_type_id')
                ->references('id')
                ->on('stat_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stat_type_mappings');
    }
};
