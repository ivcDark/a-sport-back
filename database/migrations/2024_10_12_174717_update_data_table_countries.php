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
        $countries = \App\Models\Country::all();

        foreach ($countries as $country) {
            $country->sort = $country->sort == null ? 99 : $country->sort;
            $country->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $countries = \App\Models\Country::all();

        foreach ($countries as $country) {
            $country->sort = $country->sort == 99 ? null : $country->sort;
            $country->save();
        }
    }
};
