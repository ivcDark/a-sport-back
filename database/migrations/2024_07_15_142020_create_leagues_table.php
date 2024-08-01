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
        Schema::create('leagues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('code')->nullable();
            $table->uuid('country_id');
            $table->softDeletes();
            $table->timestamps();

            $table->index('country_id', 'leagues_country_id_idx');

            $table->foreign('country_id', 'leagues_country_id_fk')
                ->on('countries')
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leagues');
    }
};
